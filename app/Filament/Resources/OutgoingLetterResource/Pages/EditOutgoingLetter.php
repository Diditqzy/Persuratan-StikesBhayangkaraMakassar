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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            // 1. TOMBOL TERIMA (Submitted -> Draft)
            Actions\Action::make('verify')
                ->label('Terima Pengajuan')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => $this->record->status === 'submitted')
                ->requiresConfirmation()
                ->modalHeading('Terima Pengajuan?')
                ->modalDescription('Status akan berubah menjadi Draft. Anda dapat mulai memproses surat ini.')
                ->action(function () {
                    $this->record->update(['status' => 'draft']);
                    Notification::make()->success()->title('Pengajuan Diterima')->send();
                    // Refresh halaman agar form terbuka (tidak disabled)
                    $this->redirect(route('filament.admin.resources.outgoing-letters.edit', $this->record));
                }),

            // 2. TOMBOL TOLAK (Submitted -> Rejected)
            Actions\Action::make('reject_initial')
                ->label('Tolak Pengajuan')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'submitted')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_note')
                        ->label('Alasan Penolakan')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'rejection_note' => $data['rejection_note'],
                        'rejected_at' => now(),
                        'rejected_by' => Auth::id(),
                    ]);
                    Notification::make()->warning()->title('Pengajuan Ditolak')->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // --- TOMBOL LAINNYA (YANG SUDAH ADA SEBELUMNYA) ---
            
            Actions\Action::make('download_qr')
                ->label('Ambil TTD Digital')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->url(fn () => route('outgoing-letters.download-qr', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => in_array($this->record->status, ['approved', 'completed'])),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status !== 'completed'),

            Actions\Action::make('submit')
                ->label('Ajukan Verifikasi')
                ->icon('heroicon-o-paper-airplane')
                ->color('blue')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['draft', 'revision_needed']))
                ->action(function () {
                    $this->record->update(['status' => 'pending_approval']);
                    Notification::make()->success()->title('Surat Berhasil Diajukan')->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

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
                        'signature_code' => (string) \Illuminate\Support\Str::uuid(), 
                    ]);
                    Notification::make()->success()->title('Surat Disetujui')->send();
                }),

            Actions\Action::make('request_revision')
                ->label('Minta Revisi')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'pending_approval')
                ->form([
                    Textarea::make('instruction')->label('Catatan Revisi')->required(),
                ])
                ->action(function (array $data) {
                    OutgoingDisposition::create([
                        'outgoing_letter_id' => $this->record->id,
                        'user_id' => Auth::id(),
                        'instruction' => $data['instruction'],
                    ]);
                    $this->record->update(['status' => 'revision_needed']);
                    Notification::make()->warning()->title('Dikembalikan untuk Revisi')->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\Action::make('finalize')
                ->label('Finalisasi Surat')
                ->icon('heroicon-o-lock-closed')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'approved')
                ->action(function () {
                    if (empty($this->record->letter_number)) {
                        Notification::make()->danger()->title('Nomor Surat Kosong!')->send();
                        return;
                    }
                    $this->record->update(['status' => 'completed']);
                    Notification::make()->success()->title('Surat Final')->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            
            Actions\Action::make('print')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('outgoing.print', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->status === 'completed'),
        ];
    }

    protected function getFormActions(): array
    {
        // Jika status sudah Completed (Final), hilangkan tombol Save & Cancel
        if ($this->record->status === 'completed') {
            return [];
        }

        return parent::getFormActions();
    }
}