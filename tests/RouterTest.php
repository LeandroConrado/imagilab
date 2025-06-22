<?php

use PHPUnit\Framework\TestCase;
use Core\Router;

class DummyController
{
    public static bool $called = false;

    public function hello(): void
    {
        self::$called = true;
    }
}

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        DummyController::$called = false;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/hello';
    }

    public function testDispatchRoutesToController(): void
    {
        $router = new Router();
        $router->add('GET', '/hello', [DummyController::class, 'hello']);
        $router->dispatch();
        $this->assertTrue(DummyController::$called);
    }
}
