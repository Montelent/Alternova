<?php

namespace App\Http\Controllers;

use App\Services\FavoriteService;
use App\Services\WatchlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountExportController extends Controller
{
    public function favorites(Request $request): StreamedResponse
    {
        abort_unless(Auth::check(), 401);

        $rows = app(FavoriteService::class)->list();

        return $this->csv('alternova-favorites.csv', $rows, function ($alt) {
            return [
                $alt->name,
                $alt->slug,
                $alt->proprietaryTool?->name ?? '',
                number_format((float) $alt->overall_health_score, 1, '.', ''),
                $alt->license_type ?? '',
                $alt->repo_url ?? '',
                route('alternatives.show', $alt),
            ];
        });
    }

    public function watchlist(Request $request): StreamedResponse
    {
        abort_unless(Auth::check(), 401);

        $rows = app(WatchlistService::class)->listFor(Auth::user());

        return $this->csv('alternova-watchlist.csv', $rows, function ($alt) {
            return [
                $alt->name,
                $alt->slug,
                $alt->proprietaryTool?->name ?? '',
                number_format((float) $alt->overall_health_score, 1, '.', ''),
                $alt->license_type ?? '',
                $alt->repo_url ?? '',
                route('alternatives.show', $alt),
            ];
        });
    }

    /**
     * @param  \Illuminate\Support\Collection  $rows
     * @param  callable  $map
     */
    protected function csv(string $filename, $rows, callable $map): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return response()->streamDownload(function () use ($rows, $map) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['Name', 'Slug', 'Proprietary', 'Health', 'License', 'Repo URL', 'Page URL']);
            foreach ($rows as $row) {
                fputcsv($out, $map($row));
            }
            fclose($out);
        }, $filename, $headers);
    }
}
