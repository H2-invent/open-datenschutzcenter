<?php

namespace App\Command;

use App\Service\CronService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cron',
    description: 'Add a short description for your command',
)]
class CronCommand extends Command
{
    public function __construct(
        private CronService $cronService,
        string              $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $res = $this->cronService->sendEmailsForAcademy();
        $io->success($res);
        $io->success('Cron finished');

        return Command::SUCCESS;
    }
}
