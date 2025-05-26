<?php

namespace App\Filament\Resources\AccountsPayableResource\Pages;

use App\Filament\Resources\AccountsPayableResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Notifications\Notification;

class ViewAccountsPayable extends ViewRecord
{
    protected static string $resource = AccountsPayableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('register_payment')
                ->label('Registrar Pagamento')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['pending', 'overdue', 'partial']))
                ->form([
                    Forms\Components\TextInput::make('payment_amount')
                        ->label('Valor do Pagamento')
                        ->required()
                        ->numeric()
                        ->prefix('R$')
                        ->step('0.01')
                        ->minValue(0.01)
                        ->maxValue(fn () => $this->record->remaining_amount),
                    
                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Data do Pagamento')
                        ->required()
                        ->default(now())
                        ->native(false),
                    
                    Forms\Components\TextInput::make('payment_method')
                        ->label('Método de Pagamento')
                        ->required()
                        ->placeholder('PIX, Transferência, etc.'),
                    
                    Forms\Components\Textarea::make('payment_notes')
                        ->label('Observações do Pagamento')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->registerPayment(
                        $data['payment_amount'],
                        $data['payment_date'],
                        $data['payment_method']
                    );

                    if (!empty($data['payment_notes'])) {
                        $this->record->notes = $this->record->notes . "\n\nPagamento: " . $data['payment_notes'];
                        $this->record->save();
                    }

                    Notification::make()
                        ->title('Pagamento registrado com sucesso!')
                        ->success()
                        ->send();
                        
                    return redirect()->to(static::getUrl(['record' => $this->record]));
                }),

            Actions\EditAction::make(),
            
            Actions\DeleteAction::make(),
        ];
    }
}