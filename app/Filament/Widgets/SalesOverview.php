<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        try {
            // Pedidos hoje (usando count em vez de sum)
            $ordersToday = Order::whereDate('created_at', today())->count();

            // Pedidos este mês
            $ordersThisMonth = Order::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count();

            // Pedidos pendentes (se o campo status existir)
            $pendingOrders = 0;
            try {
                $pendingOrders = Order::where('status', 'pending')->count();
            } catch (\Exception $e) {
                // Se não tem campo status, ignora
                $pendingOrders = Order::count(); // Total de pedidos
            }

            // Novos clientes hoje
            $newCustomersToday = Customer::whereDate('created_at', today())->count();

            // Total de produtos
            $totalProducts = Product::count();

        } catch (\Exception $e) {
            // Se der erro, usa valores padrão
            $ordersToday = 0;
            $ordersThisMonth = 0;
            $pendingOrders = 0;
            $newCustomersToday = 0;
            $totalProducts = 0;
        }

        return [
            Stat::make('Pedidos Hoje', $ordersToday)
                ->description('Pedidos criados hoje')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Pedidos do Mês', $ordersThisMonth)
                ->description('Total de pedidos este mês')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Total de Pedidos', $pendingOrders)
                ->description('Todos os pedidos no sistema')
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('warning'),

            Stat::make('Novos Clientes', $newCustomersToday)
                ->description('Cadastros hoje')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),

            Stat::make('Total de Produtos', $totalProducts)
                ->description('Produtos cadastrados')
                ->descriptionIcon('heroicon-m-cube')
                ->color('gray'),
        ];
    }
}