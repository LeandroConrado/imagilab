<?php
// 1. Ativa a exibição de erros do PHP imediatamente.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// 2. Garante que a sessão seja iniciada apenas uma vez
if (!session_id()) {
    session_start();
}

// Carrega o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 3. Carrega as variáveis de ambiente do .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    die("Erro ao carregar o arquivo .env: " . $e->getMessage());
}

// 4. Configura o Whoops para erros em ambiente de desenvolvimento
if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') {
    if (class_exists('\\Whoops\\Run') && class_exists('\\Whoops\\Handler\\PrettyPageHandler')) {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    } else {
        die("Erro crítico: As classes do Whoops não foram encontradas. Por favor, execute 'composer install' e 'composer dump-autoload -o' na raiz do seu projeto.");
    }
} else {
    die('Ambiente de produção, Whoops não ativado. (APP_ENV não é development)');
}

// Inclui o namespace Core\Router
use Core\Router;

// 5. Instancia o roteador
try {
    $router = new Router();
} catch (\Exception $e) {
    die("Erro ao instanciar o Router: " . $e->getMessage());
}

// 6. Inclui as definições de rotas do arquivo de rotas
try {
    $routesDefinition = require __DIR__ . '/../config/routes.php';
    $routesDefinition($router);
    // die('Teste 6: Rotas carregadas!'); // <-- REMOVIDO AQUI
} catch (\Exception $e) {
    die("Erro ao carregar as rotas: " . $e->getMessage());
}

// 7. Despacha a requisição.
try {
    $router->dispatch();
} catch (\Exception $e) {
    die("Erro ao despachar a requisição: " . $e->getMessage());
}
?>