<?php

namespace App;

use Stringable;

class Node implements Stringable
{
    public function __construct(
        public Token $type,
        public string|int|array $value
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%s: %s', $this->type->name, $this->value);
    }

    public static function getTerminator(): self
    {
        return new Node(Token::TERMINATOR, Token::TERMINATOR->value);
    }

    public static function getArray(): self
    {
        return new Node(Token::ARRAY, Token::ARRAY->value);
    }
}