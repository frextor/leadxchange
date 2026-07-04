<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::orderBy('slug')->get();
        return view('admin.super_admin.pages.index', compact('pages'));
    }

    public function edit(Page $page): View
    {
        return view('admin.super_admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $page->update([
            'title'   => $request->title,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Page "' . $page->title . '" mise à jour et publiée.');
    }
}
