<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Software;
use App\Entity\SoftwareConfig;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVT;
use App\Form\Type\SoftwareConfigType;
use App\Form\Type\SoftwareType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class SoftwareService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
    }

    function newSoftware(Team $team, User $user)
    {
        $software = new Software();
        $software->setTeam($team);
        $software->setCreatedAt(new \DateTime());
        $software->setActiv(true);
        $software->setUser($user);

        return $software;
    }

    function cloneSoftware(Software $software, User $user)
    {
        $newSoftware = clone $software;
        $newSoftware->setPrevious($software);
        $newSoftware->setActiv(true);
        $newSoftware->setUser($user);
        $newSoftware->setCreatedAt(new \DateTime());
        return $newSoftware;
    }

    function createForm(Software $software, Team $team)
    {
        $processes = $this->em->getRepository(VVT::class)->findActivByTeam($team);
        $form = $this->formBuilder->create(SoftwareType::class, $software, ['processes' => $processes]);

        return $form;
    }

    function newConfig(Software $software)
    {
        $config = new SoftwareConfig();
        $config->setCreatedAt(new \DateTime());
        $config->setActiv(true);
        $config->setSoftware($software);

        return $config;
    }

    function createConfigForm(SoftwareConfig $softwareConfig)
    {
        $form = $this->formBuilder->create(SoftwareConfigType::class, $softwareConfig);

        return $form;
    }
}
