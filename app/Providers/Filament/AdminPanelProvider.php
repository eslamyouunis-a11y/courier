<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\HtmlString;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()

            /* ===============================
               LOGO (BIGGER + CLEAN)
            =============================== */
            ->brandLogo(fn () => new HtmlString('
                <div class="meta-logo-wrapper">
                    <img src="/logo-full.svg" class="logo-full" alt="Courier">
                    <img src="/logo-icon.svg" class="logo-icon" alt="Courier">
                </div>
            '))
            ->brandLogoHeight('4rem') // 👈 زودنا المساحة نفسها
            ->brandName(null)

            /* ❌ بدون دارك مود */
            ->darkMode(false)

            /* 🎨 ألوان Meta */
            ->colors([
                "primary" => [
                    50  => "#eef1f3",
                    100 => "#d9dfe3",
                    200 => "#b3bdc6",
                    300 => "#8d9ca9",
                    400 => "#5b7283",
                    500 => "#283943",
                    600 => "#24333c",
                    700 => "#1f2c34",
                    800 => "#19242a",
                    900 => "#121a20",
                ],
            ])

            ->font('Changa', provider: GoogleFontProvider::class)

            /* ===============================
               META STYLE + UI
            =============================== */
            ->renderHook(
                'panels::body.end',
                fn () => new HtmlString('
<style>

/* ===== Global Meta Background ===== */
body, .fi-main {
    background: linear-gradient(
        135deg,
        #f0f2f5 0%,
        #ffffff 45%,
        #eef1f6 100%
    );
}

/* ===== Sidebar Width ===== */
.fi-layout {
    --sidebar-width: 240px;
    --sidebar-collapsed-width: 72px;
}

/* ===== Sidebar Container ===== */
.fi-sidebar {
    background: #ffffff !important;
    border-left: 1px solid #e4e6eb;
}

/* ===== Logo Wrapper ===== */
.meta-logo-wrapper {
    min-height: 72px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Full Logo */
.logo-full {
    height: 56px;   /* 👈 أكبر بوضوح */
    width: auto;
    display: block;
}

/* Icon Logo */
.logo-icon {
    height: 42px;
    width: auto;
    display: none;
}

/* Collapsed logic (Filament) */
.fi-layout[data-sidebar-collapsed="true"] .logo-full {
    display: none !important;
}
.fi-layout[data-sidebar-collapsed="true"] .logo-icon {
    display: block !important;
}

/* ===== Sidebar Items ===== */
.fi-sidebar-item-label,
.fi-sidebar-group-label span,
.fi-sidebar-item > a span {
    font-weight: 900 !important;
    color: #050505;
}

.fi-sidebar-item > a {
    margin-inline: 8px;
    border-radius: 12px;
    transition: background 0.2s ease;
}

/* Active */
.fi-sidebar-item-active > a {
    background: #283943 !important;
}
.fi-sidebar-item-active > a span,
.fi-sidebar-item-active > a svg {
    color: #ffffff !important;
}

/* Hover */
.fi-sidebar-item:not(.fi-sidebar-item-active) > a:hover {
    background: #f2f3f5;
}

/* ===== LOGIN BUTTON FIX ===== */
.fi-auth-page .fi-btn-primary,
.fi-auth-page .fi-btn-primary span,
.fi-auth-page .fi-btn-primary svg {
    color: #ffffff !important;
    opacity: 1 !important;
}
/* ===== GLOBAL CARDS STYLE ===== */
.fi-section {
    border-radius: 16px !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.04) !important;
    border: 1px solid #eef2f7 !important;
}

/* ===== HEADER SECTION ===== */
.fi-section:first-child {
    background: linear-gradient(135deg,#f8fafc,#ffffff);
    border: 1px solid #e5e7eb;
}

/* ===== SECTION TITLES ===== */
.fi-section-header {
    font-weight: 800 !important;
    font-size: 15px;
    color: #1f2937;
}

/* ===== BADGES ===== */
.fi-badge {
    border-radius: 999px !important;
    padding: 6px 14px !important;
    font-weight: 700;
}

/* ===== MONEY BOX ===== */
.bg-success-50 {
    background: linear-gradient(135deg,#ecfdf5,#f0fdf4) !important;
}

/* ===== BARCODE ===== */
.barcode-container svg {
    max-width: 100%;
    height: auto;
}

/* ===== TEXT ===== */
.fi-infolist-text-entry {
    font-size: 14px;
    color: #374151;
}
</style>
                ')
            )

            ->navigationGroups([
                NavigationGroup::make()->label("الرئيسية")->collapsible(false),
                NavigationGroup::make()->label("الإعدادات")->collapsed(),
            ])

            ->discoverResources(in: app_path("Filament/Resources"), for: "App\\Filament\\Resources")
            ->discoverPages(in: app_path("Filament/Pages"), for: "App\\Filament\\Pages")
            ->pages([Pages\Dashboard::class])
            ->discoverWidgets(in: app_path("Filament/Widgets"), for: "App\\Filament\\Widgets")
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
