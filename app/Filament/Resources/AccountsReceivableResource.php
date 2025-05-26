<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountsReceivableResource\Pages;
use App\Models\AccountsReceivable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AccountsReceivableResource extends Resource
{
    protected static ?string $model = AccountsReceivable::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationGroup = 'Financial';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Information')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->unique(AccountsReceivable::class, 'invoice_number', ignoreRecord: true)
                            ->default(fn () => 'REC-' . strtoupper(uniqid())),

                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select related order (optional)'),
                    ])->columns(3),

                Forms\Components\Section::make('Financial Details')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $amountPaid = $get('amount_paid') ?? 0;
                                $set('amount_remaining', $state - $amountPaid);
                            }),

                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $amount = $get('amount') ?? 0;
                                $set('amount_remaining', $amount - $state);
                            }),

                        Forms\Components\TextInput::make('amount_remaining')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(3),

                Forms\Components\Section::make('Dates & Status')
                    ->schema([
                        Forms\Components\DatePicker::make('issue_date')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('due_date')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'credit_card' => 'Credit Card',
                                'debit_card' => 'Debit Card',
                                'bank_transfer' => 'Bank Transfer',
                                'pix' => 'PIX',
                                'check' => 'Check',
                            ])
                            ->placeholder('Select payment method'),
                    ])->columns(2),

                Forms\Components\Section::make('Installment Information')
                    ->schema([
                        Forms\Components\TextInput::make('installment_number')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),

                        Forms\Components\TextInput::make('total_installments')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3),

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Additional notes or observations'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_remaining')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'partial' => 'info',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('due_date', 'asc');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['pending', 'overdue'])->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $overdueCount = static::getModel()::where('status', 'overdue')->count();
        return $overdueCount > 0 ? 'danger' : 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountsReceivables::route('/'),
            'create' => Pages\CreateAccountsReceivable::route('/create'),
            'edit' => Pages\EditAccountsReceivable::route('/{record}/edit'),
        ];
    }
}
