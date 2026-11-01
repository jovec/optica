<?php

namespace App\Http\Controllers;

use App\Models\Examination;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PrintExaminationController extends Controller
{
    /**
     * Show the examination as a printable prescription.
     */
    public function __invoke(Examination $examination): View
    {
        Gate::authorize('view', $examination);

        $examination->load('patient', 'examiner');

        return view('examinations.print', [
            'examination' => $examination,
            'organization' => app('currentOrganization'),
        ]);
    }
}
