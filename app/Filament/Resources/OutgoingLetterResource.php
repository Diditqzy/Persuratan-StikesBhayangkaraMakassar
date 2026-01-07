<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutgoingLetterResource\Pages;
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['type', 'user', 'signer'])
            ->withoutGlobalScopes();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- ALERT SECTION ---
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

            // --- INFO DASAR ---
            Forms\Components\Section::make('Informasi Dasar')
                ->schema([
                    Forms\Components\Select::make('type_id')
                        ->relationship('type', 'name')
                        ->label('Jenis Surat')
                        ->searchable()->preload()->required()->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('additional_data', [])),

                    Forms\Components\TextInput::make('subject')->label('Perihal')->required()->maxLength(255)->columnSpan(2),
                    Forms\Components\TextInput::make('recipient')->label('Tujuan')->required()->maxLength(255),
                    Forms\Components\DatePicker::make('letter_date')->label('Tanggal')->required()->default(now()),
                ])->columns(2),

            // --- KHUSUS SKAK (ID 1) ---
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
                    Forms\Components\Textarea::make('additional_data.alamat')->label('Alamat')->rows(2)->required()->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('type_id') == 1)
                ->columns(2),

            // --- UPLOAD MANUAL (NON-SKAK) ---
            Forms\Components\Section::make('File Surat')
                ->schema([
                    FileUpload::make('final_file_path')
                        ->label('Upload Dokumen')
                        ->disk('public')->directory('surat-keluar')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)->downloadable()->openable()
                        ->required(fn (Get $get) => $get('type_id') != 1)
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('type_id') != 1),

            // --- LAMPIRAN ---
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
                            Forms\Components\TextInput::make('filename')->label('Nama File')->required(),
                            FileUpload::make('file_path')->label('File')->disk('public')->directory('lampiran-surat-keluar')->required(),
                        ])->columns(2),
                ]),

            // --- ADMIN VALIDATION ---
            Forms\Components\Section::make('Validasi Admin')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'submitted' => 'Pengajuan Baru', 'draft' => 'Draft',
                            'pending_approval' => 'Menunggu TTD', 'approved' => 'Disetujui',
                            'rejected' => 'Ditolak', 'completed' => 'Selesai',
                        ])
                        ->default('submitted')->disabled()->dehydrated(),
                    Forms\Components\TextInput::make('letter_number')->label('Nomor Surat')->disabled(),
                ])
                ->visible(fn () => Auth::user()->role === 'admin'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Tgl')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('type.name')->label('Jenis')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Perihal')->limit(30),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info', 'draft' => 'gray', 'pending_approval' => 'warning',
                        'approved' => 'success', 'rejected', 'revision_needed' => 'danger',
                        'completed' => 'primary', default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'submitted' => 'Baru', 'pending_approval' => 'Menunggu TTD',
                        'revision_needed' => 'Revisi', default => ucfirst($state),
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->modalWidth('4xl')
                    ->extraModalFooterActions([
                        // ACTION 1: APPROVE
                        Tables\Actions\Action::make('approve_modal')
                            ->label('Setujui & TTD')
                            ->color('success')
                            ->icon('heroicon-o-check-badge')
                            ->requiresConfirmation()
                            ->visible(fn (OutgoingLetter $record) => 
                                Auth::user()->role === 'pimpinan' && 
                                !in_array($record->status, ['approved', 'rejected'])
                            )
                            ->action(function (OutgoingLetter $record) {
                                // Cari Pimpinan
                                $pimpinan = Signer::whereHas('user', fn ($q) => $q->where('role', 'pimpinan'))->first();

                                if (!$pimpinan) {
                                    Notification::make()->title('Data Signer Pimpinan tidak ditemukan!')->danger()->send();
                                    return;
                                }

                                // Paksa simpan manual agar aman dari fillable
                                $record->signer_id = $pimpinan->id;
                                $record->status = 'approved';
                                $record->approved_at = now();
                                $record->save();

                                Notification::make()->title('Surat Disetujui')->success()->send();
                            }),

                        // ACTION 2: REVISI
                        Tables\Actions\Action::make('request_revision')
                            ->label('Revisi')
                            ->color('warning')
                            ->form([Textarea::make('note')->required()->label('Catatan')])
                            ->visible(fn (OutgoingLetter $record) => 
                                Auth::user()->role === 'pimpinan' && 
                                !in_array($record->status, ['approved', 'rejected', 'revision_needed'])
                            )
                            ->action(fn (OutgoingLetter $r, array $d) => $r->update([
                                'status' => 'revision_needed', 'rejection_note' => $d['note']
                            ])),

                        // ACTION 3: REJECT
                        Tables\Actions\Action::make('reject_modal')
                            ->label('Tolak')
                            ->color('danger')
                            ->form([Textarea::make('note')->required()->label('Alasan')])
                            ->visible(fn (OutgoingLetter $record) => 
                                Auth::user()->role === 'pimpinan' && 
                                !in_array($record->status, ['approved', 'rejected'])
                            )
                            ->action(fn (OutgoingLetter $r, array $d) => $r->update([
                                'status' => 'rejected', 'rejection_note' => $d['note']
                            ])),
                    ]),

                Tables\Actions\EditAction::make()->visible(fn () => Auth::user()->role === 'admin'),
                Tables\Actions\Action::make('print')
                    ->icon('heroicon-o-printer')
                    ->url(fn (OutgoingLetter $record) => route('outgoing.print', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => in_array($record->status, ['approved', 'completed'])),
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