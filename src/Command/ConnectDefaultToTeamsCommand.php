<?php

namespace App\Command;

use App\Entity\Team;
use App\Service\ConnectDefaultToTeamsService;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConnectDefaultToTeamsCommand extends Command
{
    protected static $defaultName = 'app:connect:defaultToTeams';
    protected static $defaultDescription = 'This Command converts default attributes which can be selected in the dropdown field. ONLY USE ONCE. MAKE A BACKUP OF THE DATABASE BEFORE EXECUTING.';
    private $connectDefaultService;
    private $em;

    public function __construct(ConnectDefaultToTeamsService $connectDefaultToTeamsService, EntityManagerInterface $entityManager, $name = null)
    {
        parent::__construct($name);
        $this->connectDefaultService = $connectDefaultToTeamsService;
        $this->em = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $io = new SymfonyStyle($input, $output);
        $teams = $this->em->getRepository(Team::class)->findAll();
        $io->success(sprintf('We will connect %d Teams', sizeof($teams)));
        $section1 = $output->section();
        $section2 = $output->section();
        $progressBar = new ProgressBar($section1, sizeof($teams));
        $progressBar->start();

        foreach ($teams as $data) {
            $this->connectDefaultService->connectDefault($data, $section2);
            $progressBar->advance();
        }
        $progressBar->finish();

        $io->success('Success');

        return Command::SUCCESS;
    }
}
