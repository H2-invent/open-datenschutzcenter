<?php

namespace App\Command;

use App\Entity\Team;
use App\Service\ConnectDefaultToTeamsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConnectDefaultToTeamsCommand extends Command
{
    protected static $defaultName = 'app:connect:defaultToTeams';
    protected static $defaultDescription = 'This Command converts default attributes which can be selected in the dropdown field. ONLY USE ONCE. MAKE A BACKUP OF THE DATABASE BEFOE';
    private $connectDefaultService;
    private $em;

    public function __construct($name = null, ConnectDefaultToTeamsService $connectDefaultToTeamsService, EntityManagerInterface $entityManager)
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
        $io = new SymfonyStyle($input, $output);
        $teams = $this->em->getRepository(Team::class)->findAll();
        $io->success(sprintf('We connect %d Teams',sizeof($teams)));
        $progressBar = new ProgressBar($output, sizeof($teams));
        $progressBar->start();
        foreach ($teams as $data) {
            $this->connectDefaultService->connectDefault($data,$output);
            $progressBar->advance();
        }
        $progressBar->finish();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
