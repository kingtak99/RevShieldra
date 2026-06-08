<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $business = $this->ensurePageAccess('feedback');
        $allLocations = Auth::user()->isOwnerOfBusiness($business)
            ? $business->locations()->latest()->get()
            : $business->locations()->whereIn('id', Auth::user()->allowedLocationIds($business))->latest()->get();

        $feedback = $this->applyFeedbackFilters(Feedback::query(), $request, $business)
            ->with('location')
            ->get();

        return view('feedback.index', [
            'feedback' => $feedback,
            'business' => $business,
            'allLocations' => $allLocations,
        ]);
    }
}
