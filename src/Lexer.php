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
                        $terminator = mb_substr($data, $i, 4, 'UTF-8');
                        if ($terminator === '\\r\\n') {
                            $this->tokens[] = new Node(Token::INTEGER,
                                (int) mb_substr($data, $this->position + 1, $i - $this->position - 1, 'UTF-8'));
                            $this->tokens[] = new Node(Token::TERMINATOR, $terminator);
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
                        $terminator = mb_substr($data, $i, 4, 'UTF-8');

                        if ($terminator === '\\r\\n') {

                            if ($i >= $length) {
                                throw new Exception('Invalid string');
                            }

                            $this->tokens[] = new Node(Token::SIMPLE_STRING, $string);
                            $this->tokens[] = new Node(Token::TERMINATOR, $terminator);
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
                        $terminator = mb_substr($data, $i, 4, 'UTF-8');

                        if ($terminator === '\\r\\n') {
                            $this->position = $i + 4;
                            break;
                        }
                    }
                }

                $stringLength = (int) $stringLength;

                $terminator = mb_substr($data, $i, 4, 'UTF-8');

                if ($terminator !== '\\r\\n') {
                    throw new Exception("Invalid string");
                }

                $i += 4;

                $string = mb_substr($data, $i, $stringLength, 'UTF-8');

                $terminator = mb_substr($data, $i + $stringLength, 4, 'UTF-8');

                if ($terminator !== '\\r\\n') {
                    throw new Exception("Invalid string");
                }

                $this->tokens[] = new Node(Token::BULK_STRING, $string);
                $this->tokens[] = new Node(Token::TERMINATOR, $terminator);
                $this->position = $i + $stringLength + 4;
            }
            $i++;
        }

        return $this->tokens;
    }
}