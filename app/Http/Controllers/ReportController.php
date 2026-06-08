<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $business = $this->ensurePageAccess('reports');
        $allLocations = Auth::user()->isOwnerOfBusiness($business)
            ? $business->locations()->latest()->get()
            : $business->locations()->whereIn('id', Auth::user()->allowedLocationIds($business))->latest()->get();

        $feedback = $this->applyFeedbackFilters(Feedback::query(), $request, $business)
            ->with('location')
            ->get();

        return view('reports.index', [
            'business' => $business,
            'feedback' => $feedback,
            'allLocations' => $allLocations,
        ]);
    }

    public function exportRatings(Request $request)
    {
        $business = $this->ensurePageAccess('reports');
        $feedback = $this->applyFeedbackFilters(Feedback::query(), $request, $business)
            ->with('location')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ratings-report.csv"',
        ];

        $callback = function () use ($feedback) {
            echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel compatibility
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Rating', 'Message', 'Location']);

            foreach ($feedback as $entry) {
                fputcsv($handle, [
                    $entry->created_at->timezone('Asia/Amman')->format('Y-m-d H:i:s'),
                    $entry->rating,
                    $entry->message,
                    $entry->location->name,
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function exportComplaints(Request $request)
    {
        $business = $this->ensurePageAccess('reports');
        $feedback = $this->applyFeedbackFilters(Feedback::query(), $request, $business)
            ->with('location')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="complaints-report.csv"',
        ];

        $callback = function () use ($feedback) {
            echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel compatibility
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Rating', 'Message', 'Location']);

            foreach ($feedback as $entry) {
                fputcsv($handle, [
                    $entry->created_at->timezone('Asia/Amman')->format('Y-m-d H:i:s'),
                    $entry->rating,
                    $entry->message,
                    $entry->location->name,
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
