<?php

namespace App\Filament\Resources\LetterTypeResource\Pages;

use App\Filament\Resources\LetterTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditLetterType extends EditRecord
{
    protected static string $resource = LetterTypeResource::class;

    public function mount(int | string $record): void
    {
        if ($record == 1) {
            Notification::make()
                ->warning()
                ->title('Akses Ditolak')
                ->body('Jenis surat ini dilindungi sistem dan tidak dapat diedit.')
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->id !== 1),
        ];
    }
}