<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OutgoingLetterResource;
use App\Models\OutgoingLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;


class PimpinanStats extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        return [
            // 1. KOTAK UTAMA: BUTUH PERSETUJUAN (Ini Shortcut-nya)
            Stat::make('Butuh Persetujuan', OutgoingLetter::where('status', 'pending')->count())
                ->description('Klik untuk validasi surat')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('danger') // Merah (Penting)
                ->url(OutgoingLetterResource::getUrl('index')), // Link ke halaman surat

            // 2. KOTAK INFO: SUDAH DISETUJUI
            Stat::make('Sudah Disetujui', OutgoingLetter::where('status', 'approved')->count())
                ->description('Total surat yang Anda tanda tangani')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'), // Hijau (Info saja, tidak perlu link jika tidak diminta)

            // 3. KOTAK INFO: DITOLAK
            Stat::make('Ditolak', OutgoingLetter::where('status', 'rejected')->count())
                ->description('Surat yang dikembalikan')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('gray'), // Abu-abu
        ];
    }

    public static function canView(): bool
        {
            // Pastikan di database users, kolom role isinya 'pimpinan' (huruf kecil semua)
            return Auth::user()->role === 'pimpinan';
        }
}