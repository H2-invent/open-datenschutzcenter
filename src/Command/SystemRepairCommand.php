<?php
declare(strict_types=1);

namespace App\Command;

use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:system:repair')]
class SystemRepairCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private TeamRepository $teamRepository,
        string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output):int {
        $io = new SymfonyStyle($input, $output);
        $io->info('-------------------------');
        $io->info('we try to repair you ODC');
        $io->info('-------------------------');
        $io->info('We repair the external DSB entities');
        $this->addDsbAsAdminAndTeamMember();

        $io->success('We repaired your system');
        return self::SUCCESS;
    }

    private function addDsbAsAdminAndTeamMember(): void
    {
        $teams = $this->teamRepository->findAll();

        foreach($teams as $team){
            $user = $team->getDsbUser();

            if($user === null){
                continue;
            }

            $team->addAdmin($user);
            $team->addMember($user);

            $this->em->persist($team);
            $this->em->flush();
        }
    }
}