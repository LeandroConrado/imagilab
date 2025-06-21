<?php

namespace App\Controllers\Admin;

use Core\Controller;

class ConfiguracoesController extends Controller
{
    public function index(): void
    {
        $this->render('admin/configuracoes/index.twig', [
            'titulo' => 'Configurações do Sistema'
        ]);
    }
}