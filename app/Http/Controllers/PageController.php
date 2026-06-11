<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page->title}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
               background: #f0f4f8; color: #0d2b45; padding: 24px 20px; line-height: 1.7; }
        h1 { font-size: 22px; font-weight: 700; margin-bottom: 20px; color: #0d2b45; }
        h2 { font-size: 17px; font-weight: 600; margin: 20px 0 8px; color: #1e8f88; }
        p  { font-size: 14px; margin-bottom: 12px; }
        ul { padding-left: 18px; margin-bottom: 12px; }
        li { font-size: 14px; margin-bottom: 6px; }
        .updated { font-size: 12px; color: #9ba8b7; margin-bottom: 24px; }
    </style>
</head>
<body>
    <h1>{$page->title}</h1>
    <p class="updated">Dernière mise à jour : {$page->updated_at->format('d/m/Y')}</p>
    {$page->content}
</body>
</html>
HTML;

        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }
}
