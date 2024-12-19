<?php

use App\Node;
use App\Parser;
use App\Token;

it('can lex integers', function () {
    $command = ':3\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::INTEGER, 3),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can lex integers with multiple digits', function () {
    $command = ':123\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::INTEGER, 123),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can lex simple strings', function () {
    $command = '+Hello\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::SIMPLE_STRING, 'Hello'),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can lex bulk strings', function () {
    $command = '$5\r\nHello\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::BULK_STRING, 'Hello'),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can lex bulk strings with double digit length', function () {
    $command = '$11\r\nHello World\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::BULK_STRING, 'Hello World'),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
});

it('can lex simple array', function () {
    $command = '*2\r\n$5\r\nHello\r\n$5\r\nWorld\r\n';

    $result = (new Parser())->tokenize($command);

    expect($result)->toMatchArray([
        new Node(Token::ARRAY, [
            new Node(Token::BULK_STRING, 'Hello'),
            new Node(Token::BULK_STRING, 'World'),
        ]),
        new Node(Token::TERMINATOR, '\r\n'),
    ]);
})->skip();