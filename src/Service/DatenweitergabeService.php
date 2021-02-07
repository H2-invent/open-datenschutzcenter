<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Datenweitergabe;
use App\Entity\DatenweitergabeGrundlagen;
use App\Entity\DatenweitergabeStand;
use App\Entity\Software;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVT;
use App\Entity\VVTDsfa;
use App\Form\Type\DatenweitergabeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class DatenweitergabeService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
    }

    function newDatenweitergabe(User $user, $type, $prefix)
    {
        $daten = new Datenweitergabe();
        $daten->setTeam($user->getTeam());
        $daten->setNummer($prefix . hexdec(uniqid()));
        $daten->setActiv(true);
        $daten->setCreatedAt(new \DateTime());
        $daten->setArt($type);
        $daten->setUser($user);

        return $daten;
    }

    function cloneDatenweitergabe(Datenweitergabe $datenweitergabe, User $user)
    {
        $newDaten = clone $datenweitergabe;
        $newDaten->setPrevious($datenweitergabe);
        $newDaten->setCreatedAt(new \DateTime());
        $newDaten->setUpdatedAt(new \DateTime());
        $newDaten->setUser($user);
        return $newDaten;
    }

    function createForm(Datenweitergabe $datenweitergabe, Team $team)
    {
        $stand = $this->em->getRepository(DatenweitergabeStand::class)->findByTeam($team);
        $grundlagen = $this->em->getRepository(DatenweitergabeGrundlagen::class)->findByTeam($team);
        $verfahren = $this->em->getRepository(VVT::class)->findBy(array('team' => $team, 'activ' => true));
        $software = $this->em->getRepository(Software::class)->findBy(array('team' => $team, 'activ' => true));

        $form = $this->formBuilder->create(DatenweitergabeType::class, $datenweitergabe, ['stand' => $stand, 'grundlage' => $grundlagen, 'kontakt' => $team->getKontakte(), 'verfahren' => $verfahren, 'software' => $software]);

        return $form;
    }

    function newDsfa(Team $team, User $user, VVT $vvt)
    {
        $dsfa = new VVTDsfa();
        $dsfa->setVvt($vvt);
        $dsfa->setCreatedAt(new \DateTime());
        $dsfa->setActiv(true);
        $dsfa->setUser($user);

        return $dsfa;
    }

    function cloneDsfa(VVTDsfa $dsfa, User $user)
    {
        $newDsfa = clone $dsfa;
        $newDsfa->setPrevious($dsfa);
        $newDsfa->setVvt($dsfa->getVvt());
        $newDsfa->setActiv(true);
        $newDsfa->setCreatedAt(new \DateTime());
        $newDsfa->setUser($user);

        return $newDsfa;
    }
}
