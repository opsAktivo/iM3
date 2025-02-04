<?php

namespace App\Http\Controllers;

use App\Models\VitalSignIm3Json;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    public function index() {
        $examinationResults = VitalSignIm3Json::orderBy('machine_timestamp', 'desc')->get();

        return view('examinations', [
            'examinationResults' => $examinationResults,
        ]);
    }
}
