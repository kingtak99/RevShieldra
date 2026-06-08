<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $business = $this->ensurePageAccess('dashboard');

        $locations = $this->accessibleLocations($request, $business);
        $feedback = $this->applyFeedbackFilters(Feedback::query(), $request, $business)
            ->with('location')
            ->get();

        $locationAction = null;
        $locationActionLabel = null;

        if (Auth::user()->canAccessPage('locations', $business)) {
            if (Auth::user()->isOwnerOfBusiness($business)) {
                $locationAction = route('locations.create');
                $locationActionLabel = 'Add location';
            } else {
                $locationAction = route('locations.index');
                $locationActionLabel = 'View locations';
            }
        }

        $allLocations = Auth::user()->isOwnerOfBusiness($business)
            ? $business->locations()->latest()->get()
            : $business->locations()->whereIn('id', Auth::user()->allowedLocationIds($business))->latest()->get();

        return view('dashboard', [
            'business' => $business,
            'locationCount' => $locations->count(),
            'totalScans' => $locations->count() * 1,
            'averageRating' => $feedback->avg('rating') ?: 0,
            'positiveRatings' => $feedback->where('rating', '>=', 4)->count(),
            'negativeRatings' => $feedback->where('rating', '<=', 3)->count(),
            'locations' => $locations,
            'allLocations' => $allLocations,
            'locationAction' => $locationAction,
            'locationActionLabel' => $locationActionLabel,
        ]);
    }
}
