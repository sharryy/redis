<?php

namespace App;

use Exception;
use RuntimeException;

class Parser
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
        $length = mb_strlen($data, 'UTF-8');

        if ($length === 0) {
            return [];
        }

        while ($this->position <= $length) {
            $char = mb_substr($data, $this->position, 1, 'UTF-8');

            match ($char) {
                ':' => $this->parseIntegers($data, $length),
                '+' => $this->parseSimpleString($data, $length),
                '$' => $this->parseBulkStrings($data, $length),
                default => throw new RuntimeException('Invalid character')
            };

            $this->position++;
        }

        return $this->tokens;
    }

    private function parseIntegers(string $data, int $length): void
    {
        $integer = $data[++$this->position];

        if (! is_numeric((int) $integer)) {
            throw new RuntimeException('Invalid integer');
        }

        while ($this->position < $length) {
            $integer = '';
            $char = $data[$this->position];

            while (is_numeric($char)) {
                $integer .= $char;
                $char = $data[++$this->position];
            }

            if ($this->isTerminator($data, $this->position)) {
                $this->tokens[] = new Node(Token::INTEGER, (int) $integer);
                $this->tokens[] = Node::getTerminator();
                $this->position += 4;
                break;
            }
        }
    }

    private function parseSimpleString(string $data, int $length): void
    {
        $string = '';

        while (++$this->position < $length) {
            $char = $data[$this->position];
            if ($char !== '\\') {
                if (! is_string($char)) {
                    throw new RuntimeException('Invalid string');
                }
                $string .= $char;
            } else {
                if ($this->isTerminator($data, $this->position)) {
                    if ($this->position >= $length) {
                        throw new RuntimeException('Invalid string');
                    }

                    $this->tokens[] = new Node(Token::SIMPLE_STRING, $string);
                    $this->tokens[] = Node::getTerminator();
                    $this->position += 4;
                    break;
                }
            }
        }
    }

    private function parseBulkStrings(string $data, int $length): void
    {
        $stringLength = '';

        while (++$this->position < $length) {
            $digit = $data[$this->position];

            if ($digit !== '\\') {

                if (! is_numeric($digit)) {
                    throw new RuntimeException("Invalid length");
                }

                $stringLength .= $digit;
            } else {
                if ($this->isTerminator($data, $this->position)) {
                    $this->position += 4;
                    break;
                }
            }
        }

        $stringLength = (int) $stringLength;

        $string = mb_substr($data, $this->position, $stringLength, 'UTF-8');

        if (! $this->isTerminator($data, $this->position + $stringLength)) {
            throw new RuntimeException("Invalid string");
        }

        $this->tokens[] = new Node(Token::BULK_STRING, $string);
        $this->tokens[] = Node::getTerminator();
        $this->position = $this->position + $stringLength + 4;
    }

    private function isTerminator(string $data, int $index): bool
    {
        $terminator = mb_substr($data, $index, 4, 'UTF-8');

        return $terminator === '\\r\\n';
    }
}