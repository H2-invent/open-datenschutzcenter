<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vorfall;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTPersonen;
use App\Form\Type\VorfallType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class VorfallService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
    }

    function cloneVorfall(Vorfall $input, User $user)
    {
        $data = clone $input;
        $data->setPrevious($input);
        $data->setActiv(true);
        $data->setUser($user);
        $data->setCreatedAt(new DateTime());
        return $data;
    }

    function createForm(Vorfall $vorfall, Team $team)
    {
        $personen = $this->em->getRepository(VVTPersonen::class)->findActiveByTeam($team);
        $kategorien = $this->em->getRepository(VVTDatenkategorie::class)->findByTeam($team);

        $form = $this->formBuilder->create(VorfallType::class, $vorfall, ['personen' => $personen, 'daten' => $kategorien]);

        return $form;
    }

    function newVorfall(Team $team, User $user)
    {
        $vorfall = new Vorfall();
        $vorfall->setTeam($team);
        $vorfall->setActiv(true);
        $vorfall->setNummer(uniqid());
        $vorfall->setCreatedAt(new DateTime());
        $vorfall->setUser($user);
        $vorfall->setDatum(new DateTime());

        return $vorfall;
    }
}
