<?php

namespace Core;

class Request
{
    /**
     * Retorna o método da requisição HTTP (GET, POST, etc.).
     * @return string
     */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Retorna a URI da requisição.
     * @return string
     */
    public static function uri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Retorna um valor de $_GET.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Retorna um valor de $_POST.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function post(string $key, $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Retorna todos os dados de $_POST.
     * @return array
     */
    public static function allPost(): array
    {
        return $_POST;
    }

    /**
     * Retorna todos os dados de $_GET.
     * @return array
     */
    public static function allGet(): array
    {
        return $_GET;
    }
}