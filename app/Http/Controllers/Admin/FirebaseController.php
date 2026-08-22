<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneraleSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class FirebaseController extends Controller
{
    public function index()
    {
        $fileName = 'firebase_credentials.json';
        $hasConfig = Storage::exists($fileName);

        $projectId = $hasConfig ? json_decode(Storage::get($fileName), true)['project_id'] : $projectId = null;

        $filePath = $hasConfig ? Storage::url($fileName) : null;

        $fcmWebConfig = GeneraleSetting::first()?->fcm_web_config;
        $fcmWebConfig = $fcmWebConfig ? json_decode($fcmWebConfig, true) : null;

        return view('admin.firebase.index', compact('hasConfig', 'filePath', 'projectId', 'fcmWebConfig'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $file = $request->file('file');

        $json = json_decode($file->get(), true);

        if (array_key_exists('type', $json) && array_key_exists('project_id', $json) && array_key_exists('private_key', $json) && array_key_exists('client_email', $json) && array_key_exists('client_id', $json)) {
            $fileExits = file_exists(storage_path('app/public/firebase_credentials.json'));
            if ($fileExits) {
                unlink(storage_path('app/public/firebase_credentials.json'));
            }
            $file->move(storage_path('app/public'), 'firebase_credentials.json');

            return back()->withSuccess('Firebase config updated successfully');
        }

        return back()->withError('Sorry! the selected file is not a valid firebase config file');
    }

    public function updateWebConfig(Request $request)
    {
        $request->validate([
            'fcm_web_config' => 'nullable|json',
        ]);

        $setting = GeneraleSetting::first();
        if ($setting) {
            $setting->update([
                'fcm_web_config' => $request->fcm_web_config,
            ]);

            Cache::forget('generale_setting');
        }

        return back()->withSuccess('Firebase Web SDK config updated successfully');
    }
}
