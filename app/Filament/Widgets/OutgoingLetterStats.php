<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OutgoingLetterResource;
use App\Models\OutgoingLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OutgoingLetterStats extends BaseWidget
{
    // Mengatur agar widget ini muncul di urutan paling atas (di atas grafik)
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        return [
            // 1. TOMBOL PENGAJUAN BARU (Pending)
            Stat::make('Pengajuan Baru', OutgoingLetter::where('status', 'pending')->count())
                ->description('Surat menunggu verifikasi')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning') // Kuning/Oranye
                // Link menuju halaman index Surat Keluar
                ->url(OutgoingLetterResource::getUrl('index')),

            // 2. TOMBOL DALAM PROSES (Approved/Verified - sesuaikan status di db anda)
            Stat::make('Dalam Proses', OutgoingLetter::where('status', 'approved')->count())
                ->description('Surat disetujui/sedang diproses')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success') // Hijau
                ->url(OutgoingLetterResource::getUrl('index')),

            // 3. TOMBOL ARSIP / DITOLAK
            Stat::make('Arsip / Ditolak', OutgoingLetter::where('status', 'rejected')->count())
                ->description('Surat ditolak atau selesai')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('danger') // Merah
                ->url(OutgoingLetterResource::getUrl('index')),
        ];
    }
}