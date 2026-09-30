<?php

namespace App\Http\Controllers;

use App\Models\CollectionSubmission;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CollectionSubmissionStatusController extends Controller
{
    public function __invoke(string $token): View
    {
        abort_unless(Schema::hasTable('collection_submissions'), 404);

        $submission = CollectionSubmission::query()
            ->where('tracking_token', $token)
            ->firstOrFail();

        return view('pages.collection-submission-status', [
            'submission' => $submission,
        ]);
    }
}
