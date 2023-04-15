<?php

namespace App\Model;

use Attribute;

#[Attribute]
readonly class ModelMeta
{
    public function __construct(
        public string $primaryKey,

        public string $table
    )
    {
    }
}