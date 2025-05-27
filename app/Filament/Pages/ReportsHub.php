<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Customer;
use App\Models\AccountsPayable;
use App\Models\Product;

class ReportsHub extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.reports-hub';

    protected static ?string $navigationLabel = 'Central de Relatórios';

    protected static ?string $title = 'Central de Relatórios';

    protected static ?string $navigationGroup = 'Relatórios';

    protected static ?int $navigationSort = 0;

    public function getReportCards(): array
    {
        return [
            // Relatórios Financeiros
            [
                'title' => 'Contas a Pagar',
                'description' => 'Relatório detalhado das contas a pagar com filtros avançados',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'danger',
                'route' => 'filament.admin.pages.accounts-payable-report',
                'stats' => [
                    'total' => AccountsPayable::count(),
                    'pending' => AccountsPayable::where('status', 'pending')->count(),
                    'overdue' => AccountsPayable::where('status', 'overdue')->count(),
                ],
                'category' => 'Financeiro'
            ],
            
            [
                'title' => 'Fluxo de Caixa',
                'description' => 'Análise de entradas e saídas financeiras por período',
                'icon' => 'heroicon-o-chart-bar',
                'color' => 'success',
                'route' => '#', // Para implementar
                'stats' => [
                    'revenue' => 'R$ 45.000',
                    'expenses' => 'R$ 22.000',
                    'profit' => 'R$ 23.000',
                ],
                'category' => 'Financeiro'
            ],

            // Relatórios de Vendas
            [
                'title' => 'Vendas por Período',
                'description' => 'Relatório completo de vendas com análise temporal',
                'icon' => 'heroicon-o-shopping-cart',
                'color' => 'primary',
                'route' => 'filament.admin.pages.sales-report',
                'stats' => [
                    'total' => Order::count(),
                    'completed' => Order::where('status', 'completed')->count(),
                    'this_month' => Order::whereMonth('created_at', now()->month)->count(),
                ],
                'category' => 'Vendas'
            ],
            
            [
                'title' => 'Produtos Mais Vendidos',
                'description' => 'Ranking dos produtos com melhor performance',
                'icon' => 'heroicon-o-trophy',
                'color' => 'warning',
                'route' => '#', // Para implementar
                'stats' => [
                    'products' => Product::count(),
                    'categories' => '5',
                    'bestseller' => 'Produto A',
                ],
                'category' => 'Vendas'
            ],

            // Relatórios de Clientes
            [
                'title' => 'Análise de Clientes',
                'description' => 'Relatório detalhado da base de clientes',
                'icon' => 'heroicon-o-users',
                'color' => 'info',
                'route' => '#', // Para implementar
                'stats' => [
                    'total' => Customer::count(),
                    'new_today' => Customer::whereDate('created_at', today())->count(),
                    'active' => Customer::count(),
                ],
                'category' => 'Clientes'
            ],
            
            [
                'title' => 'Comportamento de Compra',
                'description' => 'Análise de padrões de comportamento dos clientes',
                'icon' => 'heroicon-o-chart-pie',
                'color' => 'purple',
                'route' => '#', // Para implementar
                'stats' => [
                    'avg_ticket' => 'R$ 150',
                    'frequency' => '2.3x/mês',
                    'retention' => '85%',
                ],
                'category' => 'Clientes'
            ],

            // Relatórios Operacionais
            [
                'title' => 'Controle de Estoque',
                'description' => 'Relatório de produtos, movimentação e estoque baixo',
                'icon' => 'heroicon-o-cube',
                'color' => 'gray',
                'route' => '#', // Para implementar
                'stats' => [
                    'products' => Product::count(),
                    'low_stock' => '3',
                    'out_stock' => '0',
                ],
                'category' => 'Operacional'
            ],
            
            [
                'title' => 'Relatório Executivo',
                'description' => 'Visão geral executiva com principais indicadores',
                'icon' => 'heroicon-o-presentation-chart-line',
                'color' => 'indigo',
                'route' => '#', // Para implementar
                'stats' => [
                    'growth' => '+15%',
                    'profit_margin' => '23%',
                    'roi' => '18%',
                ],
                'category' => 'Executivo'
            ],
        ];
    }

    public function getGroupedReports(): array
    {
        $reports = $this->getReportCards();
        $grouped = [];

        foreach ($reports as $report) {
            $category = $report['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $report;
        }

        return $grouped;
    }
}