<?php

namespace App\Command;

use App\Entity\Team;
use App\Service\ConnectDefaultToTeamsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class TeamNewCommand extends Command
{
    protected static $defaultName = 'app:team:new';
    protected static $defaultDescription = 'Create a new Team';
    private $em;
    private $connectService;

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    public function __construct(EntityManagerInterface $entityManager, ConnectDefaultToTeamsService $connectDefaultToTeamsService, string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->connectService = $connectDefaultToTeamsService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $team = new Team();
        $question = new Question('Team name (identical to keycloak group name if keycloak groups are used): ', 'TestCompany');
        $name = $helper->ask($input, $output, $question);
        $team->setName($name);
        $question = new Question('Display name (if different from team name): ', '');
        $keycloakGroup = $helper->ask($input, $output, $question);
        $team->setKeycloakGroup($keycloakGroup);
        $question = new Question('Street: ', '');
        $street = $helper->ask($input, $output, $question);
        $team->setStrasse($street);
        $question = new Question('PLZ: ', 'default');
        $plz = $helper->ask($input, $output, $question);
        $team->setPlz($plz);
        $question = new Question('City: ', 'default');
        $city = $helper->ask($input, $output, $question);
        $team->setStadt($city);
        $question = new Question('Email: ', 'default');
        $email = $helper->ask($input, $output, $question);
        $team->setEmail($email);
        $question = new Question('DSB : ', 'default');
        $dsb = $helper->ask($input, $output, $question);
        $team->setDsb($dsb);
        $question = new Question('Phone: ', 'default');
        $phone = $helper->ask($input, $output, $question);
        $team->setTelefon($phone);
        $question = new Question('CEO: ', 'default');
        $ceo = $helper->ask($input, $output, $question);
        $team->setCeo($ceo);
        $question = new Question('Email signature: ', 'default');
        $signature = $helper->ask($input, $output, $question);
        $team->setSignature($signature);
        $question = new Question('Industry: ', 'default');
        $industry = $helper->ask($input, $output, $question);
        $team->setIndustry($industry);
        $question = new Question('Specialty: ', 'default');
        $specialty = $helper->ask($input, $output, $question);
        $team->setSpecialty($specialty);
        $team->setActiv(true);
        $this->em->persist($team);
        $this->em->flush();
        $this->connectService->connectDefault($team, $output);

        $io->success(sprintf('We created a new Team with the name %s', $team->getName()));

        return Command::SUCCESS;
    }
}
