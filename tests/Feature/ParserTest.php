<?php

use App\Node;
use App\Parser;
use App\Token;

it('can parse integers', function () {
    $command = ':3\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::INTEGER, 3),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse integers with multiple digits', function () {
    $command = ':123\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::INTEGER, 123),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse simple strings', function () {
    $command = '+Hello\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::SIMPLE_STRING, 'Hello'),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse bulk strings', function () {
    $command = '$5\r\nHello\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::BULK_STRING, 'Hello'),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse bulk strings with double digit length', function () {
    $command = '$11\r\nHello World\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::BULK_STRING, 'Hello World'),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse simple integer array', function () {
    $command = '*1\r\n:1\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::ARRAY, [
            new Node(Token::INTEGER, 1),
        ]),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse simple string array', function () {
    $command = '*1\r\n$5\r\nHello\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::ARRAY, [
            new Node(Token::BULK_STRING, 'Hello'),
        ]),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse integer and string array', function () {
    $command = '*2\r\n:1\r\n$5\r\nHello\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::ARRAY, [
            new Node(Token::INTEGER, 1),
            new Node(Token::BULK_STRING, 'Hello'),
        ]),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse array of multiple integers', function () {
    $command = '*2\r\n:1\r\n:2\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::ARRAY, [
            new Node(Token::INTEGER, 1),
            new Node(Token::INTEGER, 2),
        ]),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse array of multiple strings', function () {
    $command = '*2\r\n$5\r\nHello\r\n$5\r\nWorld\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::ARRAY, [
            new Node(Token::BULK_STRING, 'Hello'),
            new Node(Token::BULK_STRING, 'World'),
        ]),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can parse nested array', function () {
    $command = '*2\r\n:1\r\n*1\r\n$6\r\nNested\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::ARRAY, [
            new Node(Token::INTEGER, 1),
            new Node(Token::ARRAY, [
                new Node(Token::BULK_STRING, 'Nested'),
            ]),
        ]),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});