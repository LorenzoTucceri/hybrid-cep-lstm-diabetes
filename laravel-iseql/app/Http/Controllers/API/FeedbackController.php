<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class FeedbackController extends Controller {
    // Funziona alla perfezione!
    public function feedbackCount(Request $request) {
        // Ottenimento del numero di feedback.
        $count = Feedback::whereHas('file', function ($query) use ($request) {
            $query->where('patient_id', $request->user()->patient_id);
        })->count();

        return response()->json(
            [
                "success" => true,
                "feedback_count" => $count
            ]);
    }
}
