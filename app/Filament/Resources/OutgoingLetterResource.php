<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutgoingLetterResource\Pages;
use App\Models\OutgoingDisposition;
use App\Models\OutgoingLetter;
use App\Models\Signer;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OutgoingLetterResource extends Resource
{
    protected static ?string $model = OutgoingLetter::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Surat Keluar';
    protected static ?string $modelLabel = 'Surat Keluar';
    protected static ?string $navigationGroup = 'Manajemen Surat';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['type', 'user', 'signer'])
            ->withoutGlobalScopes();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Status')
                ->schema([
                    Forms\Components\Placeholder::make('revisi_alert')
                        ->label('Catatan Revisi:')
                        ->content(fn ($record) => $record?->dispositions()->latest()->first()?->instruction)
                        ->extraAttributes(['class' => 'text-warning-600 font-bold'])
                        ->visible(fn ($record) => $record?->status === 'revision_needed'),
                    
                    Forms\Components\Placeholder::make('reject_alert')
                        ->label('Alasan Penolakan:')
                        ->content(fn ($record) => $record->rejection_note)
                        ->extraAttributes(['class' => 'text-danger-600 font-bold'])
                        ->visible(fn ($record) => $record?->status === 'rejected'),
                ])
                ->visible(fn ($record) => in_array($record?->status, ['revision_needed', 'rejected'])),

            Forms\Components\Section::make('Informasi Dasar')
                ->schema([
                    Forms\Components\Select::make('type_id')
                        ->relationship('type', 'name')
                        ->label('Jenis Surat')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('additional_data', [])),

                    Forms\Components\TextInput::make('subject')
                        ->label('Perihal')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('recipient')
                        ->label('Tujuan')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('letter_date')
                        ->label('Tanggal')
                        ->required()
                        ->default(now()),
                ])->columns(2),

            Forms\Components\Section::make('Data Mahasiswa')
                ->description('Data untuk generate otomatis.')
                ->schema([
                    Forms\Components\TextInput::make('additional_data.nama')->label('Nama')->required(),
                    Forms\Components\TextInput::make('additional_data.nim')->label('NIM')->required(),
                    
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('additional_data.prodi')->label('Prodi')->required(),
                        Forms\Components\TextInput::make('additional_data.semester')->label('Semester')->numeric()->required(),
                        Forms\Components\TextInput::make('additional_data.tingkat')->label('Tingkat')->required(),
                    ]),
                    
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('additional_data.tempat_lahir')->label('Tempat Lahir')->required(),
                        Forms\Components\DatePicker::make('additional_data.tanggal_lahir')->label('Tgl Lahir')->required(),
                    ]),
                    
                    Forms\Components\Textarea::make('additional_data.alamat')
                        ->label('Alamat')
                        ->rows(2)
                        ->required()
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('type_id') == 1)
                ->columns(2),

            Forms\Components\Section::make('File Surat Final (Admin)')
                ->description('Upload file surat yang telah diketik/dibuat oleh Admin.')
                ->schema([
                    FileUpload::make('final_file_path')
                        ->label('Upload Dokumen Surat (Word/PDF)')
                        ->disk('public')
                        ->directory('surat-keluar')
                        ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(10240)
                        ->downloadable()
                        ->openable()
                        // Wajib diisi jika BUKAN ID 1
                        ->required(fn (Get $get) => $get('type_id') != 1)
                        ->validationMessages([
                            'required' => 'File surat wajib diupload sebelum disimpan.',
                        ])
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('type_id') != 1),

            Forms\Components\Section::make('Lampiran Pendukung')
                ->schema([
                    Forms\Components\Placeholder::make('info')
                        ->content('Wajib upload Bukti Pembayaran UKT Terakhir untuk SKAK.')
                        ->extraAttributes(['class' => 'text-primary-600 font-bold'])
                        ->visible(fn (Get $get) => $get('type_id') == 1),

                    Repeater::make('attachments')
                        ->relationship()
                        ->label('File Lampiran')
                        ->schema([
                            Forms\Components\TextInput::make('filename')
                                ->label('Nama File')
                                ->required(),
                            
                            FileUpload::make('file_path')
                                ->label('File')
                                ->disk('public')
                                ->directory('lampiran-surat-keluar')
                                ->required(),
                        ])->columns(2),
                ]),

            Forms\Components\Section::make('Validasi Admin')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status Pengajuan')
                        ->options([
                            'submitted' => 'Pengajuan Baru',
                            'draft' => 'Draft',
                            'pending_approval' => 'Menunggu TTD',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'completed' => 'Selesai',
                        ])
                        ->default('submitted')
                        ->disabled() // Admin tidak boleh ubah
                        ->dehydrated(),
                        
                    Forms\Components\TextInput::make('letter_number')
                        ->label('Nomor Surat')
                        ->disabled(),
                ])
                ->visible(fn () => Auth::user()->role === 'admin'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Buat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Jenis')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal')
                    ->limit(30)
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->toggleable(),
                
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
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'submitted' => 'Baru',
                        'pending_approval' => 'Menunggu TTD',
                        'revision_needed' => 'Revisi',
                        default => ucfirst($state),
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->button()
                    ->size('xs')
                    ->color('gray'),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->button()
                    ->size('xs')
                    ->visible(fn () => Auth::user()->role === 'admin'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->button()
                    ->size('xs')
                    ->visible(fn (OutgoingLetter $r) => Auth::user()->role === 'admin' && $r->status !== 'completed'),

                Tables\Actions\Action::make('print')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->button()
                    ->size('xs')
                    ->color('info')
                    ->url(fn (OutgoingLetter $record) => route('outgoing.print', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => in_array($record->status, ['approved', 'completed'])),
                
                // Actions Pimpinan
                Tables\Actions\Action::make('approve_modal')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->button()
                    ->size('xs')
                    ->requiresConfirmation()
                    ->visible(fn (OutgoingLetter $record) => 
                        Auth::user()->role === 'pimpinan' && 
                        !in_array($record->status, ['approved', 'rejected'])
                    )
                    ->action(function (OutgoingLetter $record) {
                        $pimpinan = Signer::whereHas('user', fn ($q) => $q->where('role', 'pimpinan'))->first();

                        if (!$pimpinan) {
                            Notification::make()->title('Data Signer Pimpinan tidak ditemukan!')->danger()->send();
                            return;
                        }

                        $record->update([
                            'status' => 'approved',
                            'signer_id' => $pimpinan->id,
                            'approved_at' => now(),
                            'approved_by' => Auth::id(),
                        ]);

                        Notification::make()->title('Surat Disetujui')->success()->send();
                    }),
                
                Tables\Actions\Action::make('request_revision')
                    ->label('Revisi')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->size('xs')
                    ->form([
                        Textarea::make('note')->required()->label('Catatan Revisi')
                    ])
                    ->visible(fn (OutgoingLetter $record) => 
                        Auth::user()->role === 'pimpinan' && 
                        !in_array($record->status, ['approved', 'rejected', 'revision_needed'])
                    )
                    ->action(function (OutgoingLetter $record, array $data) {
                        OutgoingDisposition::create([
                            'outgoing_letter_id' => $record->id,
                            'user_id' => Auth::id(),
                            'instruction' => $data['note'],
                        ]);

                        $record->update([
                            'status' => 'revision_needed', 
                            'rejection_note' => $data['note']
                        ]);
                        
                        Notification::make()->title('Dikembalikan untuk revisi')->warning()->send();
                    }),
                
                Tables\Actions\Action::make('reject_modal')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->button()
                    ->size('xs')
                    ->form([
                        Textarea::make('note')->required()->label('Alasan Penolakan')
                    ])
                    ->visible(fn (OutgoingLetter $record) => 
                        Auth::user()->role === 'pimpinan' && 
                        !in_array($record->status, ['approved', 'rejected'])
                    )
                    ->action(function (OutgoingLetter $record, array $data) {
                        $record->update([
                            'status' => 'rejected', 
                            'rejection_note' => $data['note'],
                            'rejected_at' => now(),
                            'rejected_by' => Auth::id(),
                        ]);
                        Notification::make()->title('Surat Ditolak')->danger()->send();
                    }),
            ]);
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