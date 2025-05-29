<?php

namespace App\Filament\Resources\SalesReportResource\Pages;

use App\Filament\Resources\SalesReportResource;
use App\Models\Order;
use App\Models\Customer;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class CustomerSalesReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = SalesReportResource::class;

    protected static string $view = 'filament.pages.reports.customer-sales-report';

    public ?array $data = [];
    public $dateFrom;
    public $dateTo;
    public $selectedCustomer = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Total Pedidos')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total Vendas')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_ticket')
                    ->label('Ticket Médio')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_order')
                    ->label('Último Pedido')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('region')
                    ->label('Região')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        // Extrair região do JSON addresses
                        $addresses = $record->customer->addresses ?? [];
                        if (is_string($addresses)) {
                            $addresses = json_decode($addresses, true) ?? [];
                        }

                        // Procurar por cidade/estado nos endereços
                        foreach ($addresses as $address) {
                            if (isset($address['city']) && isset($address['state'])) {
                                return $address['city'] . ' - ' . $address['state'];
                            }
                        }

                        return 'Não informado';
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('period')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Data Inicial')
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Data Final')
                            ->default(now()),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->where('created_at', '>=', $date))
                            ->when($data['date_to'], fn ($q, $date) => $q->where('created_at', '<=', $date));
                    }),

                Tables\Filters\SelectFilter::make('min_orders')
                    ->label('Mínimo de Pedidos')
                    ->options([
                        '1' => '1 ou mais',
                        '5' => '5 ou mais',
                        '10' => '10 ou mais',
                        '20' => '20 ou mais',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value']) {
                            return $query->having('total_orders', '>=', $state['value']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Ver Detalhes')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn ($record) => 'Detalhes de ' . $record->customer->name)
                    ->modalContent(fn ($record) => $this->getCustomerDetailsView($record))
                    ->modalWidth('5xl'),
            ])
            ->defaultSort('total_sales', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getTableQuery()
    {
        $dateFrom = $this->dateFrom ? Carbon::parse($this->dateFrom) : now()->startOfMonth();
        $dateTo = $this->dateTo ? Carbon::parse($this->dateTo) : now();

        return Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('
                customer_id,
                COUNT(*) as total_orders,
                SUM(total) as total_sales,
                AVG(total) as average_ticket,
                MAX(created_at) as last_order
            ')
            ->with('customer')
            ->groupBy('customer_id');
    }

    protected function getCustomerDetailsView($record): View
    {
        $orders = Order::where('customer_id', $record->customer_id)
            ->latest()
            ->limit(10)
            ->get();

        return view('filament.modals.customer-sales-details', [
            'customer' => $record->customer,
            'orders' => $orders,
            'stats' => [
                'total_orders' => $record->total_orders,
                'total_sales' => $record->total_sales,
                'average_ticket' => $record->average_ticket,
            ]
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_excel')
                ->label('📊 Exportar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(fn () => $this->exportExcel()),

            Actions\Action::make('back')
                ->label('← Voltar')
                ->url(fn () => static::getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function exportExcel(): void
    {
        $this->dispatch('notify', 'Exportação em desenvolvimento...');
    }

    public function getTitle(): string
    {
        return 'Relatório de Vendas por Cliente';
    }
}
