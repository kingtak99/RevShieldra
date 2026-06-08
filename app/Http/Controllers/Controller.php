<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function resolveBusiness(): ?Business
    {
        $user = Auth::user();

        return $user?->businessesOwned()->first() ?? $user?->businesses()->first();
    }

    protected function ensurePageAccess(string $page): ?Business
    {
        $business = $this->resolveBusiness();

        abort_if(! $business || ! Auth::user()->canAccessPage($page, $business), 403, 'You do not have permission to view this page.');

        return $business;
    }

    protected function parseFilterDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function accessibleLocationIds(Request $request, Business $business): array
    {
        $allowedLocationIds = Auth::user()->isOwnerOfBusiness($business)
            ? $business->locations()->pluck('id')->toArray()
            : Auth::user()->allowedLocationIds($business);

        $requestedIds = (array) $request->input('locations', []);
        $selectedIds = array_filter($requestedIds, function ($id) use ($allowedLocationIds) {
            return in_array($id, $allowedLocationIds);
        });

        return count($selectedIds) ? array_map('intval', $selectedIds) : $allowedLocationIds;
    }

    protected function accessibleLocations(Request $request, Business $business)
    {
        return $business->locations()->whereIn('id', $this->accessibleLocationIds($request, $business))->latest()->get();
    }

    protected function applyFeedbackFilters($query, Request $request, Business $business)
    {
        $locationIds = $this->accessibleLocationIds($request, $business);

        if (! empty($locationIds)) {
            $query->whereIn('location_id', $locationIds);
        }

        if ($from = $this->parseFilterDate($request->input('from'))) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $this->parseFilterDate($request->input('to'))) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }
}
