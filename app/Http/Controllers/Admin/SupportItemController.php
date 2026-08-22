<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportItemController extends Controller
{
    public function index()
    {
        $supportItems = SupportItem::orderBy('order')->get();

        return view('admin.support-item.index', compact('supportItems'));
    }

    public function update(Request $request, SupportItem $supportItem)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'icon' => 'nullable|image|max:2048',
        ]);

        $icon = $supportItem->icon;

        if ($request->hasFile('icon')) {
            if (Storage::disk('public')->exists($supportItem->icon)) {
                Storage::disk('public')->delete($supportItem->icon);
            }

            $icon = Storage::disk('public')->put('support-items', $request->file('icon'));
        }

        $supportItem->update([
            'title' => $request->title,
            'ar_title' => $request->ar_title,
            'description' => $request->description,
            'ar_description' => $request->ar_description,
            'icon' => $icon,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->withSuccess(__('Support item updated successfully'));
    }
}
