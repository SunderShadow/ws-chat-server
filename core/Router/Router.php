<?php

namespace Core\Router;

class Router
{
    private $onUndefined;

    public function __construct(
        private readonly array $routes,
        private readonly RouteResolver $resolver
    )
    {
    }

    /**
     * Register callback on undefined action
     * @param callable $cb
     * @return void
     */
    public function onUndefined(callable $cb): void
    {
        $this->onUndefined = $cb;
    }

    /**
     * @throws \ReflectionException
     */
    public function resolve(string $actionName): void
    {
        $action = $this->routes[$actionName] ?? $this->onUndefined;
        $request = new Request(0, [], $actionName);

        $response = $this->resolver->resolve($action, $request);

        if ($response) {
            var_dump($response);
            // TODO: implement response handle
        }
    }
}