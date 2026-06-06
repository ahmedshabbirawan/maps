<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(UpdateProfilePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->route('profile.edit', ['tab' => 'password'])
            ->with('success', 'Your password has been updated.');
    }
}
