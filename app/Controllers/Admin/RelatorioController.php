<?php

namespace App\Controllers\Admin;

use App\Models\Pedido;
use Core\Controller;

class RelatorioController extends Controller
{
    /**
     * Exibe a página principal de Relatórios com o menu de opções.
     */
    public function index(): void
    {
        $this->render('admin/relatorios/index.twig', [
            'titulo' => 'Central de Relatórios'
        ]);
    }

    /**
     * Gera e exibe o relatório de vendas por período.
     */
    public function vendas(): void
    {
        // Define as datas padrão (últimos 30 dias) se não houver filtro
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $dadosRelatorio = (new Pedido())->gerarRelatorioVendas($dataInicio, $dataFim);

        $this->render('admin/relatorios/vendas.twig', [
            'titulo' => 'Relatório de Vendas',
            'relatorio' => $dadosRelatorio,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim
        ]);
    }
}
