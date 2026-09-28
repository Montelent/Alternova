<?php

namespace App\Http\Controllers;

use App\Models\ProprietaryTool;
use Illuminate\View\View;

class EmbedController extends Controller
{
    public function tool(ProprietaryTool $tool): View
    {
        abort_unless($tool->is_published, 404);

        $alternatives = $tool->publishedAlternatives()
            ->with('repoMetric')
            ->orderByDesc('overall_health_score')
            ->limit(8)
            ->get();

        return view('embed.tool', [
            'tool' => $tool,
            'alternatives' => $alternatives,
        ]);
    }
}
