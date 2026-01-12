<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\IncomingLetter;
use App\Models\OutgoingLetter;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class LettersOverviewChart extends ChartWidget
{
    // Judul Grafik
    protected static ?string $heading = 'Statistik Surat (1 Tahun Terakhir)';
    
    // Agar grafik memenuhi lebar layar (opsional, hapus jika ingin setengah layar)
    protected static ?int $sort = 1;
    // protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 2; // Asumsi Grid total 3 kolom


    protected function getData(): array
    {
        // 1. Ambil Data Surat Masuk per Bulan
        $incomingData = Trend::model(IncomingLetter::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        // 2. Ambil Data Surat Keluar per Bulan
        $outgoingData = Trend::model(OutgoingLetter::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Surat Masuk',
                    'data' => $incomingData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#3b82f6', // Warna Biru
                    'borderColor' => '#1d4ed8',
                ],
                [
                    'label' => 'Surat Keluar',
                    'data' => $outgoingData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#f59e0b', // Warna Kuning/Amber
                    'borderColor' => '#b45309',
                ],
            ],
            'labels' => $incomingData->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Jenis Grafik: Batang
    }
}