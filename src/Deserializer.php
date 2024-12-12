<?php

namespace App;

class Deserializer
{
    public function deserialise(string $data): mixed
    {
        $type = mb_substr($data, 0, 1, 'UTF-8');

        return match ($type) {
            '+' => $this->deserialiseSimpleStrings($data),
            ':' => $this->deserialiseIntegers($data),
            '$' => $this->deserialiseBulkStrings($data)
        };
    }

    private function deserialiseSimpleStrings(string $data): string
    {
        return mb_substr($data, 1, -4, 'UTF-8');
    }

    private function deserialiseIntegers(string $data): int
    {
        return (int) mb_substr($data, 1, -4, 'UTF-8');
    }

    private function deserialiseBulkStrings(string $data): string
    {
        $data = ltrim($data, '$');

        $length = (int) $data[0];

        $data = ltrim($data, $length);

        $data = ltrim($data, '\r\n');

        return mb_substr($data, 0, $length, 'UTF-8');
    }
}