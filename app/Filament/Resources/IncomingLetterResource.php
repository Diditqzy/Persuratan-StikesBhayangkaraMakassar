<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomingLetterResource\Pages;
use App\Filament\Resources\IncomingLetterResource\RelationManagers;
use App\Models\IncomingLetter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IncomingLetterResource extends Resource
{
    protected static ?string $model = IncomingLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Surat Masuk')
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
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                            ->rules(['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']) // Validasi sisi server (Max 5MB)
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                // --- SECTION 2: DISPOSISI ---
                Section::make('Lembar Disposisi')
                    ->description('Instruksi pimpinan untuk surat ini')
                    ->schema([
                        Forms\Components\Repeater::make('dispositions')
                            ->relationship() // Relasi ke tabel incoming_dispositions
                            ->label('Daftar Instruksi')
                            ->schema([
                                // Menampilkan siapa yang membuat (Otomatis)
                                Forms\Components\Select::make('user_id')
                                    ->label('Pemberi Instruksi')
                                    ->relationship('user', 'name') // Ambil nama user otomatis
                                    ->default(fn () => Auth::id()) // Default user yg login
                                    ->disabled() // Tidak bisa diubah (Read Only)
                                    ->dehydrated() // Wajib ada: Supaya data tetap tersimpan meski disabled
                                    ->required()
                                    ->columnSpanFull(), // Atau columns(1) sesuai selera

                                Forms\Components\Select::make('target_division')
                                    ->label('Tujuan Disposisi')
                                    ->options([
                                        'Prodi Keperawatan' => 'Prodi Keperawatan',
                                        'Prodi Kebidanan' => 'Prodi Kebidanan',
                                        'Prodi Ners' => 'Prodi Ners',
                                        'Bagian Keuangan' => 'Bagian Keuangan',
                                        'Bagian Akademik' => 'Bagian Akademik',
                                        'LPPM' => 'LPPM',
                                        'Kemahasiswaan' => 'Kemahasiswaan',
                                    ])
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Textarea::make('instruction')
                                    ->label('Isi Instruksi')
                                    ->required()
                                    ->columnSpanFull(),

                                // // Hidden: Simpan ID user otomatis
                                // Forms\Components\Hidden::make('user_id')
                                //     ->default(fn () => auth()->id()),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Instruksi Baru')
                            ->itemLabel(fn (array $state): ?string => $state['target_division'] ?? null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agenda_number')
                    ->label('No. Agenda')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('sender')
                    ->label('Pengirim')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal')
                    ->limit(30)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('received_date')
                    ->label('Diterima')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Disposisi')
                    ->badge() // Biar tampil gaya lencana (kotak warna)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting_disposition' => 'Menunggu',
                        'dispositioned' => 'Selesai',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'waiting_disposition' => 'warning', // Kuning
                        'dispositioned' => 'success', // Hijau
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Nanti kita tambah tombol "Disposisi" di sini
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Daftarkan Relation Manager di sini
            // RelationManagers\DispositionsRelationManager::class,
        ];
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
