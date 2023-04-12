<?php

namespace Core;

use Sunder\DI\Dependency;

class DI extends \Sunder\DI\DI
{
    public function set(string $id, mixed $data)
    {
        $this->dependencies[$id] = new Dependency($this, $data);
    }
}