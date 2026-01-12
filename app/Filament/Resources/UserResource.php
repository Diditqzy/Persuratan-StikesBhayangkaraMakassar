<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Manajemen Pengguna';
    protected static ?string $navigationGroup = 'Pengaturanfffff';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->disabled(), // Read only (dari SIAKAD)
                
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->disabled(), // Read only
                
                Forms\Components\TextInput::make('identity_number')
                    ->label('NIM / NIP')
                    ->disabled(), // Read only

                // INI YANG PENTING: Pimpinan bisa ubah role
                Forms\Components\Select::make('role')
                    ->label('Hak Akses / Jabatan')
                    ->options([
                        'user' => 'User (Mahasiswa/Dosen Biasa)',
                        'admin' => 'Admin (Operator Surat)',
                        'pimpinan' => 'Pimpinan (Ketua/Wakil)',
                    ])
                    ->required()
                    ->selectablePlaceholder(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('identity_number')->label('NIP/NIM')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pimpinan' => 'danger',
                        'admin' => 'warning',
                        'user' => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Atur Role'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}