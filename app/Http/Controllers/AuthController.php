<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Mail\TrialNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Auth::user()->forceFill(['last_login_at' => now()])->save();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $business = Business::create([
            'owner_id' => $user->id,
            'name' => $request->input('business_name', $user->name . "'s Business"),
            'plan' => 'free',
        ]);

        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'viewer']);

        $business->members()->attach($user->id, ['role_id' => $ownerRole->id]);

        $freePlan = SubscriptionPlan::firstOrCreate([
            'name' => 'Free',
        ], [
            'price' => 0,
            'interval' => 'monthly',
            'max_branches' => 1,
            'is_free' => true,
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $freePlan->id,
            'lemon_squeezy_subscription_id' => null,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(30),
            'reminder_7_days_sent' => false,
            'reminder_1_day_sent' => false,
            'expiry_notice_sent' => false,
        ]);

        $business->update(['plan' => 'trial']);

        try {
            Mail::to($user->email)->send(new TrialNotification(
                user: $user,
                subscription: $subscription,
                business: $business,
                messageType: 'welcome'
            ));
        } catch (\Throwable $e) {
            logger()->error('Trial welcome email failed: '.$e->getMessage());
        }

        $user->forceFill(['last_login_at' => now()])->save();
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
