<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Datenweitergabe;
use App\Entity\Software;
use App\Entity\SoftwareConfig;
use App\Entity\Team;
use App\Entity\User;
use App\Form\Type\SoftwareConfigType;
use App\Form\Type\SoftwareType;
use App\Repository\VVTRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;


class SoftwareService
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface   $formBuilder,
        private readonly VVTRepository          $processRepository,
    )
    {
    }

    public function cloneSoftware(Software $software, User $user): Software
    {
        $newSoftware = clone $software;
        $newSoftware->setPrevious($software);
        $newSoftware->setActiv(true);
        $newSoftware->setUser($user);
        $newSoftware->setCreatedAt(new DateTime());
        return $newSoftware;
    }

    public function createConfigForm(SoftwareConfig $softwareConfig): FormInterface
    {
        $form = $this->formBuilder->create(SoftwareConfigType::class, $softwareConfig);

        return $form;
    }

    public function createForm(Software $software, Team $team, array $options = []): FormInterface
    {
        if (isset($options['disabled']) && $options['disabled']) {
            $teamPath = $this->em->getRepository(Team::class)->getPath($team);
            $data = $this->em->getRepository(Datenweitergabe::class)->findActiveTransfersByTeamPath($teamPath);
        } else {
            $data = $this->em->getRepository(Datenweitergabe::class)->findBy(['team' => $team, 'activ' => true, 'art' => 1]);
        }

        $processes = $this->processRepository->findActiveByTeam($team);

        return $this->formBuilder->create(SoftwareType::class, $software, array_merge([
            'processes' => $processes,
            'datenweitergabe' => $data
        ], $options));
    }

    public function newConfig(Software $software): SoftwareConfig
    {
        $config = new SoftwareConfig();
        $config->setCreatedAt(new DateTime());
        $config->setActiv(true);
        $config->setSoftware($software);

        return $config;
    }

    public function newSoftware(Team $team, User $user): Software
    {
        $software = new Software();
        $software->setTeam($team);
        $software->setCreatedAt(new DateTime());
        $software->setPurchase(new DateTime());
        $software->setActiv(true);
        $software->setUser($user);

        return $software;
    }
}
