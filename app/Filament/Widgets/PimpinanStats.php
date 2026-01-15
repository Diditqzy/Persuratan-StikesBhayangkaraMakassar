<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IncomingLetterResource;
use App\Filament\Resources\OutgoingLetterResource;
use App\Models\IncomingLetter;
use App\Models\OutgoingLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PimpinanStats extends BaseWidget
{
    // Urutan 0 berarti paling atas
    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->role === 'pimpinan';
    }

    protected function getStats(): array
    {
        $incomingCount = IncomingLetter::where('status', 'waiting_disposition')->count();
        $outgoingPendingCount = OutgoingLetter::where('status', 'pending_approval')->count();
        $outgoingApprovedCount = OutgoingLetter::where('status', 'approved')->count();
        $outgoingRejectedCount = OutgoingLetter::where('status', 'rejected')->count();

        return [
            Stat::make('Surat Masuk (Disposisi)', $incomingCount)
                ->description($incomingCount > 0 ? 'Menunggu disposisi Anda' : 'Tidak ada surat masuk')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color('primary') 
                ->chart($incomingCount > 0 ? [2, 10, 5, 15] : [])
                ->url(IncomingLetterResource::getUrl('index')),

            Stat::make('Butuh Tanda Tangan', $outgoingPendingCount)
                ->description($outgoingPendingCount > 0 ? 'Klik untuk validasi surat' : 'Aman, tidak ada tanggungan')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color($outgoingPendingCount > 0 ? 'danger' : 'success') 
                ->chart($outgoingPendingCount > 0 ? [15, 5, 10, 2] : [])
                ->url(OutgoingLetterResource::getUrl('index')), 

            Stat::make('Sudah Disetujui', $outgoingApprovedCount)
                ->description('Total surat yang Anda tanda tangani')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'), 
        ];
    }
}