<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutgoingLetterResource\Pages;
use App\Models\OutgoingLetter;
use App\Models\OutgoingDisposition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Database\Eloquent\Builder;

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
        return $form
            ->schema([
                // --- SECTION 0: ALERT REVISI (Kondisional) ---
                Forms\Components\Section::make('Disposisi Pimpinan')
                    ->schema([
                        Forms\Components\Placeholder::make('revisi_note')
                            ->label('Instruksi / Catatan:')
                            ->content(fn ($record) => $record?->dispositions()->latest()->first()?->instruction ?? '-Tidak ada catatan-')
                            ->extraAttributes(['class' => 'text-danger-600 font-bold text-lg']),
                        
                        Forms\Components\Placeholder::make('disposition_sender')
                            ->label('Dari:')
                            ->content(fn ($record) => $record?->dispositions()->latest()->first()?->sender->name ?? '-'),
                    ])
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->iconColor('danger') 
                    ->visible(fn ($record) => $record?->status === 'revision_needed')
                    ->columns(2),

                // --- SECTION 1: DATA UTAMA ---
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
                            ->placeholder('Otomatis diisi saat status Disetujui')
                            // LOGIC PENTING:
                            // Disabled jika: Record baru (null), Draft, Pending, Revisi, ATAU Completed.
                            // Enabled HANYA jika: Status 'approved' (agar Admin bisa edit manual).
                            ->disabled(fn ($record) => 
                                !$record || 
                                in_array($record->status, ['draft', 'revision_needed', 'pending_approval']) || 
                                $record->status === 'completed'
                            )
                            // Wajib dehydrated agar nilai tersimpan saat status 'approved'
                            ->dehydrated() 
                            ->maxLength(255),

                        Forms\Components\RichEditor::make('content_data')
                            ->label('Isi Surat')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    // Matikan Section ini jika surat sudah FINAL
                    ->disabled(fn ($record) => $record?->status === 'completed'),

                // --- SECTION 2: STATUS ---
                Forms\Components\Section::make('Status & Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Surat')
                            ->options([
                                'draft' => 'Draft (Konsep)',
                                'pending_approval' => 'Menunggu Persetujuan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'completed' => 'Final / Selesai',
                            ])
                            ->default('draft')
                            ->required()
                            // Status tidak boleh diedit manual lewat form, harus lewat tombol aksi
                            ->disabled(),
                            
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pemohon')
                            ->default(Auth::id())
                            ->disabled(), // Pemohon tidak bisa diubah
                    ])
                    ->columns(1)
                    ->disabled(fn ($record) => $record?->status === 'completed'),

                // --- SECTION 3: LAMPIRAN ---
                Forms\Components\Section::make('Lampiran Dokumen')
                    ->description('Upload file draft surat atau dokumen pendukung lainnya.')
                    ->headerActions([
                        Forms\Components\Actions\Action::make('lihat_lampiran')
                            ->label('Lihat / Cetak Lampiran')
                            ->icon('heroicon-o-printer')
                            ->color('info') // Biru biar kelihatan
                            // Cuma muncul kalau ada record & ada lampiran
                            ->visible(fn ($record) => $record && $record->attachments->count() > 0)
                            ->modalHeading('Pilih Lampiran untuk Dicetak')
                            ->modalSubmitAction(false) // Gak butuh tombol submit
                            ->modalCancelAction(fn ($action) => $action->label('Tutup'))
                            ->form([
                                // Tampilkan daftar link file menggunakan Placeholder
                                Forms\Components\Placeholder::make('list_lampiran')
                                    ->label('')
                                    ->content(function ($record) {
                                        $html = '<ul class="list-disc pl-4 space-y-2">';
                                        foreach ($record->attachments as $attachment) {
                                            $url = \Illuminate\Support\Facades\Storage::url($attachment->file_path);
                                            $nama = $attachment->filename;
                                            $html .= "<li>
                                                <a href='{$url}' target='_blank' class='text-primary-600 hover:underline font-bold flex items-center gap-2'>
                                                    <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14'></path></svg>
                                                    {$nama}
                                                </a>
                                            </li>";
                                        }
                                        $html .= '</ul>';
                                        return new \Illuminate\Support\HtmlString($html);
                                    }),
                            ]),
                    ])
                    ->schema([
                        Repeater::make('attachments')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('filename')
                                    ->label('Nama File')
                                    ->required(),

                                FileUpload::make('file_path')
                                    ->label('File Lampiran')
                                    ->disk('public')
                                    ->directory('lampiran-surat-keluar')
                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                    ->maxSize(10240) // 10MB
                                    ->required()
                                    ->openable() // Menambahkan tombol "Mata" (Buka di tab baru untuk Print)
                                    ->downloadable() // Menambahkan tombol "Download" (Panah ke bawah)
                                    ->previewable(true), // Menampilkan preview kecil (thumbnail)
                                
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Lampiran'),
                    ])
                    ->disabled(fn ($record) => $record?->status === 'completed'),

            ]); 
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
                        'revision_needed' => 'danger', // Merah
                        'completed' => 'primary', // Biru
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'pending_approval' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'revision_needed' => 'Perlu Revisi',
                        'completed' => 'Final',
                        default => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // 1. Tombol Edit & Delete (Hilang jika sudah Final)
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status !== 'completed'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->status !== 'completed'),

                // 2. GRUP TOMBOL PROSES (Titik Tiga)
                Tables\Actions\ActionGroup::make([
                    
                    // A. TOMBOL PRINT (Hanya jika SUDAH FINAL)
                    Tables\Actions\Action::make('print')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn (OutgoingLetter $record) => route('outgoing.print', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'completed'),

                    // B. TOMBOL FINALISASI (Hanya jika APPROVED)
                    Tables\Actions\Action::make('finalize')
                        ->label('Finalisasi Surat')
                        ->icon('heroicon-o-lock-closed')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Finalisasi Surat?')
                        ->modalDescription('Pastikan nomor surat sudah diisi. Setelah ini surat tidak bisa diedit lagi.')
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'approved')
                        ->action(function (OutgoingLetter $record) {
                            if (empty($record->letter_number)) {
                                Notification::make()->danger()->title('Gagal: Nomor Surat Kosong!')->send();
                                return;
                            }
                            $record->update(['status' => 'completed']);
                            Notification::make()->success()->title('Surat Final & Terkunci')->send();
                        }),

                    // C. TOMBOL AJUKAN (Draft -> Pending)
                    Tables\Actions\Action::make('submit')
                        ->label('Ajukan Verifikasi')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('blue')
                        ->requiresConfirmation()
                        ->visible(fn (OutgoingLetter $record) => in_array($record->status, ['draft', 'revision_needed']))
                        ->action(fn (OutgoingLetter $record) => $record->update(['status' => 'pending_approval'])),

                    // D. TOMBOL SETUJUI (Pending -> Approved)
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'pending_approval')
                        ->action(function (OutgoingLetter $record) {
                            $record->update([
                                'status' => 'approved',
                                'approved_at' => now(),
                            ]);
                        }),

                    // E. TOMBOL REVISI (Pending -> Revision)
                    Tables\Actions\Action::make('request_revision')
                        ->label('Minta Revisi')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->visible(fn (OutgoingLetter $record) => $record->status === 'pending_approval')
                        ->form([
                            Forms\Components\Textarea::make('instruction')->label('Catatan Revisi')->required(),
                        ])
                        ->action(function (OutgoingLetter $record, array $data) {
                            OutgoingDisposition::create([
                                'outgoing_letter_id' => $record->id,
                                'user_id' => Auth::id(),
                                'instruction' => $data['instruction'],
                            ]);
                            $record->update(['status' => 'revision_needed']);
                        }),

                ])
                ->label('Proses')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('info')
                ->tooltip('Menu Persetujuan'),
            ]); 
    }

    public static function getRelations(): array
    {
        return [];
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