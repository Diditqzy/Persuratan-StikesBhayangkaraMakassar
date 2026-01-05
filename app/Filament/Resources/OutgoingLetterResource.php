<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutgoingLetterResource\Pages;
use App\Models\OutgoingLetter;
use App\Models\OutgoingDisposition;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class OutgoingLetterResource extends Resource
{
    protected static ?string $model = OutgoingLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Surat Keluar';
    protected static ?string $modelLabel = 'Surat Keluar';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['type', 'user', 'signer'])
            ->withoutGlobalScopes();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECTION 0: ALERT INFO ---
                Forms\Components\Section::make('Status Pengembalian')
                    ->schema([
                        Forms\Components\Placeholder::make('revisi_note')
                            ->label('Catatan Revisi Pimpinan:')
                            ->content(fn ($record) => $record?->dispositions()->latest()->first()?->instruction)
                            ->extraAttributes(['class' => 'text-warning-600 font-bold text-lg'])
                            ->visible(fn ($record) => $record?->status === 'revision_needed'),
                        
                        Forms\Components\Placeholder::make('reject_note_view')
                            ->label('Alasan Penolakan:')
                            ->content(fn ($record) => $record->rejection_note)
                            ->extraAttributes(['class' => 'text-danger-600 font-bold text-lg'])
                            ->visible(fn ($record) => $record?->status === 'rejected'),
                    ])
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('danger') 
                    ->visible(fn ($record) => in_array($record?->status, ['revision_needed', 'rejected']))
                    ->columns(1),

                // --- SECTION 1: DATA UTAMA ---
                Forms\Components\Section::make('Informasi Surat')
                    ->description('Isi detail surat yang akan diajukan.')
                    ->schema([
                        Forms\Components\Select::make('type_id')
                            ->relationship('type', 'name')
                            ->label('Jenis Surat')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('signer_id')
                            ->label('Penanda Tangan')
                            ->options(function () {
                                return \App\Models\Signer::with('user')->get()->mapWithKeys(fn ($s) => [$s->id => $s->user->name . ' - ' . $s->position]);
                            })
                            ->default(fn () => \App\Models\Signer::where('is_active', true)->first()->id ?? null)
                            ->required()
                            ->dehydrated()
                            ->visible(fn ($record) => $record && in_array($record->status, ['approved', 'completed']))
                            ->disabled(), 

                        Forms\Components\TextInput::make('subject')
                            ->label('Perihal / Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('recipient')
                            ->label('Tujuan Surat')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('letter_date')
                            ->label('Tanggal Surat')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\TextInput::make('letter_number')
                            ->label('Nomor Surat')
                            ->placeholder('Otomatis')
                            ->disabled(fn ($record) => !$record || in_array($record->status, ['submitted', 'draft', 'revision_needed', 'pending_approval']) || $record->status === 'completed')
                            ->dehydrated() 
                            ->maxLength(255),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Placeholder::make('separator_dynamic')
                                    ->label('Isian Formulir Tambahan')
                                    ->content('Data di bawah ini diisi oleh pemohon sesuai jenis surat.')
                                    ->extraAttributes(['class' => 'font-bold text-primary-600 border-b pb-1 mt-4']),

                                Forms\Components\KeyValue::make('additional_data_view')
                                    ->label('Jawaban Formulir')
                                    ->statePath('additional_data') 
                                    ->formatStateUsing(function ($state) {
                                        if (empty($state)) return [];
                                        return collect($state)
                                            ->reject(fn($val) => is_array($val) && isset($val['path']))
                                            ->toArray();
                                    })
                                    ->disabled() 
                                    ->dehydrated(false), 

                                Forms\Components\Placeholder::make('additional_files_view')
                                    ->label('Lampiran Pendukung')
                                    ->content(function ($record) {
                                        if (!$record || empty($record->additional_data)) return '-';
                                        
                                        $files = collect($record->additional_data)
                                            ->filter(fn($val) => is_array($val) && isset($val['path']));

                                        if ($files->isEmpty()) return 'Tidak ada lampiran tambahan.';

                                        $html = '<ul class="list-disc pl-4 space-y-1">';
                                        foreach ($files as $label => $data) {
                                            $url = \Illuminate\Support\Facades\Storage::url($data['path']);
                                            $name = $data['original_name'] ?? 'File';
                                            $html .= "<li><span class='font-medium'>{$label}:</span> <a href='{$url}' target='_blank' class='text-primary-600 hover:underline hover:text-primary-500'>Download ({$name})</a></li>";
                                        }
                                        $html .= '</ul>';

                                        return new \Illuminate\Support\HtmlString($html);
                                    })
                                    ->visible(fn ($record) => 
                                        $record && 
                                        collect($record->additional_data)->contains(fn($val) => is_array($val) && isset($val['path']))
                                    ),
                            ])
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record && !empty($record->additional_data)),
                    ])
                    ->columns(2)
                    ->disabled(fn ($record) => $record?->status === 'completed'),

                // --- SECTION 2: FILE SURAT ---
                Forms\Components\Section::make('File Surat')
                    ->description('Upload file surat PDF di sini (baik Draft maupun Final).')
                    ->schema([
                        Forms\Components\FileUpload::make('final_file_path') 
                            ->label('Dokumen Surat (PDF)')
                            ->disk('public') 
                            ->directory('surat-keluar')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) 
                            ->downloadable() 
                            ->openable()
                            ->required(fn (Forms\Get $get, $record) => 
                                $get('type_id') != 1 && 
                                $record && $record->status !== 'submitted'
                            ) 
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type_id') != 1)
                    ->disabled(fn ($record) => $record?->status === 'completed'),


                // --- SECTION 3: STATUS ---
                Forms\Components\Section::make('Status & Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Surat')
                            ->options([
                                'submitted' => 'Pengajuan Baru',
                                'draft' => 'Draft (Konsep)',
                                'pending_approval' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'completed' => 'Final / Selesai',
                            ])
                            ->default('draft')
                            ->required()
                            ->disabled(),
                            
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pemohon')
                            ->default(Auth::id())
                            ->disabled() 
                            ->dehydrated(),
                    ])
                    ->columns(1)
                    ->disabled(fn ($record) => $record?->status === 'completed'),

                // --- SECTION 4: LAMPIRAN ---
                Forms\Components\Section::make('Lampiran Dokumen')
                    ->headerActions([
                        Forms\Components\Actions\Action::make('lihat_lampiran')
                            ->label('Lihat Lampiran')
                            ->icon('heroicon-o-eye')
                            ->color('info')
                            ->visible(fn ($record) => $record && $record->attachments->count() > 0)
                            ->modalHeading('Lampiran User')
                            ->modalSubmitAction(false) 
                            ->modalCancelAction(fn ($action) => $action->label('Tutup'))
                            ->form([
                                Forms\Components\Placeholder::make('list_lampiran')
                                    ->label('')
                                    ->content(function ($record) {
                                        $html = '<ul class="list-disc pl-4 space-y-2">';
                                        foreach ($record->attachments as $attachment) {
                                            $url = \Illuminate\Support\Facades\Storage::url($attachment->file_path);
                                            $html .= "<li><a href='{$url}' target='_blank' class='text-primary-600 hover:underline font-bold'>{$attachment->filename}</a></li>";
                                        }
                                        $html .= '</ul>';
                                        return new \Illuminate\Support\HtmlString($html);
                                    }),
                            ]),
                    ])
                    ->schema([
                        Repeater::make('attachments')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('filename')->label('Nama File')->required(),
                                FileUpload::make('file_path')
                                    ->label('File Lampiran')
                                    ->disk('public')
                                    ->directory('lampiran-surat-keluar')
                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword'])
                                    ->openable()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Lampiran'),
                    ])
                    ->disabled(fn ($record) => $record?->status === 'completed'),
            ]); 
    } 

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('letter_date')->label('Tanggal')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('type.name')->label('Jenis')->sortable(),
                Tables\Columns\TextColumn::make('subject')->label('Perihal')->limit(30)->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon')->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'draft' => 'gray',
                        'pending_approval' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revision_needed' => 'danger',
                        'completed' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'Pengajuan Baru',
                        'draft' => 'Draft',
                        'pending_approval' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'revision_needed' => 'Perlu Revisi',
                        'completed' => 'Final',
                        default => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // EDIT & DELETE (Hanya Admin)
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => 
                        Auth::user()->role === 'admin' && 
                        $record->status !== 'completed'
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => 
                        Auth::user()->role === 'admin' && 
                        $record->status !== 'completed'
                    ),

                // GRUP TOMBOL PROSES
                Tables\Actions\ActionGroup::make([
                    
                    // 1. TERIMA / VERIFIKASI (Admin Only)
                    Tables\Actions\Action::make('verify')
                        ->label('Terima & Proses')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn ($record) => 
                            Auth::user()->role === 'admin' && 
                            $record->status === 'submitted'
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Terima Pengajuan?')
                        ->modalDescription('Status akan berubah menjadi Draft. Admin dapat mulai membuatkan surat.')
                        ->action(fn (OutgoingLetter $record) => $record->update(['status' => 'draft'])),

                    // 2. TOLAK PENGAJUAN (Admin Only)
                    Tables\Actions\Action::make('reject_initial')
                        ->label('Tolak Pengajuan')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->visible(fn ($record) => 
                            Auth::user()->role === 'admin' && 
                            $record->status === 'submitted'
                        )
                        ->form([
                            Forms\Components\Textarea::make('rejection_note')
                                ->label('Alasan Penolakan')
                                ->placeholder('Contoh: Data kurang lengkap, salah tujuan surat.')
                                ->required(),
                        ])
                        ->action(function (OutgoingLetter $record, array $data) {
                            $record->update([
                                'status' => 'rejected',
                                'rejection_note' => $data['rejection_note'],
                                'rejected_at' => now(),
                                'rejected_by' => Auth::id(),
                            ]);
                            Notification::make()->warning()->title('Pengajuan Ditolak')->send();
                        }),

                    // 3. PRINT (Admin/Pimpinan jika Completed)
                    Tables\Actions\Action::make('print')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn (OutgoingLetter $record) => route('outgoing.print', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'completed'),

                    // 4. DOWNLOAD QR (Admin Only - Buat ditempel di Word)
                    Tables\Actions\Action::make('download_qr')
                        ->label('Ambil QR Code')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->url(fn (OutgoingLetter $record) => route('outgoing-letters.download-qr', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (OutgoingLetter $record) => 
                            Auth::user()->role === 'admin' && // Cuma Admin
                            in_array($record->status, ['approved', 'completed'])
                        ),

                    // 5. FINALISASI (Admin Only)
                    Tables\Actions\Action::make('finalize')
                        ->label('Finalisasi Surat')
                        ->icon('heroicon-o-lock-closed')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->visible(fn (OutgoingLetter $record) => 
                            Auth::user()->role === 'admin' && 
                            $record->status === 'approved'
                        )
                        ->action(function (OutgoingLetter $record) {
                            if (empty($record->letter_number)) {
                                Notification::make()->danger()->title('Gagal: Nomor Surat Kosong!')->send();
                                return;
                            }
                            if ($record->type_id != 1 && empty($record->final_file_path)) {
                                Notification::make()->danger()->title('Gagal: File Surat Manual Belum Diupload!')->send();
                                return;
                            }
                            $updateData = ['status' => 'completed'];
                            if (empty($record->signature_code)) {
                                $updateData['signature_code'] = (string) \Illuminate\Support\Str::uuid();
                            }
                            $record->update($updateData);
                            Notification::make()->success()->title('Surat Final & Terkunci')->send();
                        }),

                    // 6. AJUKAN KE PIMPINAN (Admin Only)
                    Tables\Actions\Action::make('submit')
                        ->label('Ajukan Verifikasi')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('blue')
                        ->requiresConfirmation()
                        ->visible(fn (OutgoingLetter $record) => 
                            Auth::user()->role === 'admin' && 
                            in_array($record->status, ['draft', 'revision_needed'])
                        )
                        ->action(fn (OutgoingLetter $record) => $record->update(['status' => 'pending_approval'])),

                    // 7. SETUJUI (PIMPINAN ONLY - HARAM BUAT ADMIN)
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui & TTD')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (OutgoingLetter $record) => 
                            Auth::user()->role === 'pimpinan' && // CUMA PIMPINAN
                            $record->status === 'pending_approval'
                        )
                        ->action(function (OutgoingLetter $record) {
                            $signatureCode = (string) \Illuminate\Support\Str::uuid();
                            $record->update([
                                'status' => 'approved',
                                'approved_at' => now(),
                                'signature_code' => $signatureCode,
                            ]);
                            Notification::make()->success()->title('Surat Disetujui')->send();
                        }),

                    // 8. MINTA REVISI (PIMPINAN ONLY)
                    Tables\Actions\Action::make('request_revision')
                        ->label('Minta Revisi')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->visible(fn (OutgoingLetter $record) => 
                            Auth::user()->role === 'pimpinan' && // CUMA PIMPINAN
                            $record->status === 'pending_approval'
                        )
                        ->form([
                            Forms\Components\Textarea::make('instruction')->label('Catatan Revisi')->required(),
                        ])
                        ->action(function (OutgoingLetter $record, array $data) {
                            OutgoingDisposition::create([
                                'outgoing_letter_id' => $record->id,
                                'user_id' => Auth::id(),
                                'instruction' => $data['instruction'],
                            ]);
                            $record->update(['status' => 'revision_needed']);
                        }),

                ])
                ->label('Proses')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('info')
                ->tooltip('Menu Aksi'),
            ]); 
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutgoingLetters::route('/'),
            'create' => Pages\CreateOutgoingLetter::route('/create'),
            'edit' => Pages\EditOutgoingLetter::route('/{record}/edit'),
        ];
    }
}