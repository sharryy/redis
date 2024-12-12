<?php

namespace App;

class Lexer
{
    /**
     * @var array<Token>
     */
    public array $tokens = [];

    /**
     * The current position of the lexer.
     */
    public int $position = 0;

    public function tokenize(string $data): array
    {
       // TODO: Implement the tokenize method
    }
}