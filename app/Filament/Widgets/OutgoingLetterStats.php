<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OutgoingLetterResource;
use App\Models\OutgoingLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OutgoingLetterStats extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        return [
            Stat::make('Pengajuan Baru', OutgoingLetter::where('status', 'pending')->count())
                ->description('Surat menunggu verifikasi')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning') 
                ->url(OutgoingLetterResource::getUrl('index')),

            Stat::make('Dalam Proses', OutgoingLetter::where('status', 'approved')->count())
                ->description('Surat disetujui/sedang diproses')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(OutgoingLetterResource::getUrl('index')),

            Stat::make('Arsip / Ditolak', OutgoingLetter::where('status', 'rejected')->count())
                ->description('Surat ditolak atau selesai')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('danger')
                ->url(OutgoingLetterResource::getUrl('index')),
        ];
    }
    public static function canView(): bool
        {
            return Auth::user()->role === 'admin';
        }
}