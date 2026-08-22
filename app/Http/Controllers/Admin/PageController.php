<?php

namespace App\Http\Controllers\Admin;

use App\Services\Chat;
use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::all();

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'title_ar' => 'nullable|string|max:255',
            'content' => 'required',
            'content_ar' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        Page::create([
            'title' => $request->title,
            'title_ar' => $request->title_ar,
            'slug' => Str::slug($request->title),
            'url' => 'page/' . Str::slug($request->title),
            'description' => $request->content,
            'description_ar' => $request->content_ar,
            'icon' => $request->icon,
            'is_active' => true,
            'is_default' => false,
            'is_editable' => true,
        ]);

        return to_route('admin.page.index')->withSuccess('created successfully');
    }

    public function show(Page $page)
    {
        if (! $page) {
            return back()->withError('page not found');
        }

        return view('admin.pages.show', compact('page'));
    }

    public function edit(Page $page)
    {
        if (! $page) {
            return back()->withError('page not found');
        }

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        if (! $page) {
            return back()->withError('page not found');
        }

        if (! $page->is_editable) {

            return to_route('admin.page.index')->withSuccess('updated successfully');
        }

        $request->validate([
            'title' => 'required',
            'title_ar' => 'nullable|string|max:255',
            'content' => 'required',
            'content_ar' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        $page->update([
            'title' => $request->title,
            'title_ar' => $request->title_ar ?? $page->title_ar,
            'slug' => $page->is_default ? $page->slug : Str::slug($request->title),
            'url' => $page->is_default ? $page->slug : 'page/' . Str::slug($request->title),
            'description' => $request->content,
            'description_ar' => $request->content_ar ?? $page->description_ar,
            'icon' => $request->filled('icon') ? $request->icon : $page->getRawOriginal('icon'),
        ]);

        return to_route('admin.page.index')->withSuccess('updated successfully');
    }

    public function destroy(Page $page)
    {
        if ($page->is_default) {
            return back()->withError('cannot delete default page');
        }

        $page->delete();

        return back()->withSuccess('deleted successfully');
    }

    /**
     * generate ai data
     */

    public function generateAIData(Request $request)
    {

        try {

            $request->validate([
                'title' => 'required|string',
            ]);

            $chat = new Chat();
            $chat->systemMessage($request->title);

            $question = str_replace(
                ['{title}'],
                [$request->title],
                generaleSetting()->page_description
            );

            $question .= "Format the description with proper HTML tags (<p>, <h2>, <ul>, <li>) so it can be directly used inside CKEditor.Do not include extra phrases like 'The page name is','```html', ‘Sure’ or ‘Here is’.Just output the final formatted page description.";

            $response = $chat->send($question);

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
