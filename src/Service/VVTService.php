<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AuditTomAbteilung;
use App\Entity\Datenweitergabe;
use App\Entity\Produkte;
use App\Entity\Software;
use App\Entity\Team;
use App\Entity\Tom;
use App\Entity\User;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTDsfa;
use App\Entity\VVTGrundlage;
use App\Entity\VVTPersonen;
use App\Entity\VVTRisiken;
use App\Entity\VVTStatus;
use App\Form\Type\VVTType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class VVTService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
    }

    function cloneDsfa(VVTDsfa $dsfa, User $user)
    {
        $newDsfa = clone $dsfa;
        $newDsfa->setPrevious($dsfa);
        $newDsfa->setVvt($dsfa->getVvt());
        $newDsfa->setActiv(true);
        $newDsfa->setCreatedAt(new DateTime());
        $newDsfa->setUser($user);

        return $newDsfa;
    }

    function cloneVvt(VVT $vvt, User $user)
    {
        $newVvt = clone $vvt;
        $newVvt->setPrevious($vvt);
        $newVvt->setActiv(true);
        $newVvt->setUser($user);
        $newVvt->setCreatedAt(new DateTime());
        return $newVvt;
    }

    function createForm(VVT $VVT, Team $team)
    {
        $status = $this->em->getRepository(VVTStatus::class)->findActiveByTeam($team);
        $personen = $this->em->getRepository(VVTPersonen::class)->findByTeam($team);
        $kategorien = $this->em->getRepository(VVTDatenkategorie::class)->findByTeam($team);
        $risiken = $this->em->getRepository(VVTRisiken::class)->findByTeam($team);
        $grundlagen = $this->em->getRepository(VVTGrundlage::class)->findByTeam($team);
        $daten = $this->em->getRepository(Datenweitergabe::class)->findBy(array('team' => $team, 'activ' => true));
        $tom = $this->em->getRepository(Tom::class)->findBy(array('team' => $team, 'activ' => true));
        $abteilung = $this->em->getRepository(AuditTomAbteilung::class)->findBy(array('team' => $team, 'activ' => true));
        $produkte = $this->em->getRepository(Produkte::class)->findActiveByTeam($team);
        $software = $this->em->getRepository(Software::class)->findActiveByTeam($team);

        $form = $this->formBuilder->create(VVTType::class, $VVT, [
            'personen' => $personen,
            'kategorien' => $kategorien,
            'risiken' => $risiken,
            'status' => $status,
            'grundlage' => $grundlagen,
            'user' => $team->getMembers(),
            'daten' => $daten,
            'tom' => $tom,
            'abteilung' => $abteilung,
            'produkte' => $produkte,
            'software' => $software
        ]);

        return $form;
    }

    function newDsfa(Team $team, User $user, VVT $vvt)
    {
        $dsfa = new VVTDsfa();
        $dsfa->setVvt($vvt);
        $dsfa->setCreatedAt(new DateTime());
        $dsfa->setActiv(true);
        $dsfa->setUser($user);

        return $dsfa;
    }

    function newVvt(Team $team, User $user)
    {
        $vvt = new VVT();
        $vvt->setTeam($team);
        $vvt->setNummer('VVT-' . hexdec(uniqid()));
        $vvt->setCreatedAt(new DateTime());
        $vvt->setActiv(true);
        $vvt->setUser($user);

        return $vvt;
    }
}
