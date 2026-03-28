<?php

namespace App\Filament\Resources\AliasResource\Pages;

use App\Filament\Resources\AliasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAliases extends ListRecords
{
    protected static string $resource = AliasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
