<?php

namespace App\Filament\Widgets;

use App\Models\AccountsPayable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountsPayableOverview extends BaseWidget
{
    protected static ?int $sort = 7; // Define a ordem do widget

    protected function getStats(): array
    {
        try {
            // Valor total das contas pagas
            $totalValuePaid = AccountsPayable::where('status', 'paid')->sum('amount') ?? 0;
            
            // Quantidade de contas pagas
            $countPaid = AccountsPayable::where('status', 'paid')->count();
            
            // Valor médio das contas pagas
            $averageValue = $countPaid > 0 ? $totalValuePaid / $countPaid : 0;
            
        } catch (\Exception $e) {
            $totalValuePaid = 0;
            $countPaid = 0;
            $averageValue = 0;
        }

        return [
            Stat::make('Valor Total Pago', 'R$ ' . number_format($totalValuePaid, 2, ',', '.'))
                ->description($countPaid . ' contas finalizadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Ticket Médio', 'R$ ' . number_format($averageValue, 2, ',', '.'))
                ->description('Valor médio por conta')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
    
}