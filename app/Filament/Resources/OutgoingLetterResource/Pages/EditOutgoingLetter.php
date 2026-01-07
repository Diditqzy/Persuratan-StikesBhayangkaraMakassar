<?php

namespace App\Filament\Resources\OutgoingLetterResource\Pages;

use Filament\Actions;
use App\Models\Signer;
use App\Models\OutgoingLetter;
use App\Models\OutgoingDisposition;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\OutgoingLetterResource;

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
                ->label('Ajukan ke Pimpinan')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn (OutgoingLetter $record) =>
                Auth::user()->role === 'admin' && 
                in_array($record->status, ['draft', 'revision_needed']))
                ->action(function (OutgoingLetter $record) {
                    
                    // --- VALIDASI TAMBAHAN ---
                    // Jika bukan SKAK (ID 1) dan File Masih Kosong -> TOLAK AKSINYA
                    if ($record->type_id != 1 && empty($record->final_file_path)) {
                        Notification::make()
                            ->title('Gagal Mengajukan!')
                            ->body('Anda wajib mengupload file surat di bagian "File Surat" sebelum mengajukan ke Pimpinan.')
                            ->danger()
                            ->persistent()
                            ->send();
                        
                        // Hentikan proses
                        $this->halt(); 
                        return;
                    }

                    $record->update(['status' => 'pending_approval']);
                    
                    Notification::make()->title('Surat Diajukan ke Pimpinan')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending_approval')
                ->action(function (OutgoingLetter $record) {
                    
                    // --- LOGIKA FIX SIGNER ID ---
                    $pimpinan = Signer::whereHas('user', function ($query) {
                        $query->where('role', 'pimpinan');
                    })->first();

                    if (!$pimpinan) {
                        Notification::make()
                            ->title('GAGAL: Data Penanda Tangan Pimpinan Tidak Ditemukan')
                            ->danger()->send();
                        return;
                    }

                    // PAKSA UPDATE SIGNER ID
                    $record->signer_id = $pimpinan->id; 
                    $record->status = 'approved';
                    $record->approved_at = now();
                    $record->save();

                    Notification::make()->title('Surat Berhasil Disetujui')->success()->send();
                    
                    // Redirect balik ke index biar refresh
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn ($record) => 
                    Auth::user()->role === 'pimpinan' && 
                    !in_array($record->status, ['approved', 'rejected'])
                ),

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