<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        $content = $page->content;
        $title   = htmlspecialchars($page->title);
        $updated = $page->updated_at->format('d/m/Y');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} — LeadXchange</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Geist', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #F4F5F8;
            color: #2E3850;
            line-height: 1.75;
            padding: 0;
        }
        /* Nav */
        .lx-nav {
            background: #fff;
            border-bottom: 1px solid #E5E7EE;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .lx-logo {
            width: 32px; height: 32px;
            border-radius: 9px;
            background: linear-gradient(135deg, #2DD4B0, #14A98C);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 13px; color: #04221C;
            text-decoration: none; flex-shrink: 0;
        }
        .lx-brand { font-size: 15px; font-weight: 700; color: #0F1623; text-decoration: none; }
        .lx-back {
            margin-left: auto;
            font-size: 13px;
            color: #14A98C;
            text-decoration: none;
            font-weight: 600;
        }
        .lx-back:hover { text-decoration: underline; }

        /* Page */
        .lx-page {
            max-width: 800px;
            margin: 32px auto;
            padding: 0 20px 64px;
        }

        /* Header card */
        .lx-header {
            background: #fff;
            border: 1px solid #E5E7EE;
            border-radius: 20px;
            padding: 32px 36px;
            margin-bottom: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .lx-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(20,169,140,.1);
            color: #0B8F76;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 4px 12px;
            border-radius: 99px;
            border: 1px solid rgba(20,169,140,.2);
            margin-bottom: 14px;
        }
        .lx-header h1 {
            font-size: 26px;
            font-weight: 800;
            color: #0F1623;
            letter-spacing: -.3px;
            line-height: 1.3;
            margin-bottom: 10px;
        }
        .lx-meta {
            font-size: 12.5px;
            color: #9097AC;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .lx-meta span { display: flex; align-items: center; gap: 5px; }

        /* Content card */
        .lx-content {
            background: #fff;
            border: 1px solid #E5E7EE;
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }

        /* Typography */
        .lx-content h2 {
            font-size: 17px;
            font-weight: 700;
            color: #0F1623;
            margin: 32px 0 10px;
            padding-top: 24px;
            border-top: 1px solid #EEF0F4;
        }
        .lx-content h2:first-child { margin-top: 0; padding-top: 0; border-top: none; }
        .lx-content h3 {
            font-size: 14.5px;
            font-weight: 600;
            color: #14A98C;
            margin: 20px 0 8px;
        }
        .lx-content p {
            font-size: 14px;
            color: #2E3850;
            margin-bottom: 14px;
        }
        .lx-content ul, .lx-content ol {
            padding-left: 20px;
            margin-bottom: 14px;
        }
        .lx-content li {
            font-size: 14px;
            color: #2E3850;
            margin-bottom: 6px;
        }
        .lx-content strong { color: #0F1623; font-weight: 600; }
        .lx-content em { color: #14A98C; }
        .lx-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 13px;
        }
        .lx-content th {
            background: #F4F5F8;
            padding: 10px 14px;
            text-align: left;
            font-weight: 600;
            color: #0F1623;
            border: 1px solid #E5E7EE;
        }
        .lx-content td {
            padding: 10px 14px;
            border: 1px solid #E5E7EE;
            vertical-align: top;
        }
        .lx-content tr:hover td { background: #FAFBFC; }

        /* Alert box */
        .lx-alert {
            background: rgba(20,169,140,.06);
            border: 1px solid rgba(20,169,140,.2);
            border-radius: 12px;
            padding: 14px 18px;
            margin: 16px 0;
            font-size: 13.5px;
            color: #0B6B5A;
        }

        /* Footer */
        .lx-footer {
            text-align: center;
            margin-top: 32px;
            font-size: 12px;
            color: #9097AC;
        }
        .lx-footer a { color: #14A98C; text-decoration: none; }
        .lx-footer a:hover { text-decoration: underline; }

        @media (max-width: 600px) {
            .lx-header, .lx-content { padding: 20px; }
            .lx-header h1 { font-size: 20px; }
        }
    </style>
</head>
<body>

<nav class="lx-nav">
    <a href="/" class="lx-logo">LX</a>
    <a href="/" class="lx-brand">LeadXchange</a>
    <a href="javascript:history.back()" class="lx-back">← Retour</a>
</nav>

<div class="lx-page">
    <div class="lx-header">
        <div class="lx-header-badge">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Document légal officiel
        </div>
        <h1>{$title}</h1>
        <div class="lx-meta">
            <span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Dernière mise à jour : {$updated}
            </span>
            <span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                X-tensia SAS — RCS Nanterre 999 916 190
            </span>
        </div>
    </div>

    <div class="lx-content">
        {$content}
    </div>

    <div class="lx-footer">
        <p>© {{ date('Y') }} X-tensia SAS — Tous droits réservés</p>
        <p style="margin-top:6px;">
            <a href="/legal/cgu">CGU</a> &nbsp;·&nbsp;
            <a href="/legal/privacy">Politique de confidentialité</a> &nbsp;·&nbsp;
            <a href="mailto:contact@leadxchange.com">Contact</a>
        </p>
    </div>
</div>

</body>
</html>
HTML;

        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }
}
