<?php

namespace App\Command;

use App\Entity\VVT;
use App\Service\VVTDatenkategorieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateKategorienCommand extends Command
{
    protected static $defaultName = 'app:migrate:kategorien';
    protected static $defaultDescription = 'Add a short description for your command';
    private $em;
    private VVTDatenkategorieService $VVTDatenkategorieService;
    public function __construct(VVTDatenkategorieService $VVTDatenkategorieService, EntityManagerInterface  $entityManager, $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->VVTDatenkategorieService = $VVTDatenkategorieService;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $vvt = $this->em->getRepository(VVT::class)->findAll();
        foreach ($vvt as $data){
            $kat = $data->getKategorien();
            foreach ($kat as $data2){
                $data->addKategorien($this->VVTDatenkategorieService->createChild($data2));
                $data->removeKategorien($data2);
            }
            $this->em->persist($data);
        }
        $this->em->flush();

        $io->success('We migrate the vvts categories');

        return Command::SUCCESS;
    }
}
