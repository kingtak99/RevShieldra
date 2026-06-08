<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Location;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    protected function planLimit(string $plan): int
    {
        return match ($plan) {
            'basic' => 3,
            'pro' => 10,
            default => 1,
        };
    }

    protected function resolveBusiness(): ?Business
    {
        $user = Auth::user();

        return $user->businessesOwned()->first() ?? $user->businesses()->first();
    }

    protected function ensureBusinessExists()
    {
        $user = Auth::user();
        $business = $this->resolveBusiness();

        if ($business) {
            return $business;
        }

        $business = Business::create([
            'owner_id' => $user->id,
            'name' => $user->name . "'s Business",
            'plan' => 'free',
            'email_locale' => 'en',
        ]);

        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $business->members()->attach($user->id, ['role_id' => $ownerRole->id]);

        return $business;
    }

    public function index()
    {
        $business = $this->resolveBusiness();
        abort_if(! $business || ! Auth::user()->canAccessPage('locations', $business), 403, 'You do not have permission to view this page.');

        $locationsQuery = $business->locations()->withCount('feedback')->latest();

        if (! Auth::user()->isOwnerOfBusiness($business)) {
            $allowedLocationIds = Auth::user()->allowedLocationIds($business);
            if (empty($allowedLocationIds)) {
                $locations = collect();
            } else {
                $locations = $locationsQuery->whereIn('id', $allowedLocationIds)->get();
            }
        } else {
            $locations = $locationsQuery->get();
        }

        foreach ($locations as $location) {
            if (!$location->qr_code || !Storage::disk('public')->exists($location->qr_code)) {
                $location->update(['qr_code' => $location->generateQrCode()]);
            }
        }

        return view('locations.index', [
            'business' => $business,
            'locations' => $locations,
        ]);
    }

    protected function ensureLocationOwnership(Location $location): Location
    {
        $business = $this->resolveBusiness();
        abort_if(! $business || ! Auth::user()->isOwnerOfBusiness($business) || $location->business_id !== $business->id, 403, 'Only business owners may manage this location.');

        return $location;
    }

    public function edit(Location $location)
    {
        $this->ensureLocationOwnership($location);

        return view('locations.edit', [
            'business' => $this->resolveBusiness(),
            'location' => $location,
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $this->ensureLocationOwnership($location);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'google_maps_url' => ['required', 'string', 'max:1024'],
            'complaint_email' => ['required', 'email', 'max:255'],
        ]);

        $googleMapsUrl = $data['google_maps_url'];

        if (! str_contains($googleMapsUrl, 'google.com/maps') && ! str_contains($googleMapsUrl, 'g.page') && ! str_contains($googleMapsUrl, 'maps.app.goo.gl')) {
            return back()->withErrors(['google_maps_url' => 'Google Maps URL must be a valid Google Maps link'])->withInput();
        }

        $location->update([
            'name' => $data['name'],
            'google_maps_url' => $data['google_maps_url'],
            'complaint_email' => $data['complaint_email'],
        ]);

        return Redirect::route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $this->ensureLocationOwnership($location);

        if ($location->qr_code && Storage::disk('public')->exists($location->qr_code)) {
            Storage::disk('public')->delete($location->qr_code);
        }

        $location->delete();

        return Redirect::route('locations.index')->with('success', 'Branch deleted. Previous ratings and feedback are permanently removed, so please export the branch report before deletion.');
    }

    public function create()
    {
        $business = $this->resolveBusiness();
        abort_if(! $business || ! Auth::user()->isOwnerOfBusiness($business), 403, 'Only business owners may manage locations.');

        return view('locations.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $business = $this->resolveBusiness();

        if (! $business) {
            $business = $this->ensureBusinessExists();
        }

        abort_if(! Auth::user()->isOwnerOfBusiness($business), 403, 'Only business owners may add locations.');

        $limit = $this->planLimit($business->plan);

        if ($business->locations()->count() >= $limit) {
            return Redirect::route('locations.index')->with('upgrade_prompt', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'google_maps_url' => ['required', 'string', 'max:1024'],
            'complaint_email' => ['required', 'email', 'max:255'],
        ]);

        $googleMapsUrl = $data['google_maps_url'];

        if (! str_contains($googleMapsUrl, 'google.com/maps') && ! str_contains($googleMapsUrl, 'g.page') && ! str_contains($googleMapsUrl, 'maps.app.goo.gl')) {
            return back()->withErrors(['google_maps_url' => 'Google Maps URL must be a valid Google Maps link'])->withInput();
        }

        $location = Location::create([
            'business_id' => $business->id,
            'name' => $data['name'],
            'google_maps_url' => $data['google_maps_url'],
            'complaint_email' => $data['complaint_email'],
            'feedback_url' => Str::slug($data['name']) . '-' . Str::random(10),
            'qr_code' => '',
        ]);

        $location->update(['qr_code' => $location->generateQrCode()]);

        return Redirect::route('locations.index')->with('success', 'Location added successfully.');
    }
}
