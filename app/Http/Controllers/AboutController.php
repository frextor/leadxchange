<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Response;

class AboutController extends Controller
{
    public function show(): Response
    {
        $enabled = SystemSetting::get('about_enabled', '1');
        if ($enabled !== '1') {
            abort(404);
        }

        $title        = SystemSetting::get('about_title',         'À propos de LeadXchange');
        $tagline      = SystemSetting::get('about_tagline',        '');
        $mission      = SystemSetting::get('about_mission',        '');
        $content      = SystemSetting::get('about_content',        '');
        $contactEmail = SystemSetting::get('about_contact_email',  '');
        $foundedYear  = SystemSetting::get('about_founded_year',   date('Y'));
        $ctaLabel     = SystemSetting::get('about_cta_label',      '');
        $ctaUrl       = SystemSetting::get('about_cta_url',        '');
        $year         = date('Y');

        $titleEsc    = htmlspecialchars($title);
        $taglineEsc  = htmlspecialchars($tagline);
        $missionEsc  = nl2br(htmlspecialchars($mission));
        // Content is stored as HTML — strip dangerous tags only
        $contentHtml = strip_tags($content, '<h2><h3><p><ul><ol><li><strong><em><br><a><blockquote><hr><table><thead><tbody><tr><th><td>');

        $missionBlock = $mission ? <<<HTML
        <div class="lx-mission">
            <div class="lx-mission-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
            </div>
            <div class="lx-mission-text">{$missionEsc}</div>
        </div>
HTML : '';

        $contactBlock = $contactEmail ? <<<HTML
        <div class="lx-contact-chip">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <a href="mailto:{$contactEmail}">{$contactEmail}</a>
        </div>
HTML : '';

        $mainContentBlock = trim($contentHtml) ? <<<HTML
        <div class="lx-content">
            {$contentHtml}
        </div>
HTML : '';

        $ctaBlock = ($ctaLabel && $ctaUrl) ? <<<HTML
        <div class="lx-cta-wrap">
            <a href="{$ctaUrl}" class="lx-cta-btn">{$ctaLabel}</a>
        </div>
HTML : '';

        $statsBlock = $foundedYear ? <<<HTML
        <div class="lx-stats">
            <div class="lx-stat">
                <span class="lx-stat-val">{$foundedYear}</span>
                <span class="lx-stat-lbl">Fondé en</span>
            </div>
            <div class="lx-stat-sep"></div>
            <div class="lx-stat">
                <span class="lx-stat-val">100 %</span>
                <span class="lx-stat-lbl">Professionnel</span>
            </div>
            <div class="lx-stat-sep"></div>
            <div class="lx-stat">
                <span class="lx-stat-val">🇫🇷</span>
                <span class="lx-stat-lbl">Fait en France</span>
            </div>
        </div>
HTML : '';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$titleEsc} — LeadXchange</title>
    <meta name="description" content="{$taglineEsc}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #F5F3F0;
            color: #1A1714;
            line-height: 1.7;
        }

        /* ── Nav ── */
        .lx-nav {
            background: #fff;
            border-bottom: 1px solid #E4E0DA;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .lx-logo {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2DD4B0, #14A98C);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800; font-size: 13px; color: #04221C;
            text-decoration: none; flex-shrink: 0;
        }
        .lx-brand {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px; font-weight: 800; color: #1A1714;
            text-decoration: none; letter-spacing: -0.2px;
        }
        .lx-nav-links { margin-left: auto; display: flex; align-items: center; gap: 16px; }
        .lx-nav-link { font-size: 13px; font-weight: 600; color: #726860; text-decoration: none; }
        .lx-nav-link:hover { color: #1A1714; }
        .lx-nav-cta {
            font-size: 13px; font-weight: 700;
            padding: 7px 16px; border-radius: 8px;
            background: #14A98C; color: #fff; text-decoration: none;
            transition: opacity .15s;
        }
        .lx-nav-cta:hover { opacity: .88; }

        /* ── Hero ── */
        .lx-hero {
            background: linear-gradient(135deg, #0f2027, #1a3a4a, #1E8F88);
            color: #fff;
            text-align: center;
            padding: 64px 20px 56px;
        }
        .lx-hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255,255,255,.9);
            margin-bottom: 20px;
        }
        .lx-hero h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(28px, 5vw, 42px);
            font-weight: 800;
            letter-spacing: -0.8px;
            line-height: 1.2;
            max-width: 680px;
            margin: 0 auto 14px;
        }
        .lx-hero-tagline {
            font-size: 16px;
            color: rgba(255,255,255,.72);
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ── Page wrap ── */
        .lx-wrap {
            max-width: 760px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }

        /* ── Stats ── */
        .lx-stats {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            background: #fff;
            border: 1px solid #E4E0DA;
            border-radius: 16px;
            margin: 28px 0;
            overflow: hidden;
        }
        .lx-stat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 16px;
        }
        .lx-stat-val {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #1A1714;
            letter-spacing: -0.4px;
        }
        .lx-stat-lbl { font-size: 11px; color: #A89E94; margin-top: 3px; font-weight: 500; }
        .lx-stat-sep { width: 1px; background: #E4E0DA; align-self: stretch; }

        /* ── Mission ── */
        .lx-mission {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            background: #EAF7F3;
            border: 1px solid #B8E8DC;
            border-radius: 16px;
            padding: 22px 24px;
            margin: 28px 0;
        }
        .lx-mission-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: #14A98C;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .lx-mission-text {
            font-size: 14px;
            color: #0D5A4A;
            line-height: 1.65;
            font-weight: 500;
        }

        /* ── Main content ── */
        .lx-content {
            background: #fff;
            border: 1px solid #E4E0DA;
            border-radius: 16px;
            padding: 36px;
            margin: 12px 0;
        }
        .lx-content h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: #1A1714;
            letter-spacing: -0.2px;
            margin: 32px 0 10px;
            padding-top: 28px;
            border-top: 1px solid #F0EDE8;
        }
        .lx-content h2:first-child { margin-top: 0; padding-top: 0; border-top: none; }
        .lx-content h3 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #14A98C;
            margin: 20px 0 8px;
        }
        .lx-content p { font-size: 14px; color: #3D3530; margin-bottom: 12px; }
        .lx-content ul, .lx-content ol {
            padding-left: 22px;
            margin-bottom: 14px;
        }
        .lx-content li { font-size: 14px; color: #3D3530; margin-bottom: 6px; }
        .lx-content strong { color: #1A1714; font-weight: 600; }
        .lx-content em { color: #14A98C; font-style: italic; }
        .lx-content a { color: #14A98C; }
        .lx-content a:hover { text-decoration: underline; }

        /* ── Contact chip ── */
        .lx-contact-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            border: 1px solid #E4E0DA;
            border-radius: 999px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #1A1714;
            margin: 24px 0;
        }
        .lx-contact-chip a { color: #14A98C; text-decoration: none; }
        .lx-contact-chip a:hover { text-decoration: underline; }

        /* ── CTA ── */
        .lx-cta-wrap {
            text-align: center;
            margin: 40px 0 0;
            padding: 40px;
            background: linear-gradient(135deg, #0f2027, #1E8F88);
            border-radius: 16px;
        }
        .lx-cta-btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 12px;
            background: #fff;
            color: #14A98C;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px;
            font-weight: 800;
            text-decoration: none;
            transition: opacity .15s, transform .15s;
            letter-spacing: -0.2px;
        }
        .lx-cta-btn:hover { opacity: .92; transform: translateY(-1px); }

        /* ── Footer ── */
        .lx-footer {
            text-align: center;
            margin-top: 48px;
            font-size: 12px;
            color: #A89E94;
        }
        .lx-footer a { color: #14A98C; text-decoration: none; }
        .lx-footer a:hover { text-decoration: underline; }

        @media (max-width: 560px) {
            .lx-content { padding: 20px; }
            .lx-mission { flex-direction: column; gap: 12px; }
            .lx-stats { flex-wrap: wrap; }
            .lx-stat-sep { display: none; }
            .lx-stat { flex: 0 0 50%; border-bottom: 1px solid #E4E0DA; }
            .lx-hero { padding: 44px 16px 40px; }
            .lx-nav-links { gap: 10px; }
        }
    </style>
</head>
<body>

<nav class="lx-nav">
    <a href="/" class="lx-logo">LX</a>
    <a href="/" class="lx-brand">LeadXchange</a>
    <div class="lx-nav-links">
        <a href="/legal/cgu" class="lx-nav-link">CGU</a>
        <a href="/register" class="lx-nav-cta">S'inscrire</a>
    </div>
</nav>

<div class="lx-hero">
    <div class="lx-hero-eyebrow">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        À propos
    </div>
    <h1>{$titleEsc}</h1>
    <p class="lx-hero-tagline">{$taglineEsc}</p>
</div>

<div class="lx-wrap">

    {$statsBlock}

    {$missionBlock}

    {$mainContentBlock}

    {$contactBlock}

    {$ctaBlock}

    <div class="lx-footer">
        <p>© {$year} LeadXchange — Tous droits réservés</p>
        <p style="margin-top:6px;">
            <a href="/legal/cgu">CGU</a> &nbsp;·&nbsp;
            <a href="/legal/privacy">Confidentialité</a> &nbsp;·&nbsp;
            <a href="/a-propos">À propos</a>
        </p>
    </div>
</div>

</body>
</html>
HTML;

        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }
}
