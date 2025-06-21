<?php

namespace Core;

use App\Models\Configuracao;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
    protected Environment $twig;

    public function __construct()
    {
        $themeName = $_ENV['APP_THEME'] ?? 'ios_premium';
        $loader = new FilesystemLoader([
            __DIR__ . '/../app/Views/themes/' . $themeName,
            __DIR__ . '/../app/Views/'
        ]);

        $isDev = ($_ENV['APP_ENV'] ?? 'production') === 'development';
        $this->twig = new Environment($loader, [
            'cache' => $isDev ? false : __DIR__ . '/../storage/cache/twig',
            'debug' => $isDev,
        ]);
        
        if ($isDev) {
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        }

        // Disponibiliza dados do usuário e configurações para TODAS as views
        $this->twig->addGlobal('auth_user', Auth::user());
        $this->twig->addGlobal('settings', (new Configuracao())->getAll());
    }

    protected function render(string $template, array $data = []): void
    {
        echo $this->twig->render($template, $data);
    }
    
    protected function renderToString(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }
}
