<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterTypeResource\Pages;
use App\Models\LetterType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('code', Str::slug($state))),
                
                TextInput::make('code')
                    ->label('Kode Surat')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Contoh: SM / SK / UM'),
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