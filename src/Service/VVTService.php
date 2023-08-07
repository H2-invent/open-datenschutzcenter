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
use App\Repository\SoftwareRepository;
use App\Repository\TomRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class VVTService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface $formBuilder,
        private TomRepository $tomRepository,
        private SoftwareRepository $softwareRepository,
    )
    {
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

    function createForm(VVT $VVT, Team $team, array $options = [])
    {
        if (!isset($options['disabled']) || $options['disabled'] === false) {
            $status = $this->em->getRepository(VVTStatus::class)->findActiveByTeam($team);
            $personen = $this->em->getRepository(VVTPersonen::class)->findByTeam($team);
            $kategorien = $this->em->getRepository(VVTDatenkategorie::class)->findByTeam($team);
            $risiken = $this->em->getRepository(VVTRisiken::class)->findByTeam($team);
            $grundlagen = $this->em->getRepository(VVTGrundlage::class)->findByTeam($team);
            $daten = $this->em->getRepository(Datenweitergabe::class)->findBy(array('team' => $team, 'activ' => true));
            $abteilung = $this->em->getRepository(AuditTomAbteilung::class)->findBy(array('team' => $team, 'activ' => true));
            $produkte = $this->em->getRepository(Produkte::class)->findActiveByTeam($team);
        } else {
            $teamPath = $this->em->getRepository(Team::class)->getPath($team);
            $status = $this->em->getRepository(VVTStatus::class)->findActiveByTeamPath($teamPath);
            $personen = $this->em->getRepository(VVTPersonen::class)->findByTeamPath($teamPath);
            $kategorien = $this->em->getRepository(VVTDatenkategorie::class)->findByTeamPath($teamPath);
            $risiken = $this->em->getRepository(VVTRisiken::class)->findByTeamPath($teamPath);
            $grundlagen = $this->em->getRepository(VVTGrundlage::class)->findByTeamPath($teamPath);
            $daten = $this->em->getRepository(Datenweitergabe::class)->findActiveByTeamPath($teamPath);
            $abteilung = $this->em->getRepository(AuditTomAbteilung::class)->findActiveByTeamPath($teamPath);
            $produkte = $this->em->getRepository(Produkte::class)->findActiveByTeamPath($teamPath);
        }
        $tom = $this->tomRepository->findActiveByTeam($team);
        $software = $this->softwareRepository->findActiveByTeam($team);

        return $this->formBuilder->create(VVTType::class, $VVT, array_merge(
            [
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
            ],
            $options
        ));
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
