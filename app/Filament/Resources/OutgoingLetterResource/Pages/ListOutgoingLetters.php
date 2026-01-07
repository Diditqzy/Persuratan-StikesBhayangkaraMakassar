<?php

namespace App\Filament\Resources\OutgoingLetterResource\Pages;

use App\Filament\Resources\OutgoingLetterResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListOutgoingLetters extends ListRecords
{
    protected static string $resource = OutgoingLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::user()->role === 'admin'),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();

        // --- TABS KHUSUS PIMPINAN ---
        if ($user->role === 'pimpinan') {
            return [
                'need_approval' => Tab::make('Butuh Persetujuan')
                    ->icon('heroicon-m-clock')
                    ->badgeColor('warning')
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending_approval'))
                    ->badge(fn () => $this->getModel()::where('status', 'pending_approval')->count()),

                'history' => Tab::make('Riwayat')
                    ->icon('heroicon-m-archive-box')
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['approved', 'rejected', 'completed'])),
            ];
        }

        // --- TABS KHUSUS ADMIN ---
        return [
            'pengajuan' => Tab::make('Pengajuan Baru')
                ->icon('heroicon-m-inbox-arrow-down')
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'submitted'))
                ->badge(fn () => $this->getModel()::where('status', 'submitted')->count()),

            'proses' => Tab::make('Dalam Proses')
                ->icon('heroicon-m-arrow-path')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    'draft', 
                    'pending_approval', 
                    'revision_needed'
                ])),

            'selesai' => Tab::make('Selesai / Arsip')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    'approved', 
                    'completed', 
                    'rejected'
                ])),

            'all' => Tab::make('Semua Data')
                ->icon('heroicon-m-list-bullet'),
        ];
    }
}