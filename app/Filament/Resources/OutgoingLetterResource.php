<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutgoingLetterResource\Pages;
use App\Models\OutgoingLetter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Database\Eloquent\Builder;

class OutgoingLetterResource extends Resource
{
    protected static ?string $model = OutgoingLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text'; // Ikon Dokumen
    protected static ?string $navigationLabel = 'Surat Keluar'; // Menu di Sidebar
    protected static ?string $modelLabel = 'Surat Keluar'; // Label di tombol

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === BAGIAN 1: Data Utama Surat ===
                Forms\Components\Section::make('Informasi Surat')
                    ->description('Isi detail surat yang akan diajukan.')
                    ->schema([
                        Forms\Components\Select::make('type_id')
                            ->relationship('type', 'name')
                            ->label('Jenis Surat')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('signer_id')
                            ->label('Penanda Tangan (Pimpinan)')
                            ->options(function () {
                                return \App\Models\Signer::with('user')
                                    ->get()
                                    ->mapWithKeys(function ($signer) {
                                        return [$signer->id => $signer->user->name . ' - ' . $signer->position];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('subject')
                            ->label('Perihal / Judul')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('recipient')
                            ->label('Tujuan Surat (Kepada Yth.)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('letter_date')
                            ->label('Tanggal Surat')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\TextInput::make('letter_number')
                            ->label('Nomor Surat')
                            ->disabled() 
                            ->dehydrated()
                            ->placeholder('Otomatis diisi saat status Disetujui'),

                        Forms\Components\RichEditor::make('content_data')
                            ->label('Isi Surat')
                            ->columnSpanFull(),
                    ])->columns(2),

                // === BAGIAN 2: Status & Verifikasi ===
                Forms\Components\Section::make('Status & Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Surat')
                            ->options([
                                'draft' => 'Draft (Konsep)',
                                'pending_approval' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('draft')
                            ->required()
                            ->disabled(fn (string $operation) => $operation === 'edit'),
                            
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pemohon')
                            ->searchable()
                            ->required()
                            ->default(Auth::id()),
                    ])->columns(1),

                // === BAGIAN 3: LAMPIRAN (INI YANG BARU KITA TAMBAH) ===
                // Letaknya di bawah Section Status, tapi masih di dalam schema([])
                Forms\Components\Section::make('Lampiran Dokumen')
                    ->description('Upload file draft surat atau dokumen pendukung lainnya.')
                    ->schema([
                        Repeater::make('attachments') // Harus sama dengan nama relasi di Model
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('filename')
                                    ->label('Nama File')
                                    ->required(),

                                FileUpload::make('file_path')
                                    ->label('Upload File')
                                    ->disk('public') // Simpan di folder public
                                    ->directory('lampiran-surat-keluar')
                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                    ->maxSize(5120) // 5MB
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Lampiran'),
                    ]),

            ]); // Tutup Schema Utama
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('letter_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Jenis')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->sortable(),

                // Badge Status Berwarna
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending_approval' => 'warning', // Kuning
                        'approved' => 'success', // Hijau
                        'rejected' => 'danger', // Merah
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'pending_approval' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter nanti
            ])
            // --- BAGIAN INI YANG KITA MODIFIKASI TOTAL ---
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    // Buka di tab baru (wajib pakai url() dan openUrlInNewTab)
                    ->url(fn (OutgoingLetter $record) => route('outgoing.print', $record))
                    ->openUrlInNewTab()
                    // Cuma muncul kalau sudah Approved (Opsional, hapus visible kalau mau bisa print draft)
                    ->visible(fn (OutgoingLetter $record) => $record->status === 'approved'),
                // 1. Tombol Edit (Selalu muncul)
                Tables\Actions\EditAction::make(),

                // 2. Tombol AJUKAN (Draft -> Pending)
                // Hanya muncul kalau status masih DRAFT
                Tables\Actions\Action::make('submit')
                    ->label('Ajukan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (OutgoingLetter $record) => $record->status === 'draft')
                    ->action(function (OutgoingLetter $record) {
                        $record->update(['status' => 'pending_approval']);
                    }),

                // 3. Tombol APPROVE (Pending -> Approved)
                // Hanya muncul kalau status PENDING
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (OutgoingLetter $record) => $record->status === 'pending_approval')
                    ->action(function (OutgoingLetter $record) {
                        // Update jadi Approved & isi tanggal
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                        ]);
                        // Note: Observer akan otomatis jalan bikin Nomor Surat
                    }),

                // 4. Tombol REJECT (Pending -> Rejected)
                // Hanya muncul kalau status PENDING
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (OutgoingLetter $record) => $record->status === 'pending_approval')
                    ->form([
                        // Form pop-up alasan penolakan
                        Forms\Components\Textarea::make('note')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (OutgoingLetter $record, array $data) {
                        $record->update(['status' => 'rejected']);
                    }),
            ])
            // ----------------------------------------------
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
            'index' => Pages\ListOutgoingLetters::route('/'),
            'create' => Pages\CreateOutgoingLetter::route('/create'),
            'edit' => Pages\EditOutgoingLetter::route('/{record}/edit'),
        ];
    }
}