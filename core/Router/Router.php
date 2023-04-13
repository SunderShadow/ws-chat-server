<?php

namespace Core\Router;

class Router
{
    /** @var callable */
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
    public function resolve(Request $request): Response
    {
        $action = $this->routes[$request->action] ?? $this->onUndefined;
        $response =  $this->resolver->resolve($action, $request);

        if (!$response) {
            $response = new Response([], '');
        }
        return $response;
    }
}