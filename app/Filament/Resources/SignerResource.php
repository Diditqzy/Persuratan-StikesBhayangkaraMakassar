<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Signer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SignerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SignerResource\RelationManagers;

class SignerResource extends Resource
{
    protected static ?string $model = Signer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name') // Ambil data dari tabel users
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Pilih Pengguna'),

                TextInput::make('position')
                    ->label('Jabatan (Contoh: Ketua STIKES)')
                    ->required(),

                TextInput::make('employee_id')
                    ->label('NIP / NIDN')
                    ->required(),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),

                DatePicker::make('started_at')
                    ->label('Mulai Menjabat'),
                
                DatePicker::make('ended_at')
                    ->label('Selesai Menjabat'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name') // Menampilkan nama user dari relasi
                    ->label('Nama Pejabat')
                    ->searchable(),
                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),
                TextColumn::make('employee_id')
                    ->label('NIP/NIDN'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSigners::route('/'),
            'create' => Pages\CreateSigner::route('/create'),
            'edit' => Pages\EditSigner::route('/{record}/edit'),
        ];
    }
}
