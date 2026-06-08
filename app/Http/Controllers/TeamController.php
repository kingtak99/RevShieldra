<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    private array $availablePages = [
        'dashboard' => 'ui.dashboard',
        'locations' => 'ui.locations',
        'feedback' => 'ui.feedback',
        'reports' => 'ui.reports',
    ];

    public function index()
    {
        $business = Auth::user()->businessesOwned()->with(['members', 'locations'])->first();

        abort_if(!$business, 403, 'Only business owners can manage team members.');

        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'viewer']);

        return view('team.index', [
            'business' => $business,
            'members' => $business->members,
            'branches' => $business->locations,
            'pages' => $this->availablePages,
            'roles' => Role::query()->where('name', '!=', 'owner')->get(),
            'roleNames' => Role::query()->pluck('name', 'id')->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $business = Auth::user()->businessesOwned()->first();

        abort_if(!$business, 403, 'Only business owners may add team members.');

        $pageKeys = array_keys($this->availablePages);
        $locationIds = $business->locations()->pluck('id')->toArray();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
            'pages' => ['required', 'array', 'min:1'],
            'pages.*' => ['in:' . implode(',', $pageKeys)],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['in:' . implode(',', $locationIds)],
        ]);

        $validator->after(function ($validator) use ($request, $business) {
            $existingUser = User::query()->where('email', '=', $request->input('email'))->first();

            if ($existingUser && $business->members()->where('user_id', '=', $existingUser->id)->exists()) {
                $validator->errors()->add('email', 'This email already belongs to an existing team member.');
            }

            if (! $existingUser && ! $request->filled('password')) {
                $validator->errors()->add('password', 'A password is required for new team members.');
            }
        });

        $data = $validator->validate();
        $existingUser = User::query()->where('email', '=', $data['email'])->first();

        if ($existingUser) {
            $user = $existingUser;
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        }

        $business->members()->attach($user->id, [
            'role_id' => $data['role_id'],
            'allowed_pages' => json_encode($data['pages']),
            'allowed_locations' => json_encode($data['locations'] ?? []),
        ]);

        return back()->with('success', 'Team member added successfully.');
    }
}
