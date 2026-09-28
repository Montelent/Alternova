<?php

namespace App\Http\Controllers;

use App\Models\OpenSourceAlternative;
use Illuminate\Http\Response;

class BadgeController extends Controller
{
    public function health(string $slug): Response
    {
        $alt = OpenSourceAlternative::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        $score = $alt ? number_format((float) $alt->overall_health_score, 0) : '-';
        $color = ! $alt
            ? '#94a3b8'
            : ((float) $alt->overall_health_score >= 70
                ? '#059669'
                : ((float) $alt->overall_health_score >= 40 ? '#d97706' : '#dc2626'));

        $left = 72;
        $right = 40;
        $total = $left + $right;
        $leftMid = (int) ($left / 2);
        $rightMid = $left + (int) ($right / 2);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$total.'" height="20" role="img">'
            .'<title>Alternova health '.$score.'</title>'
            .'<linearGradient id="s" x2="0" y2="100%">'
            .'<stop offset="0" stop-color="#bbb" stop-opacity=".1"/>'
            .'<stop offset="1" stop-opacity=".1"/>'
            .'</linearGradient>'
            .'<clipPath id="r"><rect width="'.$total.'" height="20" rx="3" fill="#fff"/></clipPath>'
            .'<g clip-path="url(#r)">'
            .'<rect width="'.$left.'" height="20" fill="#334155"/>'
            .'<rect x="'.$left.'" width="'.$right.'" height="20" fill="'.$color.'"/>'
            .'<rect width="'.$total.'" height="20" fill="url(#s)"/>'
            .'</g>'
            .'<g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,sans-serif" font-size="11">'
            .'<text x="'.$leftMid.'" y="14">health</text>'
            .'<text x="'.$rightMid.'" y="14">'.$score.'</text>'
            .'</g>'
            .'</svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
