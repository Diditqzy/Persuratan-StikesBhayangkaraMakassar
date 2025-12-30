<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterTypeResource\Pages;
use App\Filament\Resources\LetterTypeResource\RelationManagers;
use App\Models\LetterType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LetterTypeResource extends Resource
{
    protected static ?string $model = LetterType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Jenis Surat'; // Menu di Sidebar
    protected static ?string $modelLabel = 'Jenis Surat'; // Label di tombol

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true) // Aktifkan live update
                    // Otomatis isi kolom Code saat Name diketik
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('code', Str::slug($state))), 
                
                TextInput::make('code')
                    ->required()
                    ->maxLength(255),

                Toggle::make('has_template')
                    ->label('Punya Template Khusus?')
                    ->reactive(), // Biar form di bawahnya bisa muncul/hilang

                RichEditor::make('template_content')
                    ->label('Isi Template Surat')
                    // Hanya muncul kalau toggle 'has_template' dinyalakan
                    ->hidden(fn (Get $get) => ! $get('has_template')) 
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->badge(), // Tampil gaya lencana
                IconColumn::make('has_template')
                    ->boolean()
                    ->label('Template?'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
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
