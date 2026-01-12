<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // Ikon di Sidebar
    protected static ?string $navigationIcon = 'heroicon-o-home';

    // Judul Halaman (Default: Dashboard)
    protected static ?string $title = 'Dashboard';

    // --- BAGIAN INI UNTUK MEMBUAT TULISAN DI BAWAH JUDUL ---
    protected ?string $subheading = 'Selamat datang, Silakan kelola surat masuk dan keluar di sini.';
}   