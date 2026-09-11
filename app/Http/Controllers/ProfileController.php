<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\DeleteProfileRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\PostImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('status', 'profile-updated');
    }

    public function destroy(DeleteProfileRequest $request, PostImageStorage $images): RedirectResponse
    {
        $user = $request->user();
        $postImages = $user->posts()
            ->where(function ($query): void {
                $query
                    ->whereNotNull('original_image_path')
                    ->orWhereNotNull('processed_image_path');
            })
            ->get(['original_image_path', 'processed_image_path']);

        Auth::guard('web')->logout();
        $user->delete();

        $postImages->each(
            fn ($post) => $images->delete($post->original_image_path, $post->processed_image_path),
        );

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
