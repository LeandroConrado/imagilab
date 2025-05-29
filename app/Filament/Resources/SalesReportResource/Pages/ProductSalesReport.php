<?php

namespace App\Filament\Resources\SalesReportResource\Pages;

use App\Filament\Resources\SalesReportResource;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Carbon;

class ProductSalesReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = SalesReportResource::class;

    protected static string $view = 'filament.pages.reports.product-sales-report';

    public $dateFrom;
    public $dateTo;

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
                Tables\Columns\ImageColumn::make('product.images')
                    ->label('Imagem')
                    ->circular()
                    ->defaultImageUrl('/images/no-product.png')
                    ->getStateUsing(fn ($record) =>
                    is_array($record->product->images) && count($record->product->images) > 0
                        ? $record->product->images[0]
                        : null
                    ),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable(),

                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Categoria')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Qtd Vendida')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total Vendas')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_price')
                    ->label('Preço Médio')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.stock_quantity')
                    ->label('Estoque Atual')
                    ->numeric()
                    ->alignCenter()
                    ->color(fn ($record) =>
                    $record->product->stock_quantity <= $record->product->min_stock
                        ? 'danger'
                        : ($record->product->stock_quantity <= $record->product->min_stock * 2 ? 'warning' : 'success')
                    ),

                Tables\Columns\TextColumn::make('margin')
                    ->label('Margem %')
                    ->getStateUsing(function ($record) {
                        if ($record->product->cost_price && $record->average_price) {
                            $margin = (($record->average_price - $record->product->cost_price) / $record->average_price) * 100;
                            return number_format($margin, 1) . '%';
                        }
                        return 'N/A';
                    })
                    ->color(fn ($record) => {
                        if (!$record->product->cost_price || !$record->average_price) return 'gray';
                        $margin = (($record->average_price - $record->product->cost_price) / $record->average_price) * 100;
                        return $margin >= 30 ? 'success' : ($margin >= 15 ? 'warning' : 'danger');
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
                    ->when($data['date_from'], function ($q, $date) {
                        $q->whereHas('order', fn ($orderQuery) =>
                        $orderQuery->where('created_at', '>=', $date)
                        );
                    })
                    ->when($data['date_to'], function ($q, $date) {
                        $q->whereHas('order', fn ($orderQuery) =>
                        $orderQuery->where('created_at', '<=', $date)
                        );
                    });
            }),

        Tables\Filters\SelectFilter::make('category')
            ->label('Categoria')
            ->relationship('product.category', 'name')
            ->searchable()
            ->preload(),

        Tables\Filters\SelectFilter::make('stock_status')
            ->label('Status do Estoque')
            ->options([
                'low' => 'Estoque Baixo',
                'normal' => 'Estoque Normal',
                'high' => 'Estoque Alto',
            ])
            ->query(function ($query, $state) {
                switch ($state['value']) {
                    case 'low':
                        return $query->whereHas('product', function ($q) {
                            $q->whereRaw('stock_quantity <= min_stock');
                        });
                    case 'normal':
                        return $query->whereHas('product', function ($q) {
                            $q->whereRaw('stock_quantity > min_stock AND stock_quantity <= min_stock * 3');
                        });
                    case 'high':
                        return $query->whereHas('product', function ($q) {
                            $q->whereRaw('stock_quantity > min_stock * 3');
                        });
                }
            }),
    ])
        ->actions([
            Tables\Actions\Action::make('view_details')
                ->label('Detalhes')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading(fn ($record) => 'Detalhes: ' . $record->product->name)
                ->modalContent(fn ($record) => $this->getProductDetailsView($record))
                ->modalWidth('4xl'),
        ])
        ->defaultSort('total_sales', 'desc')
        ->paginated([10, 25, 50]);
    }

    protected function getTableQuery()
    {
        $dateFrom = $this->dateFrom ? Carbon::parse($this->dateFrom) : now()->startOfMonth();
        $dateTo = $this->dateTo ? Carbon::parse($this->dateTo) : now();

        return OrderItem::whereHas('order', function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        })
            ->selectRaw('
                product_id,
                SUM(quantity) as total_quantity,
                SUM(total) as total_sales,
                AVG(price) as average_price,
                COUNT(DISTINCT order_id) as orders_count
            ')
            ->with(['product.category'])
            ->groupBy('product_id');
    }

    protected function getProductDetailsView($record)
    {
        // Dados de vendas por mês para o gráfico
        $monthlySales = OrderItem::where('product_id', $record->product_id)
            ->whereHas('order', function ($query) {
                $query->where('created_at', '>=', now()->subMonths(12));
            })
            ->selectRaw('
                YEAR(orders.created_at) as year,
                MONTH(orders.created_at) as month,
                SUM(order_items.quantity) as quantity,
                SUM(order_items.total) as sales
            ')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->groupByRaw('YEAR(orders.created_at), MONTH(orders.created_at)')
            ->orderByRaw('YEAR(orders.created_at), MONTH(orders.created_at)')
            ->get();

        return view('filament.modals.product-sales-details', [
            'product' => $record->product,
            'stats' => [
                'total_quantity' => $record->total_quantity,
                'total_sales' => $record->total_sales,
                'average_price' => $record->average_price,
                'orders_count' => $record->orders_count,
            ],
            'monthly_sales' => $monthlySales,
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
        return 'Relatório de Vendas por Produto';
    }
}
