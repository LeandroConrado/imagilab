<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class MonthlySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Pedidos Mensais';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '12'; // Últimos 12 meses por padrão

    protected function getFilters(): ?array
    {
        return [
            '6' => 'Últimos 6 meses',
            '12' => 'Últimos 12 meses',
            '24' => 'Últimos 24 meses',
        ];
    }

    protected function getData(): array
    {
        $months = (int) $this->filter;
        
        // Preparar dados dos últimos X meses
        $data = [];
        $labels = [];
        
        try {
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthYear = $date->format('M/Y');
                
                // Contar pedidos em vez de somar valores
                $orderCount = Order::whereMonth('created_at', $date->month)
                                 ->whereYear('created_at', $date->year)
                                 ->count();
                
                $labels[] = $monthYear;
                $data[] = $orderCount;
            }
        } catch (\Exception $e) {
            // Se der erro, retorna dados vazios
            $labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
            $data = [0, 0, 0, 0, 0, 0];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Número de Pedidos',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}