<?php

namespace App\Filament\Resources\SignerResource\Pages;

use App\Filament\Resources\SignerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSigners extends ListRecords
{
    protected static string $resource = SignerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
