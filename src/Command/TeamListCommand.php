<?php

namespace App\Command;

use App\Entity\Team;
use App\Service\ConnectDefaultToTeamsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class TeamListCommand extends Command
{
    protected static $defaultName = 'app:team:list';
    protected static $defaultDescription = 'List existing teams';
    private $em;

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    public function __construct(string $name = null, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->em = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $teams = $this->em->getRepository(Team::class)->findAll();
        foreach ($teams as $team) {
            $teamInfo = $team->getName();
            if ($team->getKeycloakGroup()) {
                $teamInfo .= ' (' . $team->getKeycloakGroup() . ')';
            }
            $output->writeln($teamInfo);
        }

        return Command::SUCCESS;
    }
}
