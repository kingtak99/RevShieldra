<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || ! $user->subscription) {
            return $next($request);
        }

        if ($user->subscription->isExpired()) {
            return redirect()->route('settings.index')
                ->with('error', 'Your subscription has expired. Renew your plan to restore full access.');
        }

        return $next($request);
    }
}
