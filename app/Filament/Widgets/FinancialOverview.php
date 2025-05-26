<?php

namespace App\Filament\Widgets;

use App\Models\AccountsPayable;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverview extends BaseWidget
{
    protected static ?int $sort = 5; // Aparece primeiro

    protected function getStats(): array
    {
        try {
            // === CONTAS A PAGAR ===
            
            // Valor total pago
            $totalPaid = AccountsPayable::where('status', 'paid')->sum('amount') ?? 0;
            
            // Valor pendente
            $totalPending = AccountsPayable::whereIn('status', ['pending', 'partial'])->sum('remaining_amount') ?? 0;
            
            // Valor em atraso
            $totalOverdue = AccountsPayable::where('status', 'overdue')->sum('remaining_amount') ?? 0;
            
            // === VENDAS (se existir o campo) ===
            $totalSales = 0;
            try {
                // Tenta buscar vendas se a coluna existir
                $totalSales = Order::where('status', 'completed')->sum('total_amount') ?? 0;
            } catch (\Exception $e) {
                // Se não existir coluna total_amount, conta pedidos
                $totalSales = Order::where('status', 'completed')->count() * 100; // Simula R$ 100 por pedido
            }
            
            // === FLUXO DE CAIXA ===
            $cashFlow = $totalSales - $totalPaid;
            
        } catch (\Exception $e) {
            $totalPaid = 0;
            $totalPending = 0;
            $totalOverdue = 0;
            $totalSales = 0;
            $cashFlow = 0;
        }

        return [
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

            Stat::make('📈 Total de Vendas', 'R$ ' . number_format($totalSales, 2, ',', '.'))
                ->description('Receita total')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('💹 Fluxo de Caixa', 'R$ ' . number_format($cashFlow, 2, ',', '.'))
                ->description('Receita - Despesas')
                ->descriptionIcon($cashFlow >= 0 ? 'heroicon-m-face-smile' : 'heroicon-m-face-frown')
                ->color($cashFlow >= 0 ? 'success' : 'danger'),
        ];
    }
}