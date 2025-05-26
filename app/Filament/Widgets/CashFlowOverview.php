<?php

namespace App\Filament\Widgets;

use App\Models\AccountsPayable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashFlowOverview extends BaseWidget
{
    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        $pending = 0;
        $overdue = 0;
        $paid = 0;

        try {
            $pending = AccountsPayable::where('status', 'pending')->count();
            $overdue = AccountsPayable::where('status', 'overdue')->count();
            $paid = AccountsPayable::where('status', 'paid')->count();
        } catch (\Exception $e) {
            // Se der erro, mantém os valores padrão
        }

        return [
            Stat::make('Contas Pendentes', $pending)
                ->description('Aguardando pagamento')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Contas em Atraso', $overdue)
                ->description('Vencidas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Contas Pagas', $paid)
                ->description('Finalizadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}