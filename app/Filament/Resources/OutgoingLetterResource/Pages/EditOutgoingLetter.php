<?php

namespace App\Filament\Resources\OutgoingLetterResource\Pages;

use App\Filament\Resources\OutgoingLetterResource;
use App\Models\OutgoingDisposition;
use App\Models\OutgoingLetter;
use App\Models\Signer;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
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
        // Helper Variables
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isPimpinan = $user->role === 'pimpinan';

        return [    
            // TOMBOL QR CODE
            Actions\Action::make('download_qr')
                ->label('QR Code')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->url(fn (OutgoingLetter $record) => route('outgoing-letters.download-qr', $record))
                ->openUrlInNewTab()
                ->visible(fn (OutgoingLetter $record) => in_array($record->status, ['approved', 'completed'])),

            // TOMBOL CETAK PDF
            Actions\Action::make('print')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn (OutgoingLetter $record) => route('outgoing.print', $record))
                ->openUrlInNewTab()
                ->visible(fn (OutgoingLetter $record) => in_array($record->status, ['approved', 'completed'])),

            // Verifikasi (Submitted -> Draft)
            Actions\Action::make('verify')
                ->label('Terima Pengajuan')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (OutgoingLetter $record) => $isAdmin && $record->status === 'submitted')
                ->requiresConfirmation()
                ->action(function (OutgoingLetter $record) {
                    $record->update([
                        'status' => 'draft',
                        'verified_by' => Auth::id(),
                        'verified_at' => now(),
                    ]);
                    Notification::make()->title('Pengajuan Diterima')->success()->send();
                    $this->redirect(route('filament.admin.resources.outgoing-letters.edit', $record));
                }),

            // Tolak Awal (Submitted -> Rejected)
            Actions\Action::make('reject_initial')
                ->label('Tolak Pengajuan')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn (OutgoingLetter $record) => $isAdmin && $record->status === 'submitted')
                ->form([
                    Textarea::make('rejection_note')->label('Alasan Penolakan')->required(),
                ])
                ->action(function (OutgoingLetter $record, array $data) {
                    $record->update([
                        'status' => 'rejected',
                        'rejection_note' => $data['rejection_note'],
                        'rejected_at' => now(),
                        'rejected_by' => Auth::id(),
                    ]);
                    Notification::make()->title('Pengajuan Ditolak')->warning()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Submit ke Pimpinan (Draft/Revisi -> Pending)
            Actions\Action::make('submit')
                ->label('Ajukan ke Pimpinan')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (OutgoingLetter $record) => $isAdmin && in_array($record->status, ['draft', 'revision_needed']))
                ->action(function (OutgoingLetter $record) {
                    if ($record->type_id != 1 && empty($record->final_file_path)) {
                        Notification::make()->title('Gagal!')->body('Wajib upload file surat (Word/PDF) sebelum diajukan.')->danger()->send();
                        return;
                    }

                    $record->update([
                        'status' => 'pending_approval',
                        'rejected_at' => null,
                        'rejected_by' => null,
                        'rejection_note' => null,
                    ]);
                    
                    Notification::make()->title('Surat Diajukan ke Pimpinan')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Finalisasi (Approved -> Completed)
            Actions\Action::make('finalize')
                ->label('Finalisasi Surat')
                ->icon('heroicon-o-lock-closed')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn (OutgoingLetter $record) => $isAdmin && $record->status === 'approved')
                ->action(function (OutgoingLetter $record) {
                    if (empty($record->letter_number)) {
                        Notification::make()->title('Nomor Surat Wajib Diisi!')->danger()->send();
                        return;
                    }
                    $record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'completed_by' => Auth::id(),
                    ]);
                    Notification::make()->title('Surat Final & Selesai')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Approve
            Actions\Action::make('approve')
                ->label('Setujui & TTD')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (OutgoingLetter $record) => $isPimpinan && $record->status === 'pending_approval')
                ->action(function (OutgoingLetter $record) {
                    $pimpinan = Signer::whereHas('user', fn ($q) => $q->where('role', 'pimpinan'))->first();

                    if (!$pimpinan) {
                        Notification::make()->title('Data Signer Pimpinan belum diatur.')->danger()->send();
                        return;
                    }

                    $record->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => Auth::id(),
                        'signer_id' => $pimpinan->id,
                    ]);

                    Notification::make()->title('Surat Disetujui')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Minta Revisi
            Actions\Action::make('request_revision')
                ->label('Minta Revisi')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->visible(fn (OutgoingLetter $record) => $isPimpinan && $record->status === 'pending_approval')
                ->form([
                    Textarea::make('instruction')->label('Catatan Revisi')->required(),
                ])
                ->action(function (OutgoingLetter $record, array $data) {
                    OutgoingDisposition::create([
                        'outgoing_letter_id' => $record->id,
                        'user_id' => Auth::id(),
                        'instruction' => $data['instruction'],
                    ]);
                    
                    $record->update([
                        'status' => 'revision_needed',
                        'rejection_note' => $data['instruction']
                    ]);

                    Notification::make()->title('Dikembalikan untuk Revisi')->warning()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Tolak Final
            Actions\Action::make('reject_modal')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (OutgoingLetter $record) => $isPimpinan && $record->status === 'pending_approval')
                ->form([
                    Textarea::make('rejection_note')->label('Alasan Penolakan')->required(),
                ])
                ->action(function (OutgoingLetter $record, array $data) {
                    $record->update([
                        'status' => 'rejected',
                        'rejection_note' => $data['rejection_note'],
                        'rejected_at' => now(),
                        'rejected_by' => Auth::id(),
                    ]);
                    Notification::make()->title('Surat Ditolak')->danger()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Hapus (Hanya Admin & Belum Completed)
            Actions\DeleteAction::make()
                ->visible(fn (OutgoingLetter $record) => $isAdmin && $record->status !== 'completed'),
        ];
    }

    protected function getFormActions(): array
    {
        if (Auth::user()->role === 'pimpinan' || $this->record->status === 'completed') {
            return [];
        }
        return parent::getFormActions();
    }
}