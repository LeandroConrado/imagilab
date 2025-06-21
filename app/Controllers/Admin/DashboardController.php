<?php
namespace App\Controllers\Admin;

use App\Models\Cliente;
use App\Models\Lancamento;
use App\Models\Pedido;
use App\Models\Produto;
use Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        // Prepara dados para o gráfico
        $vendasDiarias = (new Pedido())->getVendasDiarias(15); // Últimos 15 dias
        $graficoLabels = json_encode(array_column($vendasDiarias, 'dia'));
        $graficoData = json_encode(array_column($vendasDiarias, 'total'));

        $this->render('admin/dashboard/index.twig', [
            'titulo' => 'Dashboard',
            'metricas' => [
                'financeiro' => (new Lancamento())->getDashboardMetrics(),
                'total_clientes' => (new Cliente())->countAll(),
                'total_produtos' => (new Produto())->countAll(),
                'total_pedidos' => (new Pedido())->countAll(),
                'faturamento_total' => (new Pedido())->getFaturamentoTotal(),
            ],
            'mais_vendidos' => (new Produto())->getMaisVendidos(5),
            'grafico_labels' => $graficoLabels,
            'grafico_data' => $graficoData
        ]);
    }
}
