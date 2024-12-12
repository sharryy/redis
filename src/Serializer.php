<?php

namespace App;

use Throwable;
use Exception;

class Serializer
{
    public function serialise(mixed $data): string
    {
        try {
            $type = gettype($data);

            if ($type === 'string' && strtolower($data) === 'ping') {
                return "+PONG\\r\\n";
            }

            if ($type === 'string') {
                return $this->serialiseSimpleStrings($data);
            }

            if ($type === 'integer') {
                return $this->serialiseIntegers($data);
            }

            if ($type === 'array') {
                return $this->serialiseArrays($data);
            }
        } catch (Throwable $exception) {
            return "-{$exception->getMessage()}\\r\\n";
        }
    }


    private function serialiseSimpleStrings(string $data): string
    {
        if (str_contains($data, '\r') || str_contains($data, '\n')) {
            return "+Error\\r\\n";
        }

        return "+$data\\r\\n";
    }

    private function serialiseIntegers(int $data): string
    {
        return ":$data\\r\\n";
    }

    private function serialiseBulkStrings(string $item): string
    {
        return "\$".strlen($item)."\\r\\n".$item."\\r\\n";
    }

    /** @throws Throwable */
    private function serialiseArrays(mixed $data, int $depth = 1): string
    {
        if ($depth > 20) {
            throw new Exception("Depth limit reached");
        }

        $length = count($data);
        $result = "*$length\\r\\n";

        foreach ($data as $item) {
            $result .= match (gettype($item)) {
                'string' => $this->serialiseBulkStrings($item),
                'integer' => $this->serialiseIntegers($item),
                'array' => $this->serialiseArrays($item, $depth++),
            };
        }

        return $result;
    }
}