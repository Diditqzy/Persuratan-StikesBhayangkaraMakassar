<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\LetterType;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater; 
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\LetterTypeResource\Pages;

class LetterTypeResource extends Resource
{
    protected static ?string $model = LetterType::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Jenis Surat';
    protected static ?string $modelLabel = 'Jenis Surat';
    protected static ?string $navigationGroup = 'Pengaturan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Jenis Surat')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, $state) => $set('code', Str::slug($state))),
                
                TextInput::make('code')
                    ->label('Kode Surat')
                    ->required(),

                // --- FORM BUILDER SECTION ---
                Section::make('Desain Formulir Pengajuan')
                    ->description('Atur kolom inputan yang harus diisi mahasiswa. Klik "Tambah Inputan" untuk menambah pertanyaan.')
                    ->schema([
                        Repeater::make('form_config')
                            ->label('Daftar Inputan')
                            ->schema([
                                // 1. Label Pertanyaan
                                TextInput::make('label')
                                    ->label('Nama Input / Pertanyaan')
                                    ->placeholder('Contoh: Semester, No. HP, Upload KTP')
                                    ->required()
                                    ->columnSpan(2),
                                
                                // 2. Jenis Input
                                Select::make('type')
                                    ->label('Jenis Input')
                                    ->options([
                                        'text' => 'Teks / Angka / Tanggal (Input Biasa)',
                                        'file' => 'Upload File (PDF/Gambar)',
                                    ])
                                    ->required(),
                                
                                // 3. Opsi Wajib Isi
                                Toggle::make('required')
                                    ->label('Wajib Diisi?')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(4) // Agar tampil rapi ke samping
                            ->addActionLabel('Tambah Inputan Baru')
                            ->reorderableWithButtons() // Bisa digeser urutannya
                            ->collapsible() // Bisa dilipat biar ga penuh
                            ->cloneable() // Bisa diduplikasi
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null), // Label header repeater ambil dari inputan label
                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->sortable()->searchable(),
                TextColumn::make('code')->label('Kode')->badge()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => Auth::user()->role === 'admin'),
                
                // DELETE DIBATASI: ID 1 (Template Sistem) TIDAK BOLEH DIHAPUS
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => 
                        Auth::user()->role === 'admin' && 
                        $record->id !== 1 
                    ),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLetterTypes::route('/'),
            'create' => Pages\CreateLetterType::route('/create'),
            'edit' => Pages\EditLetterType::route('/{record}/edit'),
        ];
    }
}