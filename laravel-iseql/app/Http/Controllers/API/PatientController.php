<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Patient;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class PatientController extends Controller {
    public function patientCount() {
        // Ottenimento del numero di pazienti.
        $count = Patient::count();

        return response()->json(
            [
                "success" => true,
                "patient_count" => $count
            ]);
    }
}
