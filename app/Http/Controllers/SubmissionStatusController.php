<?php

namespace App\Http\Controllers;

use App\Models\AlternativeSubmission;
use Illuminate\View\View;

class SubmissionStatusController extends Controller
{
    public function __invoke(string $token): View
    {
        $submission = AlternativeSubmission::query()
            ->where('tracking_token', $token)
            ->firstOrFail();

        return view('pages.submission-status', [
            'submission' => $submission,
        ]);
    }
}
