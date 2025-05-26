<?php

namespace App\Filament\Resources\AccountsReceivableResource\Pages;

use App\Filament\Resources\AccountsReceivableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountsReceivables extends ListRecords
{
    protected static string $resource = AccountsReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
