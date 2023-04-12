<?php

namespace Core\Router;

readonly class Response
{
    /**
     * @param array $to FDs
     * @param string $actionName
     * @param array $data
     */
    public function __construct(
        public array $to,
        public string $actionName,
        public array $data = []
    )
    {}
}