<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\File;

/**
 * @author Davide Rossi
 * @author Lorenzo Tucceri Cimini
 */
class CsvController extends Controller {
    public function csvCount() {
        // Ottenimento del numero di file CSV.
        $count = File::count();

        return response()->json(
            [
                "success" => true,
                "csv_count" => $count
            ]);
    }
}
