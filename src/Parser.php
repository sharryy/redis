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

            if ($char == ':') {
                $this->tokens[] = $this->parseIntegers($data, $length);
                $this->tokens[] = Node::getTerminator();
            } elseif ($char == '+') {
                $this->tokens[] = $this->parseSimpleString($data, $length);
                $this->tokens[] = Node::getTerminator();
            } elseif ($char == '$') {
                $this->tokens[] = $this->parseBulkStrings($data, $length);
                $this->tokens[] = Node::getTerminator();
            } elseif ($char == '*') {
                $this->parseArrays($data, $length);
            }

            $this->position++;
        }

        return $this->tokens;
    }

    private function parseArrays(string $data, int $length): void
    {
        $this->tokens[] = Node::getArray();

        $len = '';
        $digit = $data[++$this->position];

        while (is_numeric($digit)) {
            $len .= $digit;
            $digit = $data[++$this->position];
        }

        if ($this->isTerminator($data, $this->position)) {
            $this->position += 4;
        }

        while ($this->position < $length) {
            $char = $data[$this->position];

            match ($char) {
                ':' => $this->parseIntegers($data, $length),
                '+' => $this->parseSimpleString($data, $length),
                '$' => $this->parseBulkStrings($data, $length),
                default => throw new RuntimeException('Invalid character')
            };
        }
    }

    private function parseIntegers(string $data, int $length): ?Node
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
                $this->position += 4;
                return new Node(Token::INTEGER, (int) $integer);
            }
        }

        return null;
    }

    private function parseSimpleString(string $data, int $length): ?Node
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

                    $this->position += 4;
                    return new Node(Token::SIMPLE_STRING, $string);
                    break;
                }
            }
        }

        return null;
    }

    private function parseBulkStrings(string $data, int $length): ?Node
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

        $this->position = $this->position + $stringLength + 4;

        return new Node(Token::BULK_STRING, $string);
    }

    private function isTerminator(string $data, int $index): bool
    {
        $terminator = mb_substr($data, $index, 4, 'UTF-8');

        return $terminator === '\\r\\n';
    }
}