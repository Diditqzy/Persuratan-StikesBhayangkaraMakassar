<?php

namespace App\Filament\Resources\OutgoingLetterResource\Pages;

use App\Filament\Resources\OutgoingLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOutgoingLetters extends ListRecords
{
    protected static string $resource = OutgoingLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // TAB 1: PENGAJUAN BARU (Status 'submitted')
            'pengajuan' => Tab::make('Pengajuan Baru')
                ->icon('heroicon-m-inbox-arrow-down')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'submitted'))
                ->badge(fn () => $this->getModel()::where('status', 'submitted')->count())
                ->badgeColor('danger'),

            // TAB 2: DALAM PROSES (Draft, Pending, Revisi)
            'proses' => Tab::make('Dalam Proses')
                ->icon('heroicon-m-arrow-path')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    'draft', 
                    'pending_approval', 
                    'revision_needed'
                ])),

            // TAB 3: SELESAI / ARSIP (Approved, Completed, Rejected)
            'selesai' => Tab::make('Selesai / Arsip')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    'approved', 
                    'completed',
                    'rejected'
                ])),

            // TAB 4: SEMUA DATA (Backup)
            'all' => Tab::make('Semua Data')
                ->icon('heroicon-m-list-bullet'),
        ];
    }
}