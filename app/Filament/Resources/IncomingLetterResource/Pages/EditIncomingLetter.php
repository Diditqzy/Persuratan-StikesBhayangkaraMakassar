<?php

namespace App\Filament\Resources\IncomingLetterResource\Pages;

use App\Filament\Resources\IncomingLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditIncomingLetter extends EditRecord
{
    protected static string $resource = IncomingLetterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (Auth::user()->role === 'pimpinan') {
            $data['status'] = 'dispositioned';
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => Auth::user()->role === 'admin'),
        ];
    }

    protected function getFormActions(): array
    {
        if (Auth::user()->role === 'pimpinan') {
            return [
                $this->getSaveFormAction()
                    ->label('Kirim Disposisi')
                    ->color('success') 
                    ->icon('heroicon-m-paper-airplane')
                    ->submit('save'), 
                
                $this->getCancelFormAction(), 
            ];
        }

        return parent::getFormActions();
    }
}