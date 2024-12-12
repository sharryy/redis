<?php

use App\Serializer;

beforeEach(function () {
    $this->serializer = new Serializer();
});

it('can serialise ping command', function () {
    $command = 'PING';

    $result = $this->serializer->serialise($command);

    expect($result)->toBe('+PONG\r\n');
});

it('can serialise strings', function () {
    $command = 'Hello';

    $result = $this->serializer->serialise($command);

    expect($result)->toBe('+Hello\r\n');
});

it('can serialise integers', function () {
    $command = 1;

    $result = $this->serializer->serialise($command);

    expect($result)->toBe(':1\r\n');
});

it('can serialise invalid strings', function () {
    $command = 'Hello \r\n';

    $result = $this->serializer->serialise($command);

    expect($result)->toBe('+Error\r\n');
});

it('can serialise arrays', function () {
    $command = ['Hello', 'World'];

    $result = $this->serializer->serialise($command);

    expect($result)->toBe('*2\r\n$5\r\nHello\r\n$5\r\nWorld\r\n');
});

it('can serialise integer arrays', function () {
    $command = [1, 2, 3];

    $result = $this->serializer->serialise($command);

    expect($result)->toBe('*3\r\n:1\r\n:2\r\n:3\r\n');
});

it('can serialise mixed arrays', function () {
    $command = ['Hello', 'World', 1, ['Nested']];

    $result = $this->serializer->serialise($command);

    expect($result)->toBe('*4\r\n$5\r\nHello\r\n$5\r\nWorld\r\n:1\r\n*1\r\n$6\r\nNested\r\n');
});
