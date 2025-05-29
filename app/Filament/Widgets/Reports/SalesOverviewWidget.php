<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SalesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Período atual (mês atual)
        $currentMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Mês anterior
        $previousMonth = now()->subMonth()->startOfMonth();
        $endOfPreviousMonth = now()->subMonth()->endOfMonth();

        // Vendas do mês atual
        $currentSales = Order::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->sum('total');

        // Vendas do mês anterior
        $previousSales = Order::whereBetween('created_at', [$previousMonth, $endOfPreviousMonth])
            ->sum('total');

        // Crescimento
        $salesGrowth = $previousSales > 0 ? (($currentSales - $previousSales) / $previousSales) * 100 : 0;

        // Pedidos hoje
        $ordersToday = Order::whereDate('created_at', today())->count();

        // Pedidos pendentes
        $pendingOrders = Order::where('status', 'pending')->count();

        // Produto mais vendido do mês
        $topProduct = OrderItem::whereHas('order', function ($query) use ($currentMonth, $endOfMonth) {
            $query->whereBetween('created_at', [$currentMonth, $endOfMonth]);
        })
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->first();

        return [
            Stat::make('💰 Vendas do Mês', 'R$ ' . number_format($currentSales, 2, ',', '.'))
                ->description($this->getGrowthDescription($salesGrowth))
                ->descriptionIcon($salesGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('📦 Pedidos Hoje', $ordersToday)
                ->description('Pedidos realizados hoje')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('⏳ Pedidos Pendentes', $pendingOrders)
                ->description('Aguardando processamento')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('🏆 Produto Top', $topProduct ? $topProduct->product->name ?? 'N/A' : 'N/A')
                ->description($topProduct ? $topProduct->total_quantity . ' vendidos' : 'Nenhuma venda este mês')
                ->descriptionIcon('heroicon-m-star')
                ->color('purple'),
        ];
    }

    private function getGrowthDescription($growth): string
    {
        if ($growth > 0) {
            return '↗ ' . number_format($growth, 1) . '% vs mês anterior';
        } elseif ($growth < 0) {
            return '↘ ' . number_format(abs($growth), 1) . '% vs mês anterior';
        } else {
            return 'Sem alteração vs mês anterior';
        }
    }
}
