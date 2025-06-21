<?php

namespace App\Controllers\Admin;

use App\Models\Lancamento;
use Core\Controller;

class FinanceiroController extends Controller
{
    public function index(): void
    {
        $this->render('admin/financeiro/dashboard.twig', [
            'titulo' => 'Dashboard Financeiro',
            'metricas' => (new Lancamento())->getDashboardMetrics()
        ]);
    }
}