<?php

use App\Deserializer;

beforeEach(function () {
    $this->deserializer = new Deserializer();
});

it('can deserialize simple strings', function () {
    $command = '+OK\r\n';

    $result = $this->deserializer->deserialise($command);

    expect($result)->toBe('OK');
});

it('can deserialize integers', function () {
    $command = ':1\r\n';

    $result = $this->deserializer->deserialise($command);

    expect($result)->toBe(1);
});

it('can deserialise bulk strings', function () {
    $command = '$5\r\nHello\r\n';

    $result = $this->deserializer->deserialise($command);

    expect($result)->toBe('Hello');
});


it('can lex', function () {
    $command = '*4\r\n$5\r\nHello\r\n$5\r\nWorld\r\n:1\r\n*1\r\n$6\r\nNested\r\n';

    $result = (new \App\Parser())->tokenize($command);
});