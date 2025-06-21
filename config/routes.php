<?php

// Todos os nossos controllers
use App\Controllers\Admin\CategoriaController;
use App\Controllers\Admin\ClienteController;
use App\Controllers\Admin\CondicaoPagamentoController;
use App\Controllers\Admin\ConfiguracoesController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\FinanceiroController;
use App\Controllers\Admin\FormaPagamentoController;
use App\Controllers\Admin\FornecedorController;
use App\Controllers\Admin\LancamentoController;
use App\Controllers\Admin\PedidoController;
use App\Controllers\Admin\PedidoStatusController;
use App\Controllers\Admin\ProdutoController;
use App\Controllers\Admin\TipoFreteController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\BannerController;
use App\Controllers\Admin\ConfiguracoesGeraisController;
use App\Controllers\Admin\RelatorioController;
use App\Controllers\AuthController;
use App\Controllers\Site\HomeController;
use App\Middleware\AuthMiddleware; // Nosso Middleware simples

/**
 * @param \Core\Router $router
 */
return function(\Core\Router $router) {
    // --- Rotas Públicas (NÃO precisam de login) ---
    $router->add('GET', '/login', [AuthController::class, 'showLoginForm']);
    $router->add('POST', '/login', [AuthController::class, 'login']);
    $router->add('GET', '/logout', [AuthController::class, 'logout']);
    $router->add('GET', '/', [HomeController::class, 'index']);


    // --- GRUPO DE ROTAS DO ADMIN (TODAS PROTEGIDAS) ---
    // O middleware é aplicado a todas as rotas dentro deste grupo.
    $router->group('/admin', function($router) {
        
        $router->add('GET', '/', [DashboardController::class, 'index']);

        // Usuários
        $router->add('GET', '/usuarios', [UserController::class, 'index']);
        $router->add('GET', '/usuarios/create', [UserController::class, 'create']);
        $router->add('POST', '/usuarios', [UserController::class, 'store']);
        $router->add('GET', '/usuarios/{id}/edit', [UserController::class, 'edit']);
        $router->add('POST', '/usuarios/{id}', [UserController::class, 'update']);
        $router->add('POST', '/usuarios/{id}/delete', [UserController::class, 'destroy']);
        
        // Categorias
        $router->add('GET', '/categorias', [CategoriaController::class, 'index']);
        $router->add('GET', '/categorias/create', [CategoriaController::class, 'create']);
        $router->add('POST', '/categorias', [CategoriaController::class, 'store']);
        $router->add('GET', '/categorias/{id}/edit', [CategoriaController::class, 'edit']);
        $router->add('POST', '/categorias/{id}', [CategoriaController::class, 'update']);
        $router->add('POST', '/categorias/{id}/delete', [CategoriaController::class, 'destroy']);

        // Fornecedores
        $router->add('GET', '/fornecedores', [FornecedorController::class, 'index']);
        $router->add('GET', '/fornecedores/create', [FornecedorController::class, 'create']);
        $router->add('POST', '/fornecedores', [FornecedorController::class, 'store']);
        $router->add('GET', '/fornecedores/{id}/edit', [FornecedorController::class, 'edit']);
        $router->add('POST', '/fornecedores/{id}', [FornecedorController::class, 'update']);
        $router->add('POST', '/fornecedores/{id}/delete', [FornecedorController::class, 'destroy']);
        
        // Produtos
        $router->add('GET', '/produtos', [ProdutoController::class, 'index']);
        $router->add('GET', '/produtos/create', [ProdutoController::class, 'create']);
        $router->add('POST', '/produtos', [ProdutoController::class, 'store']);
        $router->add('GET', '/produtos/{id}/edit', [ProdutoController::class, 'edit']);
        $router->add('POST', '/produtos/{id}', [ProdutoController::class, 'update']);
        $router->add('POST', '/produtos/{id}/delete', [ProdutoController::class, 'destroy']);

        // Clientes
        $router->add('GET', '/clientes', [ClienteController::class, 'index']);
        $router->add('GET', '/clientes/create', [ClienteController::class, 'create']);
        $router->add('POST', '/clientes', [ClienteController::class, 'store']);
        $router->add('GET', '/clientes/{id}/edit', [ClienteController::class, 'edit']);
        $router->add('POST', '/clientes/{id}', [ClienteController::class, 'update']);
        $router->add('POST', '/clientes/{id}/delete', [ClienteController::class, 'destroy']);

        // Pedidos
        $router->add('GET', '/pedidos', [PedidoController::class, 'index']);
        $router->add('GET', '/pedidos/create', [PedidoController::class, 'create']);
        $router->add('POST', '/pedidos', [PedidoController::class, 'store']);
        $router->add('GET', '/pedidos/{id}', [PedidoController::class, 'show']);
        $router->add('GET', '/pedidos/{id}/edit', [PedidoController::class, 'edit']);
        $router->add('POST', '/pedidos/{id}', [PedidoController::class, 'update']);
        $router->add('POST', '/pedidos/{id}/send-email', [PedidoController::class, 'sendEmail']);

        // Financeiro
        $router->add('GET', '/financeiro', [FinanceiroController::class, 'index']);
        $router->add('GET', '/financeiro/lancamentos', [LancamentoController::class, 'index']);
        $router->add('GET', '/financeiro/lancamentos/create', [LancamentoController::class, 'create']);
        $router->add('POST', '/financeiro/lancamentos', [LancamentoController::class, 'store']);
        $router->add('GET', '/financeiro/lancamentos/{id}/edit', [LancamentoController::class, 'edit']);
        $router->add('POST', '/financeiro/lancamentos/{id}', [LancamentoController::class, 'update']);
        $router->add('POST', '/financeiro/lancamentos/{id}/delete', [LancamentoController::class, 'destroy']);

        // Banners
        $router->add('GET', '/banners', [BannerController::class, 'index']);
        $router->add('GET', '/banners/create', [BannerController::class, 'create']);
        $router->add('POST', '/banners', [BannerController::class, 'store']);
        $router->add('GET', '/banners/{id}/edit', [BannerController::class, 'edit']);
        $router->add('POST', '/banners/{id}', [BannerController::class, 'update']);
        $router->add('POST', '/banners/{id}/delete', [BannerController::class, 'destroy']);

        // Relatórios
        $router->add('GET', '/relatorios', [RelatorioController::class, 'index']);
        $router->add('GET', '/relatorios/vendas', [RelatorioController::class, 'vendas']);
        
        // Configurações
        $router->add('GET', '/configuracoes', [ConfiguracoesController::class, 'index']);
        $router->add('GET', '/configuracoes/formas-pagamento', [FormaPagamentoController::class, 'index']);
        $router->add('GET', '/configuracoes/formas-pagamento/create', [FormaPagamentoController::class, 'create']);
        $router->add('POST', '/configuracoes/formas-pagamento', [FormaPagamentoController::class, 'store']);
        $router->add('GET', '/configuracoes/formas-pagamento/{id}/edit', [FormaPagamentoController::class, 'edit']);
        $router->add('POST', '/configuracoes/formas-pagamento/{id}', [FormaPagamentoController::class, 'update']);
        $router->add('POST', '/configuracoes/formas-pagamento/{id}/delete', [FormaPagamentoController::class, 'destroy']);
        $router->add('GET', '/configuracoes/pedido-status', [PedidoStatusController::class, 'index']);
        $router->add('GET', '/configuracoes/pedido-status/create', [PedidoStatusController::class, 'create']);
        $router->add('POST', '/configuracoes/pedido-status', [PedidoStatusController::class, 'store']);
        $router->add('GET', '/configuracoes/pedido-status/{id}/edit', [PedidoStatusController::class, 'edit']);
        $router->add('POST', '/configuracoes/pedido-status/{id}', [PedidoStatusController::class, 'update']);
        $router->add('POST', '/configuracoes/pedido-status/{id}/delete', [PedidoStatusController::class, 'destroy']);
        $router->add('GET', '/configuracoes/tipos-frete', [TipoFreteController::class, 'index']);
        $router->add('GET', '/configuracoes/tipos-frete/create', [TipoFreteController::class, 'create']);
        $router->add('POST', '/configuracoes/tipos-frete', [TipoFreteController::class, 'store']);
        $router->add('GET', '/configuracoes/tipos-frete/{id}/edit', [TipoFreteController::class, 'edit']);
        $router->add('POST', '/configuracoes/tipos-frete/{id}', [TipoFreteController::class, 'update']);
        $router->add('POST', '/configuracoes/tipos-frete/{id}/delete', [TipoFreteController::class, 'destroy']);
        $router->add('GET', '/configuracoes/condicoes-pagamento', [CondicaoPagamentoController::class, 'index']);
        $router->add('GET', '/configuracoes/condicoes-pagamento/create', [CondicaoPagamentoController::class, 'create']);
        $router->add('POST', '/configuracoes/condicoes-pagamento', [CondicaoPagamentoController::class, 'store']);
        $router->add('GET', '/configuracoes/condicoes-pagamento/{id}/edit', [CondicaoPagamentoController::class, 'edit']);
        $router->add('POST', '/configuracoes/condicoes-pagamento/{id}', [CondicaoPagamentoController::class, 'update']);
        $router->add('POST', '/configuracoes/condicoes-pagamento/{id}/delete', [CondicaoPagamentoController::class, 'destroy']);

        // Dentro do grupo /admin
        $router->add('GET', '/configuracoes/gerais', [ConfiguracoesGeraisController::class, 'index']);
        $router->add('POST', '/configuracoes/gerais', [ConfiguracoesGeraisController::class, 'update']);

    }, [AuthMiddleware::class]); // Middleware aplicado a todas as rotas do grupo /admin
};
