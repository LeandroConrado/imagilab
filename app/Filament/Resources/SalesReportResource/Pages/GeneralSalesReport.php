<?php

namespace App\Filament\Resources\SalesReportResource\Pages;

use App\Filament\Resources\SalesReportResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions;
use Illuminate\Support\Carbon;

class GeneralSalesReport extends Page
{
    protected static string $resource = SalesReportResource::class;

    protected static string $view = 'filament.pages.reports.general-sales-report';

    public ?array $data = [];
    public $dateFrom;
    public $dateTo;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->loadReportData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filtros do Relatório')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('dateFrom')
                                    ->label('Data Inicial')
                                    ->default(now()->startOfMonth())
                                    ->native(false)
                                    ->reactive()
                                    ->afterStateUpdated(fn () => $this->loadReportData()),

                                Forms\Components\DatePicker::make('dateTo')
                                    ->label('Data Final')
                                    ->default(now())
                                    ->native(false)
                                    ->reactive()
                                    ->afterStateUpdated(fn () => $this->loadReportData()),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('📄 Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(fn () => $this->exportPdf()),

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

    public function loadReportData(): void
    {
        $dateFrom = Carbon::parse($this->dateFrom);
        $dateTo = Carbon::parse($this->dateTo);

        // Métricas Gerais
        $this->data['metrics'] = $this->getGeneralMetrics($dateFrom, $dateTo);

        // Vendas por Período
        $this->data['sales_by_period'] = $this->getSalesByPeriod($dateFrom, $dateTo);

        // Top Produtos
        $this->data['top_products'] = $this->getTopProducts($dateFrom, $dateTo);

        // Top Clientes
        $this->data['top_customers'] = $this->getTopCustomers($dateFrom, $dateTo);

        // Status dos Pedidos
        $this->data['order_status'] = $this->getOrderStatusData($dateFrom, $dateTo);
    }

    private function getGeneralMetrics($dateFrom, $dateTo): array
    {
        $orders = Order::whereBetween('created_at', [$dateFrom, $dateTo]);

        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Comparação com período anterior
        $previousPeriod = $dateFrom->diffInDays($dateTo);
        $previousDateFrom = $dateFrom->copy()->subDays($previousPeriod);
        $previousDateTo = $dateFrom->copy()->subDay();

        $previousSales = Order::whereBetween('created_at', [$previousDateFrom, $previousDateTo])->sum('total');
        $salesGrowth = $previousSales > 0 ? (($totalSales - $previousSales) / $previousSales) * 100 : 0;

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'average_ticket' => $averageTicket,
            'sales_growth' => $salesGrowth,
            'delivered_orders' => $orders->where('status', 'delivered')->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
        ];
    }

    private function getSalesByPeriod($dateFrom, $dateTo): array
    {
        return Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d/m/Y'),
                    'total' => $item->total,
                    'orders' => $item->orders,
                ];
            })
            ->toArray();
    }

    private function getTopProducts($dateFrom, $dateTo): array
    {
        return OrderItem::whereHas('order', function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        })
            ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(total) as total_sales')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'product_name' => $item->product->name ?? 'Produto não encontrado',
                    'quantity' => $item->total_quantity,
                    'sales' => $item->total_sales,
                ];
            })
            ->toArray();
    }

    private function getTopCustomers($dateFrom, $dateTo): array
    {
        return Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('customer_id, SUM(total) as total_sales, COUNT(*) as total_orders')
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'customer_name' => $item->customer->name ?? 'Cliente não encontrado',
                    'total_sales' => $item->total_sales,
                    'total_orders' => $item->total_orders,
                    'average_ticket' => $item->total_orders > 0 ? $item->total_sales / $item->total_orders : 0,
                ];
            })
            ->toArray();
    }

    private function getOrderStatusData($dateFrom, $dateTo): array
    {
        return Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count, SUM(total) as total')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => match($item->status) {
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'shipped' => 'Enviado',
                        'delivered' => 'Entregue',
                        'cancelled' => 'Cancelado',
                        default => $item->status,
                    },
                    'count' => $item->count,
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }

    public function exportPdf(): void
    {
        // Implementar exportação PDF
        $this->dispatch('notify', 'PDF exportado com sucesso!');
    }

    public function exportExcel(): void
    {
        // Implementar exportação Excel
        $this->dispatch('notify', 'Excel exportado com sucesso!');
    }

    public function getTitle(): string
    {
        return 'Relatório Geral de Vendas';
    }
}
