<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomingLetterResource\Pages;
use App\Models\IncomingLetter;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class IncomingLetterResource extends Resource
{
    protected static ?string $model = IncomingLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Surat Masuk';
    protected static ?string $modelLabel = 'Surat Masuk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- SECTION 1: DATA SURAT ---
                Section::make('Data Surat Masuk')
                    ->description('Informasi utama surat dari instansi luar.')
                    ->schema([
                        TextInput::make('agenda_number')
                            ->label('Nomor Agenda (Internal)')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('sender')
                            ->label('Instansi Pengirim')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('reference_number')
                            ->label('Nomor Surat (Dari Pengirim)')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('subject')
                            ->label('Perihal')
                            ->required()
                            ->columnSpanFull(),

                        DatePicker::make('letter_date')
                            ->label('Tanggal Surat')
                            ->required(),

                        DatePicker::make('received_date')
                            ->label('Tanggal Diterima')
                            ->required()
                            ->default(now()),

                        Textarea::make('description')
                            ->label('Ringkasan / Keterangan')
                            ->columnSpanFull(),

                        FileUpload::make('file_path')
                            ->label('Scan Surat Asli')
                            ->disk('public')
                            ->directory('surat-masuk')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    // RULE: Disable edit data surat jika user adalah Pimpinan
                    ->disabled(fn () => Auth::user()->role === 'pimpinan'),

                // --- SECTION 2: DISPOSISI ---
                Section::make('Lembar Disposisi')
                    ->description('Instruksi pimpinan untuk surat ini')
                    ->schema([
                        Repeater::make('dispositions')
                            ->relationship()
                            ->label('Daftar Instruksi')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Pemberi Instruksi')
                                    ->relationship('user', 'name')
                                    ->default(fn () => Auth::id())
                                    ->disabled()   // Tidak bisa diubah user
                                    ->dehydrated() // Tetap dikirim ke database
                                    ->required()
                                    ->columnSpanFull(),

                                Select::make('target_division')
                                    ->label('Tujuan Disposisi')
                                    ->options([
                                        'Prodi Keperawatan' => 'Prodi Keperawatan',
                                        'Prodi Kebidanan' => 'Prodi Kebidanan',
                                        'Prodi Ners' => 'Prodi Ners',
                                        'Bagian Keuangan' => 'Bagian Keuangan',
                                        'Bagian Akademik' => 'Bagian Akademik',
                                        'LPPM' => 'LPPM',
                                        'Kemahasiswaan' => 'Kemahasiswaan',
                                        'Arsip' => 'Arsip',
                                    ])
                                    ->searchable()
                                    ->required(),

                                Textarea::make('instruction')
                                    ->label('Isi Instruksi')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Instruksi')
                            ->itemLabel(fn (array $state) => $state['target_division'] ?? 'Disposisi Baru'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agenda_number')
                    ->label('No. Agenda')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sender')
                    ->label('Pengirim')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('received_date')
                    ->label('Diterima')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting_disposition' => 'Menunggu',
                        'dispositioned' => 'Selesai',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'waiting_disposition' => 'warning',
                        'dispositioned' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Ubah label tombol Edit sesuai Role
                Tables\Actions\EditAction::make()
                    ->label(fn () => Auth::user()->role === 'pimpinan' ? 'Disposisi' : 'Edit')
                    ->icon(fn () => Auth::user()->role === 'pimpinan' ? 'heroicon-o-pencil-square' : 'heroicon-o-pencil'),
                
                // Hapus hanya boleh admin
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()->role === 'admin'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->role === 'admin'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomingLetters::route('/'),
            'create' => Pages\CreateIncomingLetter::route('/create'),
            'edit' => Pages\EditIncomingLetter::route('/{record}/edit'),
        ];
    }
}