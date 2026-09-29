@props(['points' => '', 'trend' => 'flat', 'class' => 'w-16 h-6'])

@php
    $stroke = match ($trend) {
        'up' => '#10b981',
        'down' => '#f43f5e',
        default => '#6366f1',
    };
@endphp

@if($points)
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 100 28" preserveAspectRatio="none" aria-hidden="true">
        <polyline
            fill="none"
            stroke="{{ $stroke }}"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            points="{{ $points }}"
        />
    </svg>
@endif
