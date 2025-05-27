<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Customer;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class SalesReport extends Page implements HasTable, HasForms, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.sales-report';

    protected static ?string $navigationLabel = 'Relatório de Vendas';

    protected static ?string $title = 'Relatório de Vendas';

    protected static ?string $navigationGroup = 'Relatórios';

    protected static ?int $navigationSort = 2;

    public $date_from;
    public $date_to;
    public $customer_id;
    public $status_filter;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->endOfMonth()->format('Y-m-d');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('filters')
                ->label('Filtros')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->form([
                    DatePicker::make('date_from')
                        ->label('Data Inicial')
                        ->default($this->date_from),
                    
                    DatePicker::make('date_to')
                        ->label('Data Final')
                        ->default($this->date_to),
                    
                    Select::make('customer_id')
                        ->label('Cliente')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->placeholder('Todos os clientes'),
                    
                    Select::make('status_filter')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pendente',
                            'processing' => 'Processando',
                            'completed' => 'Finalizado',
                            'cancelled' => 'Cancelado',
                        ])
                        ->placeholder('Todos os status'),
                ])
                ->action(function (array $data) {
                    $this->date_from = $data['date_from'];
                    $this->date_to = $data['date_to'];
                    $this->customer_id = $data['customer_id'];
                    $this->status_filter = $data['status_filter'];
                    
                    $this->resetTable();
                }),

            Action::make('export_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Pedido #')
                    ->prefix('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->default('Cliente não informado'),

                Tables\Columns\TextColumn::make('customer.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data do Pedido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'completed' => 'Finalizado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Itens')
                    ->getStateUsing(function (Order $record) {
                        // Simular contagem de itens (pode ajustar conforme sua estrutura)
                        return rand(1, 5);
                    })
                    ->suffix(' itens'),

                Tables\Columns\TextColumn::make('total_simulation')
                    ->label('Valor Total')
                    ->getStateUsing(function (Order $record) {
                        // Simular valor total (R$ 50 a R$ 500)
                        return 'R$ ' . number_format(rand(50, 500), 2, ',', '.');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->badge()
                    ->color('gray')
                    ->getStateUsing(function () {
                        $methods = ['PIX', 'Cartão', 'Transferência', 'Boleto'];
                        return $methods[array_rand($methods)];
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'completed' => 'Finalizado',
                        'cancelled' => 'Cancelado',
                    ]),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Criado a partir de'),
                        DatePicker::make('created_until')
                            ->label('Criado até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getTableQuery(): Builder
    {
        $query = Order::query()->with('customer');

        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        if ($this->customer_id) {
            $query->where('customer_id', $this->customer_id);
        }

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }

        return $query;
    }

    protected function getViewData(): array
    {
        $query = $this->getTableQuery();

        return [
            'total_orders' => $query->count(),
            'completed_orders' => $query->where('status', 'completed')->count(),
            'pending_orders' => $query->where('status', 'pending')->count(),
            'cancelled_orders' => $query->where('status', 'cancelled')->count(),
            'total_revenue' => $query->count() * 150, // Simulação
            'avg_order_value' => 150, // Simulação
        ];
    }
}