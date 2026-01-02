<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutgoingLetterResource\Pages;
use App\Models\OutgoingLetter;
use App\Models\OutgoingDisposition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
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

    protected static ?string $navigationIcon = 'heroicon-o-document-text'; // Ikon Dokumen
    protected static ?string $navigationLabel = 'Surat Keluar'; // Menu di Sidebar
    protected static ?string $modelLabel = 'Surat Keluar'; // Label di tombol

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
                // --- [BARU] KOTAK PERINGATAN REVISI ---
                Forms\Components\Section::make('Disposisi Pimpinan') // <--- GANTI JUDULNYA JADI INI
                    ->schema([
                        Forms\Components\Placeholder::make('revisi_note')
                            ->label('Instruksi / Catatan:') // <--- LABEL LEBIH UMUM
                            ->content(fn ($record) => $record?->dispositions()->latest()->first()?->instruction ?? '-Tidak ada catatan-')
                            ->extraAttributes(['class' => 'text-danger-600 font-bold text-lg']),
                        
                        // Opsional: Tampilkan siapa yang memberi disposisi
                        Forms\Components\Placeholder::make('disposition_sender')
                            ->label('Dari:')
                            ->content(fn ($record) => $record?->dispositions()->latest()->first()?->sender->name ?? '-'),
                    ])
                    ->icon('heroicon-o-chat-bubble-left-right') // Ganti ikon jadi chat/pesan
                    ->iconColor('danger') 
                    // Logic: Muncul kalau statusnya Revision Needed
                    ->visible(fn ($record) => $record?->status === 'revision_needed')
                    ->columns(2), // Biar rapi sebelahan (Pesan & Pengirim)

                // === BAGIAN 1: Data Utama Surat ===
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
                            ->disabled() 
                            ->dehydrated()
                            ->placeholder('Otomatis diisi saat status Disetujui'),

                        Forms\Components\RichEditor::make('content_data')
                            ->label('Isi Surat')
                            ->columnSpanFull(),
                    ])->columns(2),

                // === BAGIAN 2: Status & Verifikasi ===
                Forms\Components\Section::make('Status & Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Surat')
                            ->options([
                                'draft' => 'Draft (Konsep)',
                                'pending_approval' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('draft')
                            ->required()
                            ->disabled(fn (string $operation) => $operation === 'edit'),
                            
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pemohon')
                            ->searchable()
                            ->required()
                            ->default(Auth::id()),
                    ])->columns(1),

                // === BAGIAN 3: LAMPIRAN (INI YANG BARU KITA TAMBAH) ===
                // Letaknya di bawah Section Status, tapi masih di dalam schema([])
                Forms\Components\Section::make('Lampiran Dokumen')
                    ->description('Upload file draft surat atau dokumen pendukung lainnya.')
                    ->schema([
                        Repeater::make('attachments') // Harus sama dengan nama relasi di Model
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('filename')
                                    ->label('Nama File')
                                    ->required(),

                                FileUpload::make('file_path')
                                    ->label('Upload File')
                                    ->disk('public') // Simpan di folder public
                                    ->directory('lampiran-surat-keluar')
                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                    ->maxSize(5120) // 5MB
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Lampiran'),
                    ]),

            ]); // Tutup Schema Utama
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('letter_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Jenis')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->sortable(),

                // Badge Status Berwarna
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending_approval' => 'warning', // Kuning
                        'approved' => 'success', // Hijau
                        'rejected' => 'danger', // Merah
                        'revision_needed' => 'danger', // Merah
                        // default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'pending_approval' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'revision_needed' => 'Perlu Revisi',
                        default => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter nanti
            ])
            // --- BAGIAN INI YANG KITA MODIFIKASI TOTAL ---
            ->actions([
                // 1. Tombol Edit & Delete (Selalu Muncul)
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                // 2. GRUP TOMBOL PROSES (Titik Tiga)
                Tables\Actions\ActionGroup::make([
                    
                    // A. TOMBOL LIHAT PDF (Muncul kalau sudah Approved)
                    Tables\Actions\Action::make('print')
                        ->label('Cetak / Download PDF')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn (OutgoingLetter $record) => route('outgoing.print', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'approved'),

                    // B. TOMBOL AJUKAN (Draft -> Pending)
                    Tables\Actions\Action::make('submit')
                        ->label('Ajukan Verifikasi')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('blue')
                        ->requiresConfirmation()
                        ->modalHeading('Ajukan Surat?')
                        ->modalDescription('Surat akan dikirim ke pimpinan untuk diperiksa.')
                        // Muncul kalau status Draft ATAU Revisi (biar bisa diajukan ulang)
                        ->visible(fn (OutgoingLetter $record) => in_array($record->status, ['draft', 'revision_needed']))
                        ->action(fn (OutgoingLetter $record) => $record->update(['status' => 'pending_approval'])),

                    // C. TOMBOL SETUJUI (Pending -> Approved)
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui Surat')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'pending_approval')
                        ->action(function (OutgoingLetter $record) {
                            $record->update([
                                'status' => 'approved',
                                'approved_at' => now(),
                            ]);
                        }),

                    // D. TOMBOL MINTA REVISI (Pending -> Revision Needed)
                    Tables\Actions\Action::make('request_revision')
                        ->label('Minta Revisi')
                        ->icon('heroicon-o-pencil-square') // Ikon Edit
                        ->color('warning')
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'pending_approval')
                        ->form([
                            Forms\Components\Textarea::make('instruction')
                                ->label('Catatan Revisi untuk Admin')
                                ->placeholder('Contoh: Logo salah, tolong perbaiki paragraf 2.')
                                ->required(),
                        ])
                        ->action(function (OutgoingLetter $record, array $data) {
                            // 1. Simpan Catatan ke Tabel Disposisi Keluar
                            OutgoingDisposition::create([
                                'outgoing_letter_id' => $record->id,
                                'user_id' => Auth::id(), // ID Pimpinan yang login
                                'instruction' => $data['instruction'], // Catatan dari form
                            ]);

                            // 2. Ubah Status Surat
                            $record->update(['status' => 'revision_needed']);
                            
                            // 3. Notifikasi (Opsional)
                            \Filament\Notifications\Notification::make()
                                ->title('Surat Dikembalikan untuk Revisi')
                                ->success()
                                ->send();
                        }),

                ])
                ->label('Proses')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('info')
                ->tooltip('Menu Persetujuan'),
            ])
            // ----------------------------------------------
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]); 
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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