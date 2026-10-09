<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureSessionIsConfigured;
use App\Http\Middleware\PreventDemoWrites;
use App\Http\Middleware\SecurityHeaders;
use App\Support\WhiteLabelSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $primary = '#4f46e5';
        try {
            $primary = WhiteLabelSettings::primary();
        } catch (\Throwable) {
        }

        $adminName = config('app.name', 'Alternova').' Admin';
        $logo = null;
        $favicon = null;
        try {
            $adminName = WhiteLabelSettings::adminName();
            $logo = WhiteLabelSettings::logoUrl();
            $favicon = WhiteLabelSettings::faviconUrl();
        } catch (\Throwable) {
        }

        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName($adminName)
            ->colors([
                'primary' => Color::hex($primary),
                'danger' => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'info' => Color::Sky,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('17.5rem')
            ->collapsedSidebarWidth('4.5rem')
            ->maxContentWidth(MaxWidth::Full)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                'Content',
                'Open Source Finder',
                'Engagement',
                'System',
                'Monetization',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\NeedsAttentionWidget::class,
                \App\Filament\Widgets\SystemStatusWidget::class,
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\EngagementStatsWidget::class,
                Widgets\AccountWidget::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn () => view('filament.hooks.admin-head'),
            )
            ->middleware([
                // MUST run before StartSession — Filament does not use the web group stack alone
                EnsureSessionIsConfigured::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SecurityHeaders::class,
                PreventDemoWrites::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        if ($logo) {
            $panel = $panel->brandLogo($logo)->brandLogoHeight('2rem');
        }

        if ($favicon) {
            $panel = $panel->favicon($favicon);
        }

        if (\App\Support\DemoMode::enabled()) {
            $panel = $panel->renderHook(
                \Filament\View\PanelsRenderHook::BODY_START,
                fn (): string => view('filament.hooks.demo-banner')->render()
            );
        }

        return $panel;
    }
}
