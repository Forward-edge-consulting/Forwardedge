<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminEventCategoryController extends Controller
{
    public function index()
    {
        $categories = EventCategory::withCount('events')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.pages.events.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80|unique:event_categories,name']);
        EventCategory::create(['name' => trim($data['name']), 'sort_order' => ((int) EventCategory::max('sort_order')) + 10]);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, EventCategory $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('event_categories', 'name')->ignore($category->id)],
            'sort_order' => 'nullable|integer|min:0|max:65000',
        ]);
        $category->update(['name' => trim($data['name']), 'sort_order' => $data['sort_order'] ?? $category->sort_order]);

        return back()->with('success', 'Category saved.');
    }

    public function destroy(EventCategory $category)
    {
        $category->delete();

        return back()->with('success', 'Category deleted. Its events are now uncategorised.');
    }
}
