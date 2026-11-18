<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Widgets\DonorsByBloodGroupChart;
use App\Filament\Widgets\PlatformStats;
use App\Filament\Widgets\RegistrationsChart;
use App\Filament\Widgets\TopProfileViewers;
use App\Http\Middleware\UseEnglishLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * BloodLink administration panel at /admin.
 * Access is restricted by User::canAccessPanel() (role = admin AND status = active).
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName('BloodLink Admin')
            ->favicon(asset('favicon.svg'))
            ->colors([
                'primary' => Color::hex('#a51531'),
                'gray' => Color::Slate,
            ])
            ->font('Plus Jakarta Sans')
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->navigationGroups([
                NavigationGroup::make('People'),
                NavigationGroup::make('Requests'),
                NavigationGroup::make('Trust & Safety'),
                NavigationGroup::make('Locations')->collapsed(),
            ])
            ->navigationItems([
                NavigationItem::make('View public site')->url('/', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedGlobeAlt)->sort(99),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->widgets([
                AccountWidget::class,
                PlatformStats::class,
                RegistrationsChart::class,
                DonorsByBloodGroupChart::class,
                TopProfileViewers::class,
            ])
            ->middleware([
                UseEnglishLocale::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
