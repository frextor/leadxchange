<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventCategoryController extends Controller
{
    public function index(): View
    {
        $categories = EventCategory::orderBy('sort_order')->orderBy('label')->paginate(40);
        return view('admin.super_admin.event_categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'key'        => ['required', 'string', 'max:60', 'alpha_dash', 'unique:event_categories,key'],
            'label'      => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        EventCategory::create([
            'key'        => $request->key,
            'label'      => $request->label,
            'sort_order' => $request->sort_order ?? 0,
        ]);
        EventCategory::clearCache();
        ActivityLogger::log('admin.event_category.created', "Catégorie événement \"{$request->label}\" créée");
        return back()->with('success', "Catégorie \"{$request->label}\" créée.");
    }

    public function update(Request $request, EventCategory $eventCategory): RedirectResponse
    {
        $request->validate([
            'label'      => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $eventCategory->update([
            'label'      => $request->label,
            'sort_order' => $request->sort_order ?? $eventCategory->sort_order,
        ]);
        EventCategory::clearCache();
        ActivityLogger::log('admin.event_category.updated', "Catégorie événement \"{$eventCategory->label}\" mise à jour");
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(EventCategory $eventCategory): RedirectResponse
    {
        $label = $eventCategory->label;
        $eventCategory->delete();
        EventCategory::clearCache();
        ActivityLogger::log('admin.event_category.deleted', "Catégorie événement \"{$label}\" supprimée");
        return back()->with('success', "Catégorie \"{$label}\" supprimée.");
    }
}
