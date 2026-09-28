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

        $score = $alt ? number_format((float) $alt->overall_health_score, 0) : '—';
        $label = $alt ? $alt->name : 'Unknown';
        $color = ! $alt ? '#94a3b8' : ((float) $alt->overall_health_score >= 70 ? '#059669' : ((float) $alt->overall_health_score >= 40 ? '#d97706' : '#dc2626'));

        $labelEsc = htmlspecialchars(mb_substr($label, 0, 28), ENT_QUOTES, 'UTF-8');
        $w1 = 90;
        $w2 = 44;
        $total = $w1 + $w2;

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$total}" height="20" role="img" aria-label="Alternova health: {$score}">
  <title>Alternova health: {$score}</title>
  <linearGradient id="s" x2="0" y2="100%">
    <stop offset="0" stop-color="#bbb" stop-opacity=".1"/>
    <stop offset="1" stop-opacity=".1"/>
  </linearGradient>
  <clipPath id="r"><rect width="{$total}" height="20" rx="3" fill="#fff"/></clipPath>
  <g clip-path="url(#r)">
    <rect width="{$w1}" height="20" fill="#334155"/>
    <rect x="{$w1}" width="{$w2}" height="20" fill="{$color}"/>
    <rect width="{$total}" height="20" fill="url(#s)"/>
  </g>
  <g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" text-rendering="geometricPrecision" font-size="11">
    <text x=".{$w1}" y="14">health</text>
    <text x="{$w1}" y="14" fill="#010101" fill-opacity=".3">health</text>
    <text x="