<?php

namespace Core\Router;

readonly class Request
{
    public function __construct(
        public int $fd,
        public array $data,
        public string $action
    )
    {
    }
}