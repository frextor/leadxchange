<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    private function isMember(Group $group): bool
    {
        return $group->isMember(auth()->id()) || $group->isOwner(auth()->id());
    }

    private function pollResponse(Poll $poll): array
    {
        $poll->load(['options', 'user.profile:id,user_id,avatar']);
        $totalVotes = $poll->votes()->count();
        $userVote = $poll->votes()->where('user_id', auth()->id())->first();

        return [
            'id' => $poll->id,
            'question' => $poll->question,
            'is_multiple_choice' => $poll->is_multiple_choice,
            'ends_at' => $poll->ends_at?->toIso8601String(),
            'creator_id' => $poll->user_id,
            'creator_name' => $poll->user->full_name,
            'creator_avatar' => $poll->user->profile?->avatar_url,
            'total_votes' => $totalVotes,
            'user_vote_option_id' => $userVote?->poll_option_id,
            'created_at' => $poll->created_at->toIso8601String(),
            'options' => $poll->options->map(fn($opt) => [
                'id' => $opt->id,
                'text' => $opt->text,
                'votes_count' => $opt->votes()->count(),
            ])->toArray(),
        ];
    }

    public function index(Group $group)
    {
        if (!$this->isMember($group)) return response()->json(['message' => 'Forbidden'], 403);
        $polls = $group->polls()->with(['options', 'user.profile:id,user_id,avatar'])->latest()->get();
        return response()->json([
            'data' => $polls->map(fn($p) => $this->pollResponse($p))->toArray(),
        ]);
    }

    public function store(Request $request, Group $group)
    {
        if (!$this->isMember($group)) return response()->json(['message' => 'Forbidden'], 403);
        $request->validate([
            'question' => 'required|string|max:500',
            'options' => 'required|array|min:2|max:4',
            'options.*' => 'required|string|max:200',
        ]);
        $poll = DB::transaction(function () use ($request, $group) {
            $poll = $group->polls()->create([
                'user_id' => auth()->id(),
                'question' => $request->question,
                'is_multiple_choice' => $request->boolean('is_multiple_choice', false),
            ]);
            foreach ($request->options as $text) {
                $poll->options()->create(['text' => $text]);
            }
            return $poll;
        });
        return response()->json(['data' => $this->pollResponse($poll)], 201);
    }

    public function vote(Request $request, Group $group, Poll $poll)
    {
        if (!$this->isMember($group)) return response()->json(['message' => 'Forbidden'], 403);
        $request->validate(['option_id' => 'required|integer']);
        if (!$poll->options()->where('id', $request->option_id)->exists()) {
            return response()->json(['message' => 'Invalid option'], 422);
        }
        PollVote::updateOrCreate(
            ['poll_id' => $poll->id, 'user_id' => auth()->id()],
            ['poll_option_id' => $request->option_id]
        );
        return response()->json(['data' => $this->pollResponse($poll->fresh())]);
    }

    public function update(Request $request, Group $group, Poll $poll)
    {
        if ($poll->user_id !== auth()->id()) return response()->json(['message' => 'Forbidden'], 403);
        if ($poll->votes()->count() > 0) return response()->json(['message' => 'Cannot edit a poll that has votes'], 422);
        $request->validate([
            'question' => 'required|string|max:500',
            'options' => 'required|array|min:2|max:4',
            'options.*' => 'required|string|max:200',
        ]);
        DB::transaction(function () use ($request, $poll) {
            $poll->update([
                'question' => $request->question,
                'is_multiple_choice' => $request->boolean('is_multiple_choice', false),
            ]);
            $poll->options()->delete();
            foreach ($request->options as $text) {
                $poll->options()->create(['text' => $text]);
            }
        });
        return response()->json(['data' => $this->pollResponse($poll->fresh())]);
    }

    public function destroy(Group $group, Poll $poll)
    {
        if ($poll->user_id !== auth()->id()) return response()->json(['message' => 'Forbidden'], 403);
        DB::transaction(function () use ($poll) {
            $poll->votes()->delete();
            $poll->options()->delete();
            $poll->delete();
        });
        return response()->json(['success' => true]);
    }
}
