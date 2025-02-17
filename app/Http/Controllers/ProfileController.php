<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'profile' => $request->user()->profile,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // 更新用户基本信息
        $request->user()->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // 处理头像上传
        if ($request->hasFile('avatar')) {
            try {
                // 删除旧头像
                if ($request->user()->profile?->avatar) {
                    Storage::disk('public')->delete($request->user()->profile->avatar);
                }

                $avatarPath = $request->file('avatar')->store('avatars', 'public');

                // 更新或创建 profile
                $request->user()->profile()->updateOrCreate(
                    ['user_id' => $request->user()->id],
                    [
                        'avatar' => $avatarPath,
                        'bio' => $validated['bio'],
                        'social_links' => $validated['social_links'],
                    ]
                );
            } catch (\Exception $e) {
                return Redirect::back()
                    ->withErrors(['avatar' => $e->getMessage()])
                    ->withInput();
            }
        } else {
            // 只更新其他字段
            $request->user()->profile()->updateOrCreate(
                ['user_id' => $request->user()->id],
                [
                    'bio' => $validated['bio'],
                    'social_links' => $validated['social_links'],
                ]
            );
        }

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
