<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutgoingLetterResource\Pages;
use App\Models\OutgoingLetter;
use App\Models\OutgoingDisposition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
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
                // --- SECTION 0: ALERT REVISI ---
                Forms\Components\Section::make('Disposisi Pimpinan')
                    ->schema([
                        Forms\Components\Placeholder::make('revisi_note')
                            ->label('Instruksi / Catatan:')
                            ->content(fn ($record) => $record?->dispositions()->latest()->first()?->instruction ?? '-Tidak ada catatan-')
                            ->extraAttributes(['class' => 'text-danger-600 font-bold text-lg']),
                        
                        Forms\Components\Placeholder::make('disposition_sender')
                            ->label('Dari:')
                            ->content(fn ($record) => $record?->dispositions()->latest()->first()?->sender->name ?? '-'),
                    ])
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->iconColor('danger') 
                    ->visible(fn ($record) => $record?->status === 'revision_needed')
                    ->columns(2),

                // --- SECTION 1: DATA UTAMA ---
                Forms\Components\Section::make('Informasi Surat')
                    ->description('Isi detail surat yang akan diajukan.')
                    ->schema([
                        Forms\Components\Select::make('type_id')
                            ->relationship('type', 'name')
                            ->label('Jenis Surat')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('signer_id')
                            ->label('Penanda Tangan (Pimpinan)')
                            ->options(function () {
                                return \App\Models\Signer::with('user')
                                    ->get()
                                    ->mapWithKeys(function ($signer) {
                                        return [$signer->id => $signer->user->name . ' - ' . $signer->position];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('subject')
                            ->label('Perihal / Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('recipient')
                            ->label('Tujuan Surat (Kepada Yth.)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('letter_date')
                            ->label('Tanggal Surat')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\TextInput::make('letter_number')
                            ->label('Nomor Surat')
                            ->placeholder('Otomatis diisi saat status Disetujui')
                            ->disabled(fn ($record) => 
                                !$record || 
                                in_array($record->status, ['draft', 'revision_needed', 'pending_approval']) || 
                                $record->status === 'completed'
                            )
                            ->dehydrated() 
                            ->maxLength(255),

                        Forms\Components\RichEditor::make('content_data')
                            ->label('Isi Surat')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->disabled(fn ($record) => $record?->status === 'completed'),

                // --- SECTION 2: FILE SURAT ---
                Forms\Components\Section::make('File Surat')
                    ->description('Upload file surat di sini. Awalnya upload DRAFT untuk diperiksa pimpinan. Jika sudah disetujui, download QR, tempel, lalu upload ulang file FINAL di sini.')
                    ->schema([
                        Forms\Components\FileUpload::make('final_file_path') 
                            ->label('Dokumen Surat (PDF)')
                            ->disk('public') 
                            ->directory('surat-keluar')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->downloadable() 
                            ->openable() 
                            ->required() 
                            ->columnSpanFull(),
                    ])
                    ->disabled(fn ($record) => $record?->status === 'completed'),

                // --- SECTION 3: STATUS ---
                Forms\Components\Section::make('Status & Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Surat')
                            ->options([
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

                // --- SECTION 4: LAMPIRAN PENDUKUNG ---
                Forms\Components\Section::make('Lampiran Dokumen')
                    ->description('Upload dokumen pendukung lainnya (jika ada).')
                    ->headerActions([
                        Forms\Components\Actions\Action::make('lihat_lampiran')
                            ->label('Lihat / Cetak Lampiran')
                            ->icon('heroicon-o-printer')
                            ->color('info')
                            ->visible(fn ($record) => $record && $record->attachments->count() > 0)
                            ->modalHeading('Pilih Lampiran untuk Dicetak')
                            ->modalSubmitAction(false) 
                            ->modalCancelAction(fn ($action) => $action->label('Tutup'))
                            ->form([
                                Forms\Components\Placeholder::make('list_lampiran')
                                    ->label('')
                                    ->content(function ($record) {
                                        $html = '<ul class="list-disc pl-4 space-y-2">';
                                        foreach ($record->attachments as $attachment) {
                                            $url = \Illuminate\Support\Facades\Storage::url($attachment->file_path);
                                            $nama = $attachment->filename;
                                            $html .= "<li><a href='{$url}' target='_blank' class='text-primary-600 hover:underline font-bold flex items-center gap-2'>{$nama}</a></li>";
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
                                    ->maxSize(10240)
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
                        'draft' => 'gray',
                        'pending_approval' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'revision_needed' => 'danger',
                        'completed' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
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
                Tables\Actions\EditAction::make()->visible(fn ($record) => $record->status !== 'completed'),
                Tables\Actions\DeleteAction::make()->visible(fn ($record) => $record->status !== 'completed'),

                // GRUP TOMBOL PROSES
                Tables\Actions\ActionGroup::make([
                    // A. TOMBOL PRINT
                    Tables\Actions\Action::make('print')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn (OutgoingLetter $record) => route('outgoing.print', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'completed'),

                    // B. TOMBOL AMBIL QR CODE
                    Tables\Actions\Action::make('download_qr')
                        ->label('Ambil QR Code')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->url(fn (OutgoingLetter $record) => route('outgoing-letters.download-qr', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (OutgoingLetter $record) => in_array($record->status, ['approved', 'completed'])),

                    // C. TOMBOL FINALISASI
                    Tables\Actions\Action::make('finalize')
                        ->label('Finalisasi Surat')
                        ->icon('heroicon-o-lock-closed')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Finalisasi Surat?')
                        ->modalDescription('Pastikan nomor surat sudah diisi dan FILE FINAL sudah diupload. Surat akan dikunci.')
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'approved')
                        ->action(function (OutgoingLetter $record) {
                            if (empty($record->letter_number)) {
                                Notification::make()->danger()->title('Gagal: Nomor Surat Kosong!')->send();
                                return;
                            }
                        
                            if (empty($record->final_file_path)) {
                                Notification::make()->danger()->title('Gagal: File Surat Belum Diupload!')->send();
                                return;
                            }

                            $updateData = ['status' => 'completed'];
                            if (empty($record->signature_code)) {
                                $updateData['signature_code'] = (string) \Illuminate\Support\Str::uuid();
                            }

                            $record->update($updateData);
                            Notification::make()->success()->title('Surat Final & Terkunci')->send();
                        }),

                    // D. TOMBOL AJUKAN
                    Tables\Actions\Action::make('submit')
                        ->label('Ajukan Verifikasi')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('blue')
                        ->requiresConfirmation()
                        ->visible(fn (OutgoingLetter $record) => in_array($record->status, ['draft', 'revision_needed']))
                        ->action(fn (OutgoingLetter $record) => $record->update(['status' => 'pending_approval'])),

                    // E. TOMBOL SETUJUI (Pimpinan)
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'pending_approval')
                        ->action(function (OutgoingLetter $record) {
                            $signatureCode = (string) \Illuminate\Support\Str::uuid();
                            $record->update([
                                'status' => 'approved',
                                'approved_at' => now(),
                                'signature_code' => $signatureCode,
                            ]);
                            Notification::make()->success()->title('Surat Disetujui & QR Code Dibuat')->send();
                        }),

                    // F. TOMBOL MINTA REVISI (Pimpinan)
                    Tables\Actions\Action::make('request_revision')
                        ->label('Minta Revisi')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'pending_approval')
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
                ->color('info'),
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