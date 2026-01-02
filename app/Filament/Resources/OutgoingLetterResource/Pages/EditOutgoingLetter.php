<?php

namespace App\Filament\Resources\OutgoingLetterResource\Pages;

use App\Filament\Resources\OutgoingLetterResource;
use App\Models\OutgoingDisposition;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;

class EditOutgoingLetter extends EditRecord
{
    protected static string $resource = OutgoingLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Tombol Delete Bawaan
            Actions\DeleteAction::make(),

            // 2. TOMBOL AJUKAN (Draft -> Pending)
            Actions\Action::make('submit')
                ->label('Ajukan Verifikasi')
                ->icon('heroicon-o-paper-airplane')
                ->color('blue')
                ->requiresConfirmation()
                ->modalHeading('Ajukan Surat?')
                ->modalDescription('Surat akan dikirim ke pimpinan untuk diperiksa.')
                ->visible(fn () => in_array($this->record->status, ['draft', 'revision_needed']))
                ->action(function () {
                    $this->record->update(['status' => 'pending_approval']);
                    Notification::make()->success()->title('Surat Berhasil Diajukan')->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 3. TOMBOL APPROVE (Pending -> Approved)
            Actions\Action::make('approve')
                ->label('Setujui Surat')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending_approval')
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]);
                    Notification::make()->success()->title('Surat Disetujui')->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 4. TOMBOL MINTA REVISI (Pending -> Revision Needed)
            Actions\Action::make('request_revision')
                ->label('Minta Revisi')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'pending_approval')
                ->form([
                    Textarea::make('instruction')
                        ->label('Catatan Revisi')
                        ->placeholder('Jelaskan apa yang harus diperbaiki...')
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Simpan Catatan
                    OutgoingDisposition::create([
                        'outgoing_letter_id' => $this->record->id,
                        'user_id' => Auth::id(),
                        'instruction' => $data['instruction'],
                    ]);

                    // Ubah Status
                    $this->record->update(['status' => 'revision_needed']);

                    Notification::make()->warning()->title('Surat Dikembalikan untuk Revisi')->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            
            // 5. TOMBOL PRINT (Muncul kalau Approved)
            Actions\Action::make('print')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('outgoing.print', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status === 'approved'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
