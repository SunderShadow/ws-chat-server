<?php

namespace Core\Router;

use Core\DI;

readonly class RouteResolver
{
    public function __construct(
        private DI $dependenciesContainer
    )
    {}

    /**
     * @param callable|class-string $action
     * @param Request $request
     * @return Response|null
     * @throws \ReflectionException
     */
    public function resolve(callable|string $action, Request $request): Response|null
    {
        if (is_string($action)) {
            $action = $this->dependenciesContainer->makeInstance($action);
        }

        $this->dependenciesContainer->set(Request::class, $request);

        return $this->dependenciesContainer->call($action);
    }
}