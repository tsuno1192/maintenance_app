<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string', 'max:255'],
        ]);

        $expected = (string) (config('services.ui_token') ?: config('services.api_token'));

        if ($expected === '' || ! hash_equals($expected, $validated['access_token'])) {
            return back()
                ->withInput()
                ->withErrors(['access_token' => 'アクセストークンが正しくありません。']);
        }

        $request->session()->regenerate();
        $request->session()->put('action_list_authenticated', true);

        return redirect()->intended(route('tasks.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
