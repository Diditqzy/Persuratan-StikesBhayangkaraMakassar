<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutgoingLetterResource\Pages;
use App\Models\OutgoingLetter;
use App\Models\OutgoingDisposition;
use Filament\Forms;
use Filament\Forms\Get; // Wajib untuk logika kondisi
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
                // --- SECTION 0: ALERT INFO (Pesan Revisi/Tolak) ---
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

                // --- SECTION 1: PILIH JENIS SURAT (Menentukan Form) ---
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\Select::make('type_id')
                            ->relationship('type', 'name')
                            ->label('Jenis Surat')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live() // KUNCI: Form bereaksi saat ini dipilih
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('additional_data', [])), // Reset data jika ganti jenis

                        Forms\Components\TextInput::make('subject')
                            ->label('Perihal / Judul Surat')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('recipient')
                            ->label('Tujuan / Kepada')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('letter_date')
                            ->label('Tanggal Surat')
                            ->required()
                            ->default(now()),
                    ])->columns(2),

                // --- SECTION 2: FORM KHUSUS SKAK (ID = 1) ---
                Forms\Components\Section::make('Data Mahasiswa (Otomatis)')
                    ->description('Lengkapi data berikut untuk digenerate oleh sistem.')
                    ->schema([
                        Forms\Components\TextInput::make('additional_data.nama')
                            ->label('Nama Lengkap')
                            ->required(),
                        
                        Forms\Components\TextInput::make('additional_data.nim')
                            ->label('NIM')
                            ->required(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('additional_data.prodi')
                                    ->label('Program Studi')
                                    ->required(),
                                
                                Forms\Components\TextInput::make('additional_data.semester')
                                    ->label('Semester')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\TextInput::make('additional_data.tingkat')
                                    ->label('Tingkat')
                                    ->required(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('additional_data.tempat_lahir')
                                    ->label('Tempat Lahir')
                                    ->required(),
                                
                                Forms\Components\DatePicker::make('additional_data.tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->required(),
                            ]),

                        Forms\Components\Textarea::make('additional_data.alamat')
                            ->label('Alamat Lengkap')
                            ->rows(2)
                            ->required()
                            ->columnSpanFull(),
                        
                        // Upload Bukti Bayar (Khusus SKAK disimpan di JSON additional_data atau attachments)
                        // Kita simpan di Repeater Attachments di bawah agar seragam, tapi diberi label khusus.
                    ])
                    ->visible(fn (Get $get) => $get('type_id') == 1) // HANYA MUNCUL JIKA ID = 1
                    ->columns(2),

                // --- SECTION 3: UPLOAD FILE MANUAL (ID != 1) ---
                Forms\Components\Section::make('File Surat Manual')
                    ->description('Upload file surat yang sudah diketik manual (Word/PDF).')
                    ->schema([
                         Forms\Components\FileUpload::make('final_file_path') 
                            ->label('Upload Dokumen Surat')
                            ->disk('public') 
                            ->directory('surat-keluar')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) 
                            ->downloadable() 
                            ->openable()
                            ->required(fn (Get $get) => $get('type_id') != 1) // Wajib jika BUKAN SKAK
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get) => $get('type_id') != 1), // Sembunyi jika SKAK

                // --- SECTION 4: LAMPIRAN PENDUKUNG (UKT, dll) ---
                Forms\Components\Section::make('Lampiran Pendukung')
                    ->schema([
                        Forms\Components\Placeholder::make('info_lampiran')
                            ->content('Khusus Surat Keterangan Aktif Kuliah, WAJIB upload Bukti Pembayaran UKT Terakhir.')
                            ->extraAttributes(['class' => 'text-primary-600 font-bold'])
                            ->visible(fn (Get $get) => $get('type_id') == 1),

                        Repeater::make('attachments')
                            ->relationship()
                            ->label(fn (Get $get) => $get('type_id') == 1 ? 'Bukti UKT / Lampiran' : 'Lampiran Surat')
                            ->schema([
                                Forms\Components\TextInput::make('filename')
                                    ->label('Nama File (Cth: Bukti UKT)')
                                    ->required(),
                                FileUpload::make('file_path')
                                    ->label('File')
                                    ->disk('public')
                                    ->directory('lampiran-surat-keluar')
                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword'])
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah File'),
                    ]),

                // --- SECTION 5: ADMIN ONLY (Nomor & Signer) ---
                Forms\Components\Section::make('Validasi & Penomoran')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'submitted' => 'Pengajuan Baru',
                                'draft' => 'Draft',
                                'pending_approval' => 'Menunggu TTD',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'completed' => 'Selesai',
                            ])
                            ->default('submitted')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('letter_number')
                            ->label('Nomor Surat')
                            ->placeholder('Otomatis saat disetujui')
                            ->disabled(),

                        Forms\Components\Select::make('signer_id')
                            ->relationship('signer', 'name') // Menampilkan nama penanda tangan
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->user->name} ({$record->position})")
                            ->label('Penanda Tangan')
                            ->required(fn (string $operation, $record) => 
                                $operation === 'edit' && 
                                $record && 
                                in_array($record->status, ['pending_approval', 'approved', 'completed'])
                            )
                            ->visible(fn ($record) => 
                                $record && 
                                in_array($record->status, ['draft', 'pending_approval', 'approved', 'completed', 'revision_needed'])
                            ),
                    ])
                    ->columns(3)
                    ->visible(fn () => Auth::user()->role === 'admin'), // Cuma Admin yang lihat detail ini saat edit
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Tgl Pengajuan')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('type.name')->label('Jenis')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Perihal')->limit(30),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'draft' => 'gray',
                        'pending_approval' => 'warning',
                        'approved' => 'success',
                        'rejected', 'revision_needed' => 'danger',
                        'completed' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'Baru',
                        'pending_approval' => 'Menunggu TTD',
                        'revision_needed' => 'Revisi',
                        default => ucfirst($state),
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // LOGIKA ACTION BUTTONS (Sama seperti yang kamu kirim sebelumnya)
                // Saya ringkas agar fokus ke Form di atas.
                // Action View, Edit, Verify, Reject, Print ada di sini
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print')
                    ->icon('heroicon-o-printer')
                    ->url(fn (OutgoingLetter $record) => route('outgoing.print', $record))
                    ->visible(fn ($record) => $record->status === 'completed'),
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