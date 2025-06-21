<?php

namespace Core;

class Router
{
    private array $routes = [];
    private array $params = [];
    private array $groupMiddlewares = [];
    private string $groupPrefix = '';
    
    public function add(string $method, string $uri, array $controller): self
    {
        $uri = $this->groupPrefix . $uri;
        $uri = '/' . trim(preg_replace('/\/+/', '/', $uri), '/');
        $uri = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[^\/]+)', $uri);
        $uri = '#^' . $uri . '$#';

        $this->routes[] = [
            'uri' => $uri,
            'method' => strtoupper($method),
            'controller' => $controller,
            'middlewares' => $this->groupMiddlewares
        ];
        
        return $this;
    }

    public function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $previousPrefix = $this->groupPrefix;
        $this->groupPrefix = $previousPrefix . $prefix;
        $previousMiddlewares = $this->groupMiddlewares;
        $this->groupMiddlewares = array_merge($previousMiddlewares, $middlewares);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    public function dispatch(): void
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'])['path'];
        if (strlen($requestUri) > 1) {
            $requestUri = rtrim($requestUri, '/');
        }
        
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if (preg_match($route['uri'], $requestUri, $matches) && $route['method'] === $requestMethod) {
                foreach ($route['middlewares'] as $middlewareClass) {
                    (new $middlewareClass())->handle();
                }

                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }

                $controllerClass = $route['controller'][0];
                $method = $route['controller'][1];

                if (!class_exists($controllerClass)) { $this->abort(500, "Controller não encontrado: {$controllerClass}"); }
                $controller = new $controllerClass();
                if (!method_exists($controller, $method)) { $this->abort(500, "Método não encontrado no controller: {$method}"); }

                call_user_func_array([$controller, $method], $this->params);
                return;
            }
        }
        $this->abort(404);
    }
    
    /**
     * CORRIGIDO: Agora lança uma exceção que o Whoops pode capturar.
     */
    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        
        $finalMessage = $message ?: match($code) {
            404 => 'Página não encontrada',
            500 => 'Erro Interno do Servidor',
            default => 'Ocorreu um erro'
        };

        // Lança a exceção para o Whoops
        throw new \Exception($finalMessage, $code);
    }
}
