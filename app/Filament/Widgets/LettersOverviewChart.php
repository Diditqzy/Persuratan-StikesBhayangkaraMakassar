<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\IncomingLetter;
use App\Models\OutgoingLetter;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class LettersOverviewChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Surat (1 Tahun Terakhir)';
    
    protected static ?int $sort = 1;
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 2; 


    protected function getData(): array
    {
        $incomingData = Trend::model(IncomingLetter::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

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
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#1d4ed8',
                ],
                [
                    'label' => 'Surat Keluar',
                    'data' => $outgoingData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#b45309',
                ],
            ],
            'labels' => $incomingData->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; 
    }
}