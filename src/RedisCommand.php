<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('redis')]
class RedisCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        dump('Redis command');

        return 1;
    }
}