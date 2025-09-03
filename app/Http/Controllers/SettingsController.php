<?php
// app/Http/Controllers/SettingsController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $settings = [
            'theme' => $user->theme ?? 'dark',
            'language' => $user->language ?? 'en',
            'dateFormat' => $user->date_format ?? 'MM/DD/YYYY',
            'timeFormat' => $user->time_format ?? '12h',
        ];

        return Inertia::render('Settings/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio ?? '',
            ],
            'settings' => $settings
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'bio' => 'nullable|string|max:500'
        ]);

        $userId = Auth::id();

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'bio' => $request->bio,
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'theme' => 'nullable|in:light,dark,system',
            'language' => 'nullable|in:en,pt,es,fr,it,de',
            'dateFormat' => 'nullable|in:MM/DD/YYYY,DD/MM/YYYY,YYYY-MM-DD',
            'timeFormat' => 'nullable|in:12h,24h'
        ]);

        $userId = Auth::id();
        $updateData = [];

        if ($request->has('theme')) {
            $updateData['theme'] = $request->theme;
        }
        if ($request->has('language')) {
            $updateData['language'] = $request->language;
            session(['locale' => $request->language]);
            app()->setLocale($request->language);
        }
        if ($request->has('dateFormat')) {
            $updateData['date_format'] = $request->dateFormat;
        }
        if ($request->has('timeFormat')) {
            $updateData['time_format'] = $request->timeFormat;
        }

        if (!empty($updateData)) {
            $updateData['updated_at'] = now();

            DB::table('users')
                ->where('id', $userId)
                ->update($updateData);
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    public function changeLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,pt,es,fr,it,de'
        ]);

        $userId = Auth::id();

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'language' => $request->language,
                'updated_at' => now()
            ]);

        session(['locale' => $request->language]);
        app()->setLocale($request->language);

        return redirect()->back()->with('success', 'Language updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors([
                'current_password' => 'The current password is incorrect.'
            ]);
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    public function exportData()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        $userData = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio ?? '',
                'created_at' => $user->created_at
            ],
            'tasks' => $user->tasks->map(function ($task) {
                return [
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date,
                    'created_at' => $task->created_at,
                    'completed_at' => $task->completed_at
                ];
            }),
            'timeBoxes' => $user->timeBoxes->map(function ($timeBox) {
                return [
                    'title' => $timeBox->title,
                    'type' => $timeBox->type,
                    'start_at' => $timeBox->start_at,
                    'end_at' => $timeBox->end_at,
                    'created_at' => $timeBox->created_at
                ];
            }),
            'exported_at' => now()->toISOString()
        ];

        $json = json_encode($userData, JSON_PRETTY_PRINT);
        $filename = 'taskflow-export-' . now()->format('Y-m-d') . '.json';

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function deleteAccount(Request $request)
    {
        if ($request->has('confirmation')) {
            $request->validate([
                'confirmation' => 'required|in:DELETE'
            ]);
        }

        $userId = Auth::id();

        DB::table('task_time_box')->whereIn('task_id', function ($query) use ($userId) {
            $query->select('id')->from('tasks')->where('user_id', $userId);
        })->delete();

        DB::table('tasks')->where('user_id', $userId)->delete();
        DB::table('time_boxes')->where('user_id', $userId)->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        DB::table('users')->where('id', $userId)->delete();

        return redirect('/')->with('message', 'Account deleted successfully');
    }
}
