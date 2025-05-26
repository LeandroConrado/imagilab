<?php

namespace App\Filament\Widgets;

use App\Models\AccountsPayable;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UnifiedDashboard extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // === CONTAS A PAGAR ===
        $totalPaid = 0;
        $totalPending = 0;
        $totalOverdue = 0;
        
        try {
            $totalPaid = AccountsPayable::where('status', 'paid')->sum('amount') ?? 0;
            $totalPending = AccountsPayable::whereIn('status', ['pending', 'partial'])->sum('remaining_amount') ?? 0;
            $totalOverdue = AccountsPayable::where('status', 'overdue')->sum('remaining_amount') ?? 0;
        } catch (\Exception $e) {
            // Valores padrão se der erro
        }

        // === PEDIDOS ===
        $totalOrders = 0;
        $ordersThisMonth = 0;
        
        try {
            $totalOrders = Order::count();
            $ordersThisMonth = Order::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count();
        } catch (\Exception $e) {
            // Valores padrão se der erro
        }

        // === CLIENTES ===
        $totalCustomers = 0;
        $newCustomersToday = 0;
        
        try {
            $totalCustomers = Customer::count();
            $newCustomersToday = Customer::whereDate('created_at', today())->count();
        } catch (\Exception $e) {
            // Valores padrão se der erro
        }

        // === PRODUTOS ===
        $totalProducts = 0;
        
        try {
            $totalProducts = Product::count();
        } catch (\Exception $e) {
            // Valores padrão se der erro
        }

        return [
            // LINHA 1 - FINANCEIRO
            Stat::make('💰 Total Pago', 'R$ ' . number_format($totalPaid, 2, ',', '.'))
                ->description('Contas finalizadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('⏳ Valor Pendente', 'R$ ' . number_format($totalPending, 2, ',', '.'))
                ->description('Aguardando pagamento')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('🚨 Valor em Atraso', 'R$ ' . number_format($totalOverdue, 2, ',', '.'))
                ->description('Contas vencidas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            // LINHA 2 - PEDIDOS
            Stat::make('📦 Total de Pedidos', number_format($totalOrders, 0, ',', '.'))
                ->description('Pedidos no sistema')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('📅 Pedidos do Mês', $ordersThisMonth)
                ->description('Pedidos este mês')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('👥 Total de Clientes', number_format($totalCustomers, 0, ',', '.'))
                ->description('Clientes cadastrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('purple'),

            // LINHA 3 - OPERACIONAL
            Stat::make('🌟 Novos Clientes', $newCustomersToday)
                ->description('Cadastros hoje')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('green'),

            Stat::make('📦 Total de Produtos', number_format($totalProducts, 0, ',', '.'))
                ->description('Produtos no catálogo')
                ->descriptionIcon('heroicon-m-cube')
                ->color('blue'),

            Stat::make('⚡ Sistema', 'Online')
                ->description('Status: Funcionando')
                ->descriptionIcon('heroicon-m-signal')
                ->color('gray'),
        ];
    }
}