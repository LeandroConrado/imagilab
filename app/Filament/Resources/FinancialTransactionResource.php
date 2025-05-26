<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialTransactionResource\Pages;
use App\Models\FinancialTransaction;
use App\Models\AccountsReceivable;
use App\Models\AccountsPayable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FinancialTransactionResource extends Resource
{
    protected static ?string $model = FinancialTransaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 12;
    protected static ?string $navigationGroup = 'Financial';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Information')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'receivable' => 'Receivable Payment',
                                'payable' => 'Payable Payment',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Limpa o campo quando o tipo muda
                                $set('transactionable_id', null);

                                // Define automaticamente o transactionable_type
                                if ($state === 'receivable') {
                                    $set('transactionable_type', 'App\\Models\\AccountsReceivable');
                                } elseif ($state === 'payable') {
                                    $set('transactionable_type', 'App\\Models\\AccountsPayable');
                                }
                            }),

                        Forms\Components\Select::make('transactionable_id')
                            ->label('Related Account')
                            ->required()
                            ->options(function (Forms\Get $get) {
                                $type = $get('type');
                                if ($type === 'receivable') {
                                    return \App\Models\AccountsReceivable::with('customer')
                                        ->get()
                                        ->mapWithKeys(function ($item) {
                                            return [$item->id => $item->invoice_number . ' - ' . $item->customer->name];
                                        });
                                } elseif ($type === 'payable') {
                                    return \App\Models\AccountsPayable::with('supplier')
                                        ->get()
                                        ->mapWithKeys(function ($item) {
                                            return [$item->id => $item->invoice_number . ' - ' . ($item->supplier->name ?? 'No Supplier')];
                                        });
                                }
                                return [];
                            })
                            ->searchable()
                            ->placeholder('First select the transaction type'),

                        Forms\Components\Hidden::make('transactionable_type'),
                    ])->columns(2),

                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'credit_card' => 'Credit Card',
                                'debit_card' => 'Debit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'pix' => 'PIX',
                                'check' => 'Check',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('reference_number')
                            ->placeholder('Transaction reference or receipt number'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(4)
                            ->placeholder('Additional notes about this transaction'),

                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'receivable' => 'success',
                        'payable' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'receivable' => 'Received',
                        'payable' => 'Paid',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'pix' => 'PIX',
                        'check' => 'Check',
                    }),

                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->placeholder('No reference'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Processed by')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'receivable' => 'Receivable',
                        'payable' => 'Payable',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'pix' => 'PIX',
                        'check' => 'Check',
                    ]),

                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('transaction_from'),
                        Forms\Components\DatePicker::make('transaction_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['transaction_from'],
                                fn ($query, $date) => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['transaction_until'],
                                fn ($query, $date) => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    public static function getNavigationBadge(): ?string
    {
        // Mostra transações do dia atual
        return static::getModel()::whereDate('transaction_date', today())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialTransactions::route('/'),
            'create' => Pages\CreateFinancialTransaction::route('/create'),
            'edit' => Pages\EditFinancialTransaction::route('/{record}/edit'),
        ];
    }
}
