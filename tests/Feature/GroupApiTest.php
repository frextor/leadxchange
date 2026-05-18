<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GroupApiTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function actingAsApi(User $user)
    {
        return $this->actingAs($user, 'sanctum');
    }

    private function createGroup(User $creator, array $overrides = []): Group
    {
        $group = Group::create(array_merge([
            'name'          => 'Test Group',
            'description'   => 'A test group',
            'created_by'    => $creator->id,
            'cover_color'   => '#1E8F88',
            'is_public'     => true,
            'members_count' => 1,
        ], $overrides));

        $group->members()->attach($creator->id, ['role' => 'owner']);

        return $group;
    }

    // ── GET /api/groups ───────────────────────────────────────────────────────

    public function test_index_returns_grouped_structure(): void
    {
        $user = User::factory()->create();
        $this->createGroup($user);

        $response = $this->actingAsApi($user)->getJson('/api/groups');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['nearby', 'recommended', 'others'],
                     'meta' => ['total', 'nearby', 'recommended'],
                 ]);
    }

    public function test_index_requires_auth(): void
    {
        $this->getJson('/api/groups')->assertStatus(401);
    }

    // ── POST /api/groups ──────────────────────────────────────────────────────

    public function test_store_creates_group_with_basic_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApi($user)->postJson('/api/groups', [
            'name'        => 'Growth Hackers',
            'description' => 'A networking group',
            'cover_color' => '#FF5733',
            'is_public'   => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Growth Hackers')
                 ->assertJsonPath('data.cover_color', '#FF5733')
                 ->assertJsonPath('data.is_public', true)
                 ->assertJsonPath('data.is_member', true)
                 ->assertJsonPath('data.is_creator', true);

        $this->assertDatabaseHas('groups', ['name' => 'Growth Hackers', 'created_by' => $user->id]);
        $this->assertDatabaseHas('group_user', ['user_id' => $user->id, 'role' => 'owner']);
    }

    public function test_store_with_cover_photo_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAsApi($user)->postJson('/api/groups', [
            'name'        => 'Photo Group',
            'cover_photo' => UploadedFile::fake()->image('cover.jpg', 400, 400),
        ]);

        $response->assertStatus(201);

        $group = Group::where('name', 'Photo Group')->first();
        $this->assertNotNull($group->cover_photo);
        Storage::disk('public')->assertExists($group->cover_photo);

        $responseData = $response->json('data');
        $this->assertNotNull($responseData['cover_photo_url']);
    }

    public function test_store_with_is_public_false(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApi($user)->postJson('/api/groups', [
            'name'      => 'Private Group',
            'is_public' => false,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.is_public', false);

        $this->assertDatabaseHas('groups', ['name' => 'Private Group', 'is_public' => false]);
    }

    public function test_store_fails_without_name(): void
    {
        $user = User::factory()->create();

        $this->actingAsApi($user)->postJson('/api/groups', [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
    }

    public function test_store_fails_with_invalid_cover_color(): void
    {
        $user = User::factory()->create();

        $this->actingAsApi($user)->postJson('/api/groups', [
            'name'        => 'Bad Color',
            'cover_color' => 'notacolor',
        ])->assertStatus(422)->assertJsonValidationErrors(['cover_color']);
    }

    // ── GET /api/groups/{id} ──────────────────────────────────────────────────

    public function test_show_returns_group_detail(): void
    {
        $user  = User::factory()->create();
        $group = $this->createGroup($user);

        $response = $this->actingAsApi($user)->getJson("/api/groups/{$group->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $group->id)
                 ->assertJsonPath('data.name', $group->name)
                 ->assertJsonPath('data.is_member', true)
                 ->assertJsonPath('data.is_creator', true)
                 ->assertJsonStructure(['data' => [
                     'id', 'name', 'description', 'cover_color', 'cover_photo_url',
                     'is_public', 'members_count', 'is_member', 'is_creator',
                     'sector', 'city', 'creator', 'created_at',
                 ]]);
    }

    public function test_show_returns_404_for_missing_group(): void
    {
        $user = User::factory()->create();

        $this->actingAsApi($user)->getJson('/api/groups/9999')->assertStatus(404);
    }

    // ── PUT /api/groups/{id} ──────────────────────────────────────────────────

    public function test_update_succeeds_for_admin(): void
    {
        $user  = User::factory()->create();
        $group = $this->createGroup($user);

        $response = $this->actingAsApi($user)->putJson("/api/groups/{$group->id}", [
            'name'        => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('groups', ['id' => $group->id, 'name' => 'Updated Name']);
    }

    public function test_update_with_new_cover_photo(): void
    {
        Storage::fake('public');
        $user  = User::factory()->create();
        $group = $this->createGroup($user);

        $response = $this->actingAsApi($user)->putJson("/api/groups/{$group->id}", [
            'cover_photo' => UploadedFile::fake()->image('new_cover.jpg', 400, 400),
        ]);

        $response->assertStatus(200);

        $group->refresh();
        $this->assertNotNull($group->cover_photo);
        Storage::disk('public')->assertExists($group->cover_photo);
    }

    public function test_update_fails_for_non_admin(): void
    {
        $creator = User::factory()->create();
        $member  = User::factory()->create();
        $group   = $this->createGroup($creator);

        $group->members()->attach($member->id, ['role' => 'member']);

        $this->actingAsApi($member)->putJson("/api/groups/{$group->id}", [
            'name' => 'Hacked Name',
        ])->assertStatus(403);
    }

    public function test_update_fails_for_non_member(): void
    {
        $creator    = User::factory()->create();
        $outsider   = User::factory()->create();
        $group      = $this->createGroup($creator);

        $this->actingAsApi($outsider)->putJson("/api/groups/{$group->id}", [
            'name' => 'Hacked Name',
        ])->assertStatus(403);
    }

    // ── POST /api/groups/{id}/join ────────────────────────────────────────────

    public function test_join_adds_user_as_member(): void
    {
        $creator = User::factory()->create();
        $joiner  = User::factory()->create();
        $group   = $this->createGroup($creator);

        $response = $this->actingAsApi($joiner)->postJson("/api/groups/{$group->id}/join");

        $response->assertStatus(200)->assertJsonPath('message', 'Joined group successfully.');
        $this->assertDatabaseHas('group_user', ['group_id' => $group->id, 'user_id' => $joiner->id, 'role' => 'member']);
    }

    public function test_join_fails_if_already_member(): void
    {
        $user  = User::factory()->create();
        $group = $this->createGroup($user);

        $this->actingAsApi($user)->postJson("/api/groups/{$group->id}/join")
             ->assertStatus(422);
    }

    // ── DELETE /api/groups/{id}/leave ─────────────────────────────────────────

    public function test_leave_removes_user_from_group(): void
    {
        $creator = User::factory()->create();
        $member  = User::factory()->create();
        $group   = $this->createGroup($creator);

        $group->members()->attach($member->id, ['role' => 'member']);
        $group->increment('members_count');

        $response = $this->actingAsApi($member)->deleteJson("/api/groups/{$group->id}/leave");

        $response->assertStatus(200)->assertJsonPath('message', 'Left group successfully.');
        $this->assertDatabaseMissing('group_user', ['group_id' => $group->id, 'user_id' => $member->id]);
    }

    public function test_leave_fails_if_not_a_member(): void
    {
        $creator  = User::factory()->create();
        $outsider = User::factory()->create();
        $group    = $this->createGroup($creator);

        $this->actingAsApi($outsider)->deleteJson("/api/groups/{$group->id}/leave")
             ->assertStatus(422);
    }

    // ── POST /api/groups/{id}/invite ──────────────────────────────────────────

    public function test_invite_adds_user_as_member(): void
    {
        $admin   = User::factory()->create(); // owner by default
        $invitee = User::factory()->create();
        $group   = $this->createGroup($admin);

        $response = $this->actingAsApi($admin)->postJson("/api/groups/{$group->id}/invite", [
            'user_id' => $invitee->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('user.id', $invitee->id);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id'  => $invitee->id,
            'role'     => 'member',
        ]);
    }

    public function test_invite_fails_for_non_admin(): void
    {
        $creator = User::factory()->create();
        $member  = User::factory()->create();
        $invitee = User::factory()->create();
        $group   = $this->createGroup($creator);

        $group->members()->attach($member->id, ['role' => 'member']);

        $this->actingAsApi($member)->postJson("/api/groups/{$group->id}/invite", [
            'user_id' => $invitee->id,
        ])->assertStatus(403);
    }

    public function test_invite_fails_if_user_already_member(): void
    {
        $admin   = User::factory()->create();
        $already = User::factory()->create();
        $group   = $this->createGroup($admin);

        $group->members()->attach($already->id, ['role' => 'member']);

        $this->actingAsApi($admin)->postJson("/api/groups/{$group->id}/invite", [
            'user_id' => $already->id,
        ])->assertStatus(422);
    }

    public function test_invite_fails_with_missing_user_id(): void
    {
        $admin = User::factory()->create();
        $group = $this->createGroup($admin);

        $this->actingAsApi($admin)->postJson("/api/groups/{$group->id}/invite", [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['user_id']);
    }

    // ── GET /api/groups/{id}/members ──────────────────────────────────────────

    public function test_members_returns_list_with_roles(): void
    {
        $admin  = User::factory()->create();
        $member = User::factory()->create();
        $group  = $this->createGroup($admin);

        $group->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAsApi($admin)->getJson("/api/groups/{$group->id}/members");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [['id', 'first_name', 'last_name', 'role', 'joined_at']],
                     'total',
                 ]);

        $this->assertEquals(2, $response->json('total'));

        $roles = collect($response->json('data'))->pluck('role')->sort()->values();
        $this->assertEquals(['member', 'owner'], $roles->toArray());
    }

    public function test_members_returns_404_for_missing_group(): void
    {
        $user = User::factory()->create();

        $this->actingAsApi($user)->getJson('/api/groups/9999/members')->assertStatus(404);
    }

    // ── POST /api/groups/{id}/members/{userId}/promote ────────────────────────

    public function test_promote_succeeds_for_owner(): void
    {
        $owner  = User::factory()->create();
        $member = User::factory()->create();
        $group  = $this->createGroup($owner);

        $group->members()->attach($member->id, ['role' => 'member']);

        $response = $this->actingAsApi($owner)
            ->postJson("/api/groups/{$group->id}/members/{$member->id}/promote");

        $response->assertStatus(200)
                 ->assertJsonPath('user.id', $member->id)
                 ->assertJsonPath('user.role', 'admin');

        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id'  => $member->id,
            'role'     => 'admin',
        ]);
    }

    public function test_promote_fails_for_admin(): void
    {
        $owner  = User::factory()->create();
        $admin  = User::factory()->create();
        $target = User::factory()->create();
        $group  = $this->createGroup($owner);

        $group->members()->attach($admin->id,  ['role' => 'admin']);
        $group->members()->attach($target->id, ['role' => 'member']);

        $this->actingAsApi($admin)
            ->postJson("/api/groups/{$group->id}/members/{$target->id}/promote")
            ->assertStatus(403);
    }

    public function test_promote_fails_for_regular_member(): void
    {
        $owner  = User::factory()->create();
        $member = User::factory()->create();
        $target = User::factory()->create();
        $group  = $this->createGroup($owner);

        $group->members()->attach($member->id, ['role' => 'member']);
        $group->members()->attach($target->id, ['role' => 'member']);

        $this->actingAsApi($member)
            ->postJson("/api/groups/{$group->id}/members/{$target->id}/promote")
            ->assertStatus(403);
    }

    public function test_promote_fails_for_non_member_target(): void
    {
        $owner    = User::factory()->create();
        $outsider = User::factory()->create();
        $group    = $this->createGroup($owner);

        $this->actingAsApi($owner)
            ->postJson("/api/groups/{$group->id}/members/{$outsider->id}/promote")
            ->assertStatus(404);
    }
}
