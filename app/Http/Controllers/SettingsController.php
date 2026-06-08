<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $business = $this->ensurePageAccess('settings');
        $subscription = auth()->user()->subscription;

        return view('settings.index', [
            'business' => $business,
            'subscription' => $subscription,
            'daysRemaining' => $subscription ? $subscription->daysRemaining() : null,
        ]);
    }

    public function update(Request $request)
    {
        $business = $this->ensurePageAccess('settings');

        $data = $request->validate([
            'email_locale' => ['required', 'in:en,ar'],
        ]);

        $business->update([
            'email_locale' => $data['email_locale'],
        ]);

        return back()->with('success', __('ui.save_settings').'.');
    }
}
