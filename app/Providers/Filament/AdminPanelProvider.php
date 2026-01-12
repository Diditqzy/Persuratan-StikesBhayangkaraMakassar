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
use Filament\Support\Assets\Css;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString;
use Filament\Support\Facades\FilamentView; 
use Filament\View\PanelsRenderHook;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // ->login()
            ->colors([
                // 'primary' => Color::Indigo,
                'primary' => Color::hex('#000275'),
            
            ])
            // --- TAMBAHKAN BAGIAN INI UNTUK MENGATUR URUTAN GRUP ---
            ->navigationGroups([
                'Manajemen Surat', // Ini akan muncul paling atas
                'Pengaturan',      // Ini akan muncul di bawahnya
            ])
            
            ->brandLogo(asset('images/logo-dashboard.png'))
            ->brandLogoHeight('4rem')
            ->darkMode(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
    // --- KITA PINDAHKAN LOGIKA CSS KESINI ---
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => new HtmlString('
                <style>
                    /* SETUP CSS BIAR LEBIH KERAS (SPECIFICITY TINGGI) */
                    
                    /* 1. Header Atas & Logo Header */
                    body .fi-topbar,
                    body .fi-sidebar-header {
                        background-color: #000275 !important;
                        border-bottom: none !important;
                        height: 5.5rem !important; /* Tinggi Header */
                    }

                    /* 2. Warna Teks, Icon, Logo Putih */
                    body .fi-topbar .fi-btn, 
                    body .fi-topbar .fi-icon-btn,
                    body .fi-topbar .fi-btn-label,
                    body .fi-sidebar-header .fi-logo,
                    body .fi-sidebar-header .fi-logo span {
                        color: white !important;
                        --c-400: white !important; /* Paksa variabel warna text */
                        --c-500: white !important;
                    }

                    /* 3. Background Navigasi */
                    body .fi-topbar nav {
                        background-color: transparent !important;
                        height: 5.5rem !important; /* Tinggi Header */
                    }

                    /* 4. Hover Effect */
                    body .fi-topbar .fi-icon-btn:hover {
                        background-color: rgba(255, 255, 255, 0.2) !important;
                    }
                </style>
            '),
        );
    }
}
