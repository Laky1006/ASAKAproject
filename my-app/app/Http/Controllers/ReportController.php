<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'review_id' => 'nullable|exists:reviews,id',
            'service_id' => 'nullable|exists:services,id',
        ]);
    
        if (!$request->review_id && !$request->service_id) {
            return response()->json(['error' => 'You must report either a review or a service.'], 422);
        }
    
        $report = Report::create([
            'user_id'   => auth()->id(),
            'review_id' => $request->review_id,
            'service_id'=> $request->service_id,
            'reason'    => $request->reason,
        ]);
    
        return response()->json(['message' => 'Report submitted successfully', 'report' => $report], 201);
    }
    
}
