<?php

namespace App\Filament\Widgets;

use App\Models\AccountsPayable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaidAccountsWidget extends BaseWidget
{
    protected static ?int $sort = 2; // Primeiro da lista

    protected function getStats(): array
    {
        try {
            // Valor total pago
            $totalValuePaid = AccountsPayable::where('status', 'paid')->sum('amount') ?? 0;
            
            // Valor pago este mês
            $paidThisMonth = AccountsPayable::where('status', 'paid')
                                          ->whereMonth('payment_date', now()->month)
                                          ->whereYear('payment_date', now()->year)
                                          ->sum('amount') ?? 0;
            
            // Valor pago hoje
            $paidToday = AccountsPayable::where('status', 'paid')
                                       ->whereDate('payment_date', today())
                                       ->sum('amount') ?? 0;
            
            // Quantidade de contas pagas
            $countPaid = AccountsPayable::where('status', 'paid')->count();
            
        } catch (\Exception $e) {
            $totalValuePaid = 0;
            $paidThisMonth = 0;
            $paidToday = 0;
            $countPaid = 0;
        }

        return [
            Stat::make('💰 Total Pago', 'R$ ' . number_format($totalValuePaid, 2, ',', '.'))
                ->description($countPaid . ' contas finalizadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('📅 Pago Este Mês', 'R$ ' . number_format($paidThisMonth, 2, ',', '.'))
                ->description('Pagamentos do mês')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('🌟 Pago Hoje', 'R$ ' . number_format($paidToday, 2, ',', '.'))
                ->description('Pagamentos de hoje')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary'),
        ];
    }
}