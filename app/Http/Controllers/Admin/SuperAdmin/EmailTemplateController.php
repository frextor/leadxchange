<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    // ── List all templates ────────────────────────────────────────────────────

    public function index(): View
    {
        $templates = EmailTemplate::orderBy('key')->get()->keyBy('key');

        return view('admin.super_admin.email_templates.index', compact('templates'));
    }

    // ── Edit form ─────────────────────────────────────────────────────────────

    public function edit(string $key): View
    {
        abort_unless(array_key_exists($key, EmailTemplate::TEMPLATES), 404);

        $template = EmailTemplate::firstOrNew(
            ['key' => $key],
            [
                'name'      => EmailTemplate::TEMPLATES[$key]['name'],
                'subject'   => EmailTemplate::TEMPLATES[$key]['default_subject'],
                'body'      => '',
                'variables' => EmailTemplate::TEMPLATES[$key]['variables'],
                'is_active' => true,
            ]
        );

        $meta = EmailTemplate::TEMPLATES[$key];

        return view('admin.super_admin.email_templates.edit', compact('template', 'key', 'meta'));
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function update(Request $request, string $key): RedirectResponse
    {
        abort_unless(array_key_exists($key, EmailTemplate::TEMPLATES), 404);

        $data = $request->validate([
            'subject'   => ['required', 'string', 'max:255'],
            'body'      => ['required', 'string'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $template = EmailTemplate::firstOrNew(['key' => $key]);
        $template->fill([
            'name'      => EmailTemplate::TEMPLATES[$key]['name'],
            'subject'   => $data['subject'],
            'body'      => $data['body'],
            'variables' => EmailTemplate::TEMPLATES[$key]['variables'],
            'is_active' => $data['is_active'],
        ])->save();

        return redirect()->route('admin.super.email-templates.index')
            ->with('success', 'Template "' . EmailTemplate::TEMPLATES[$key]['name'] . '" enregistré.');
    }

    // ── Reset to default ──────────────────────────────────────────────────────

    public function reset(string $key): RedirectResponse
    {
        abort_unless(array_key_exists($key, EmailTemplate::TEMPLATES), 404);

        $meta = EmailTemplate::TEMPLATES[$key];

        $template = EmailTemplate::firstOrNew(['key' => $key]);

        // Re-read the default body from the migration seed via DB
        $seeded = \Illuminate\Support\Facades\DB::table('email_templates')
            ->where('key', $key)
            ->first();

        if ($seeded) {
            $template->fill([
                'name'      => $meta['name'],
                'subject'   => $meta['default_subject'],
                'body'      => $seeded->body,
                'variables' => $meta['variables'],
                'is_active' => true,
            ])->save();
        }

        return redirect()->route('admin.super.email-templates.edit', $key)
            ->with('success', 'Template réinitialisé aux valeurs par défaut.');
    }

    // ── Send test email ───────────────────────────────────────────────────────

    public function sendTest(Request $request, string $key)
    {
        abort_unless(array_key_exists($key, EmailTemplate::TEMPLATES), 404);

        $meta    = EmailTemplate::TEMPLATES[$key];
        $subject = $request->input('subject') ?: $meta['default_subject'];
        $body    = $request->input('body') ?: '';

        $renderedSubject = EmailTemplate::interpolate($subject, $meta['sample']);
        $renderedBody    = EmailTemplate::interpolate($body, $meta['sample']);

        $admin = auth()->user();

        try {
            Mail::html(
                view('emails.db_template', ['content' => $renderedBody, 'emailTitle' => $renderedSubject])->render(),
                function ($message) use ($admin, $renderedSubject) {
                    $message->to($admin->email, $admin->first_name . ' ' . $admin->last_name)
                            ->subject('[TEST] ' . $renderedSubject);
                }
            );

            return response()->json(['message' => 'Email de test envoyé à ' . $admin->email]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    // ── Live preview ──────────────────────────────────────────────────────────

    public function preview(Request $request, string $key): Response
    {
        abort_unless(array_key_exists($key, EmailTemplate::TEMPLATES), 404);

        $meta    = EmailTemplate::TEMPLATES[$key];
        $subject = $request->input('subject', $meta['default_subject']);
        $body    = $request->input('body', '');

        // Interpolate with sample data
        $renderedBody    = EmailTemplate::interpolate($body, $meta['sample']);
        $renderedSubject = EmailTemplate::interpolate($subject, $meta['sample']);

        $html = view('emails.db_template', [
            'content'    => $renderedBody,
            'emailTitle' => $renderedSubject,
        ])->render();

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}
