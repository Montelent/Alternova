<?php

namespace App\Http\Controllers;

use App\Support\ApiSettings;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ApiDocsController extends Controller
{
    public function __invoke(): View|Response
    {
        $enabled = ApiSettings::isGloballyEnabled();
        $base = rtrim((string) config('app.url'), '/');

        return view('api-docs', [
            'apiEnabled' => $enabled,
            'requireKey' => ApiSettings::requireKey(),
            'rateAnon' => ApiSettings::rateLimitAnonymous(),
            'rateKey' => ApiSettings::rateLimitAuthenticated(),
            'baseUrl' => $base,
            'endpoints' => $this->endpoints($base),
        ]);
    }

    /**
     * @return list<array{method: string, path: string, auth: string, summary: string, params: list<string>}>
     */
    protected function endpoints(string $base): array
    {
        return [
            [
                'method' => 'GET',
                'path' => $base.'/api/alternatives',
                'auth' => 'Optional key (required if admin enabled “Require API key”)',
                'summary' => 'List published open-source alternatives (paginated).',
                'params' => [
                    'q — search name/description/language',
                    'license — filter by license_type',
                    'category — tag/category slug',
                    'sort — health | stars | votes | name | newest',
                    'per_page — 1–50 (default 20)',
                    'page — page number',
                ],
            ],
            [
                'method' => 'GET',
                'path' => $base.'/api/alternatives/{slug}',
                'auth' => 'Optional key',
                'summary' => 'Single alternative by slug, including metrics and linked proprietary tools.',
                'params' => ['slug — URL slug of the alternative'],
            ],
            [
                'method' => 'GET',
                'path' => $base.'/api/alternatives/{slug}/pros-cons',
                'auth' => 'Optional key',
                'summary' => 'List pros and cons for an alternative.',
                'params' => ['slug — alternative slug'],
            ],
            [
                'method' => 'POST',
                'path' => $base.'/api/alternatives/{slug}/pros-cons',
                'auth' => 'API key recommended; stricter throttle',
                'summary' => 'Submit a pro or con (subject to moderation rules on the site).',
                'params' => ['JSON body: type (pro|con), body (text)'],
            ],
            [
                'method' => 'GET',
                'path' => $base.'/api/suggest',
                'auth' => 'Optional key',
                'summary' => 'Typeahead suggestions for search UIs.',
                'params' => ['q — partial query string'],
            ],
        ];
    }
}
