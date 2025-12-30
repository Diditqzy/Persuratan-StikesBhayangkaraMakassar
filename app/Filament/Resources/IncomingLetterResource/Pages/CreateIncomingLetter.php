<?php

namespace App\Filament\Resources\IncomingLetterResource\Pages;

use App\Filament\Resources\IncomingLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateIncomingLetter extends CreateRecord
{
    protected static string $resource = IncomingLetterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Paksa isi kolom input_by_user_id dengan ID user yang sedang login
        $data['input_by_user_id'] = Auth::id();
        
        // Kembalikan data yang sudah lengkap ke sistem untuk disimpan
        return $data;
    }

    // Redirect balik ke tabel setelah simpan
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
