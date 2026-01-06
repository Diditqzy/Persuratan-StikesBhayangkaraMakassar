<?php

namespace App\Filament\Resources\SignerResource\Pages;

use App\Filament\Resources\SignerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSigner extends EditRecord
{
    protected static string $resource = SignerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
