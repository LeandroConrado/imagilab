<?php

namespace App\Filament\Resources\AccountsPayableResource\Pages;

use App\Filament\Resources\AccountsPayableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountsPayables extends ListRecords
{
    protected static string $resource = AccountsPayableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
