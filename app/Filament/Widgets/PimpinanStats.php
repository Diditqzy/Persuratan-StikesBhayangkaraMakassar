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
            Stat::make('Butuh Persetujuan', OutgoingLetter::where('status', 'pending')->count())
                ->description('Klik untuk validasi surat')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('danger') 
                ->url(OutgoingLetterResource::getUrl('index')), 

            Stat::make('Sudah Disetujui', OutgoingLetter::where('status', 'approved')->count())
                ->description('Total surat yang Anda tanda tangani')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'), 

            Stat::make('Ditolak', OutgoingLetter::where('status', 'rejected')->count())
                ->description('Surat yang dikembalikan')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('gray'), 
        ];
    }

    public static function canView(): bool
        {
            return Auth::user()->role === 'pimpinan';
        }
}