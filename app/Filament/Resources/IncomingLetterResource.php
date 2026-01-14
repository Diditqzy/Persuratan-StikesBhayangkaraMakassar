<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomingLetterResource\Pages;
use App\Models\IncomingLetter;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
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
    protected static ?string $navigationGroup = 'Manajemen Surat';
    protected static ?string $navigationLabel = 'Surat Masuk';
    protected static ?string $modelLabel = 'Surat Masuk';
    protected static ?string $pluralModelLabel = 'Surat Masuk';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Informasi Surat')
                            ->description('Detail identitas surat masuk.')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('agenda_number')
                                        ->label('No. Agenda (Internal)')
                                        ->prefix('#')
                                        ->placeholder('Contoh: 001/A/2024')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('sender')
                                        ->label('Instansi Pengirim')
                                        ->prefixIcon('heroicon-m-building-office')
                                        ->placeholder('Nama Instansi')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                                Grid::make([
                                    'default' => 1,
                                    'md' => 3,
                                ])->schema([
                                    TextInput::make('reference_number')
                                        ->label('Nomor Surat (Asli)')
                                        ->placeholder('Nomor yang tertera di surat')
                                        ->columnSpan(1) 
                                        ->required(),

                                    TextInput::make('subject')
                                        ->label('Perihal')
                                        ->placeholder('Inti isi surat...')
                                        ->columnSpan(2) 
                                        ->required(),
                                ]),

                                Grid::make(2)->schema([
                                    DatePicker::make('letter_date')
                                        ->label('Tanggal Tertulis di Surat')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->required(),

                                    DatePicker::make('received_date')
                                        ->label('Tanggal Diterima Admin')
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->default(now())
                                        ->required(),
                                ]),
                            ]),

                        Section::make('Berkas & Keterangan')
                            ->collapsed(false)
                            ->schema([
                                Textarea::make('description')
                                    ->label('Ringkasan Isi')
                                    ->rows(3)
                                    ->placeholder('Tuliskan poin penting surat di sini...'),

                                FileUpload::make('file_path')
                                    ->label('Scan Dokumen Asli')
                                    ->disk('public')
                                    ->directory('surat-masuk')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->maxSize(10240)
                                    ->downloadable()
                                    ->openable()
                                    ->previewable()
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]) 
                    ->disabled(fn () => Auth::user()->role === 'pimpinan'), 

                Group::make()
                    ->schema([
                        Section::make('Lembar Disposisi')
                            ->description('Instruksi Pimpinan')
                            ->icon('heroicon-m-pencil-square')
                            ->schema([
                                Repeater::make('dispositions')
                                    ->relationship()
                                    ->hiddenLabel()
                                    ->schema([
                                        Forms\Components\Hidden::make('user_id')
                                            ->default(fn () => Auth::id())
                                            ->dehydrated()
                                            ->required(),

                                        Select::make('target_division')
                                            ->label('Teruskan Ke')
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
                                            ->label('Instruksi')
                                            ->placeholder('Contoh: Tindak lanjuti...')
                                            ->rows(2)
                                            ->required(),
                                    ])
                                    ->itemLabel(fn (array $state) => $state['target_division'] ?? 'Disposisi Baru')
                                    ->collapsible()
                                    ->collapsed(false) 
                                    ->addable(fn () => Auth::user()->role === 'pimpinan')
                                    ->deletable(fn () => Auth::user()->role === 'pimpinan'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->visible(fn () => Auth::user()->role === 'pimpinan' || Auth::user()->role === 'admin'), 
            ])
            ->columns(3); 
    }

 public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agenda_number')
                    ->label('No. Agenda')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('No. Surat')
                    ->searchable()
                    ->icon('heroicon-m-document-text')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sender')
                    ->label('Pengirim')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn (Tables\Columns\TextColumn $column): ?string => $column->getState()),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal')
                    ->searchable()
                    ->limit(30)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('received_date')
                    ->label('Tanggal Terima')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
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
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // 
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail') 
                    ->button()
                    ->size('xs')
                    ->color('gray'),

                Tables\Actions\EditAction::make()
                    ->label(fn () => Auth::user()->role === 'pimpinan' ? 'Disposisi' : 'Edit')
                    ->button()
                    ->size('xs')
                    ->color(fn () => Auth::user()->role === 'pimpinan' ? 'warning' : 'primary'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()->role === 'admin')
                    ->label('Hapus')
                    ->button()
                    ->size('xs')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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