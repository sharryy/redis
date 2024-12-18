<?php

namespace App;

use Exception;

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
        $length = mb_strlen($data, 'UTF-8');

        if ($length === 0) {
            return [];
        }

        $i = 0;

        while ($i <= $length) {
            $char = mb_substr($data, $i, 1, 'UTF-8');

            if ($char === ':') {
                $integer = $data[++$i];

                if (! is_int((int) $integer)) {
                    throw new Exception('Invalid integer');
                }

                while (++$i < $length) {
                    $char = $data[$i];
                    if ($char === '\\') {
                        if ($this->isTerminator($data, $i)) {
                            $this->tokens[] = new Node(Token::INTEGER,
                                (int) mb_substr($data, $this->position + 1, $i - $this->position - 1, 'UTF-8'));
                            $this->tokens[] = Node::getTerminator();
                            $this->position = $i + 4;
                            break;
                        }
                    }
                }
            } elseif ($char === '+') {
                $string = '';
                while (++$i < $length) {
                    $char = $data[$i];
                    if ($char !== '\\') {
                        if (! is_string($char)) {
                            throw new Exception('Invalid string');
                        }
                        $string .= $char;
                    } else {
                        if ($this->isTerminator($data, $i)) {
                            if ($i >= $length) {
                                throw new Exception('Invalid string');
                            }

                            $this->tokens[] = new Node(Token::SIMPLE_STRING, $string);
                            $this->tokens[] = Node::getTerminator();
                            $this->position = $i + 4;
                            break;
                        }
                    }
                }
            } elseif ($char === '$') {
                $stringLength = '';

                while (++$i < $length) {
                    $digit = $data[$i];

                    if ($digit !== '\\') {

                        if (! is_numeric($digit)) {
                            throw new Exception("Invalid length");
                        }

                        $stringLength .= $digit;
                    } else {
                        if ($this->isTerminator($data, $i)) {
                            $this->position = $i + 4;
                            break;
                        }
                    }
                }

                $stringLength = (int) $stringLength;

                if (! $this->isTerminator($data, $i)) {
                    throw new Exception("Invalid string");
                }

                $i += 4;

                $string = mb_substr($data, $i, $stringLength, 'UTF-8');

                if (! $this->isTerminator($data, $i + $stringLength)) {
                    throw new Exception("Invalid string");
                }

                $this->tokens[] = new Node(Token::BULK_STRING, $string);
                $this->tokens[] = Node::getTerminator();
                $this->position = $i + $stringLength + 4;
            } elseif ($char === '*') {
                $arrayLength = $data[++$i];

                if (! is_numeric($arrayLength)) {
                    throw new Exception("Invalid array length");
                }

                $i++;

                if (! $this->isTerminator($data, $i)) {
                    throw new Exception("Invalid array");
                }

                $this->tokens[] = Node::getArray();

                $i += 4;

                $notation = $data[$i];

                if ($notation === '$') {
                    // TODO: Implement bulk string array
                }
            }
            $i++;
        }

        return $this->tokens;
    }

    private function isTerminator(string $data, int $index): bool
    {
        $terminator = mb_substr($data, $index, 4, 'UTF-8');

        return $terminator === '\\r\\n';
    }
}