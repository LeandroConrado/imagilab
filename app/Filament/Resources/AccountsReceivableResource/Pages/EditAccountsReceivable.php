<?php

namespace App\Filament\Resources\AccountsReceivableResource\Pages;

use App\Filament\Resources\AccountsReceivableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountsReceivable extends EditRecord
{
    protected static string $resource = AccountsReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
