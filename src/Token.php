<?php

namespace App;

enum Token: string
{
    case SIMPLE_STRING = '+';
    case INTEGER = ':';
    case ERROR = '-';
    case BULK_STRING = '$';
    case ARRAY = '*';
    case TERMINATOR = '\r\n';
}