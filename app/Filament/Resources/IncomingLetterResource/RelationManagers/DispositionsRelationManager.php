<?php

namespace App\Filament\Resources\IncomingLetterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class DispositionsRelationManager extends RelationManager
{
    protected static string $relationship = 'dispositions';

    protected static ?string $title = 'Disposisi / Perintah';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Pilihan Divisi Tujuan (Sesuai kolom string 'target_division')
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
                    ->required()
                    ->columnSpanFull(),

                // Isi Instruksi
                Forms\Components\Textarea::make('instruction')
                    ->label('Instruksi / Pesan')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),

                // Hidden: Otomatis isi ID User yang sedang login
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // Optimasi Query (N+1 Fix)
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user'))
            ->recordTitleAttribute('instruction')
            ->columns([
                // Siapa yang memberi perintah
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemberi Perintah')
                    ->sortable(),

                // Ke mana surat diteruskan
                Tables\Columns\TextColumn::make('target_division')
                    ->label('Tujuan')
                    ->badge()
                    ->color('warning'),

                // Apa isinya
                Tables\Columns\TextColumn::make('instruction')
                    ->label('Instruksi')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Disposisi')
                    // Pastikan user_id terisi paksa saat simpan
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}