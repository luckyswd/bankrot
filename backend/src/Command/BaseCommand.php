<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'base:command',
    description: 'Base command class with execution statistics',
)]
class BaseCommand extends Command
{
    private SymfonyStyle $io;

    private array $execInfo = [
        'start_time' => null,
        'finish_time' => null,
        'start_memory' => null,
        'finish_memory' => null,
    ];

    protected function initialize(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $this->io = new SymfonyStyle($input, $output);
        $this->execInfo['start_time'] = microtime(true);
        $this->execInfo['start_memory'] = memory_get_usage();
    }

    public function __destruct()
    {
        $this->execInfo['finish_time'] = microtime(true);
        $this->execInfo['finish_memory'] = memory_get_usage();

        $executionTime = round($this->execInfo['finish_time'] - $this->execInfo['start_time'], 2);
        $memoryUsage = round(($this->execInfo['finish_memory'] - $this->execInfo['start_memory']) / 1024 / 1024, 2);
        $startTime = date('Y-m-d H:i:s', (int) $this->execInfo['start_time']);
        $finishTime = date('Y-m-d H:i:s', (int) $this->execInfo['finish_time']);

        $this->io->writeln("<fg=green>command_name:</> <fg=yellow>{$this->getName()}</>;");
        $this->io->writeln("<fg=green>start_time:</> <fg=yellow>{$startTime}</>;");
        $this->io->writeln("<fg=green>finish_time:</> <fg=yellow>{$finishTime}</>;");
        $this->io->writeln("<fg=green>execution_time:</> <fg=yellow>{$executionTime} sec</>;");
        $this->io->writeln("<fg=green>memory_usage:</> <fg=yellow>{$memoryUsage} MB</>;");
    }
}
