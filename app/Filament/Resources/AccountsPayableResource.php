<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountsPayableResource\Pages;
use App\Models\AccountsPayable;
use App\Models\Supplier;
use App\Models\ExpenseCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Summarizers\Sum;

class AccountsPayableResource extends Resource
{
    protected static ?string $model = AccountsPayable::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Accounts Payables';

    protected static ?string $modelLabel = 'Conta a Pagar';

    protected static ?string $pluralModelLabel = 'Contas a Pagar';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações da Conta')
                    ->description('Dados principais da conta a pagar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('invoice_number')
                                    ->label('Número da Fatura')
                                    ->placeholder('NF-001/2025')
                                    ->maxLength(255),

                                Forms\Components\DatePicker::make('issue_date')
                                    ->label('Data de Emissão')
                                    ->required()
                                    ->default(now())
                                    ->native(false),

                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Data de Vencimento')
                                    ->required()
                                    ->after('issue_date')
                                    ->native(false),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->required()
                            ->placeholder('Descreva o motivo da conta a pagar...')
                            ->rows(2)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Valor Total')
                                    ->required()
                                    ->numeric()
                                    ->prefix('R$')
                                    ->step('0.01')
                                    ->minValue(0),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pendente',
                                        'overdue' => 'Em Atraso',
                                        'partial' => 'Parcial',
                                        'paid' => 'Pago',
                                        'cancelled' => 'Cancelado',
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Relacionamentos')
                    ->description('Fornecedor e categoria da despesa')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('supplier_id')
                                    ->label('Fornecedor')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nome')
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email(),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Telefone'),
                                    ]),

                                Forms\Components\Select::make('expense_category_id')
                                    ->label('Categoria de Despesa')
                                    ->relationship('expenseCategory', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nome')
                                            ->required(),
                                        Forms\Components\Textarea::make('description')
                                            ->label('Descrição'),
                                    ]),
                            ]),
                    ]),

                Section::make('Informações de Pagamento')
                    ->description('Dados relacionados ao pagamento')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('paid_amount')
                                    ->label('Valor Pago')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->step('0.01')
                                    ->default(0)
                                    ->minValue(0),

                                Forms\Components\DatePicker::make('payment_date')
                                    ->label('Data do Pagamento')
                                    ->native(false)
                                    ->visible(fn (Forms\Get $get) => $get('paid_amount') > 0),

                                Forms\Components\TextInput::make('payment_method')
                                    ->label('Método de Pagamento')
                                    ->placeholder('PIX, Transferência, etc.')
                                    ->visible(fn (Forms\Get $get) => $get('paid_amount') > 0),
                            ]),

                        Forms\Components\TextInput::make('reference')
                            ->label('Referência')
                            ->placeholder('Número do pedido, contrato, etc.')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->placeholder('Informações adicionais...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('N° Fatura')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Fornecedor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable()
                    ->summarize(Sum::make()->money('BRL')),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Valor Pago')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Restante')
                    ->money('BRL')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->amount - $record->paid_amount),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'danger' => 'overdue',
                        'info' => 'partial',
                        'success' => 'paid',
                        'secondary' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'overdue' => 'Em Atraso',
                        'partial' => 'Parcial',
                        'paid' => 'Pago',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->status === 'paid') return 'success';
                        return $record->due_date < now() ? 'danger' : 'primary';
                    }),

                Tables\Columns\TextColumn::make('expenseCategory.name')
                    ->label('Categoria')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'overdue' => 'Em Atraso',
                        'partial' => 'Parcial',
                        'paid' => 'Pago',
                        'cancelled' => 'Cancelado',
                    ]),

                SelectFilter::make('supplier')
                    ->label('Fornecedor')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('due_date')
                    ->label('Vencimento')
                    ->form([
                        Forms\Components\DatePicker::make('due_from')
                            ->label('Vence a partir de'),
                        Forms\Components\DatePicker::make('due_until')
                            ->label('Vence até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Action::make('register_payment')
                    ->label('Registrar Pagamento')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'overdue', 'partial']))
                    ->form([
                        Forms\Components\TextInput::make('payment_amount')
                            ->label('Valor do Pagamento')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->step('0.01')
                            ->minValue(0.01),
                        
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
                    ->action(function ($record, array $data) {
                        $record->registerPayment(
                            $data['payment_amount'],
                            $data['payment_date'],
                            $data['payment_method']
                        );

                        if (!empty($data['payment_notes'])) {
                            $record->notes = $record->notes . "\n\nPagamento: " . $data['payment_notes'];
                            $record->save();
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Pagamento registrado com sucesso!')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountsPayables::route('/'),
            'create' => Pages\CreateAccountsPayable::route('/create'),
            'view' => Pages\ViewAccountsPayable::route('/{record}'),
            'edit' => Pages\EditAccountsPayable::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['pending', 'overdue'])->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $overdue = static::getModel()::where('status', 'overdue')->count();
        return $overdue > 0 ? 'danger' : 'warning';
    }
}