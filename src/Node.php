<?php

namespace App;

use Stringable;

class Node implements Stringable
{
    public function __construct(
        public Token $type,
        public string|int $value
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%s: %s', $this->type->name, $this->value);
    }
}