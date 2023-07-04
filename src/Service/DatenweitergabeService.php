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
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;


class DatenweitergabeService
{
    const PREFIX_PROCESSING = 'AVV-';
    const PREFIX_TRANSFER = 'DW-';

    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface   $formBuilder,
        private CurrentTeamService     $currentTeamService,

    )
    {
    }

    function cloneDatenweitergabe(Datenweitergabe $datenweitergabe, User $user): Datenweitergabe
    {
        $newDaten = clone $datenweitergabe;
        $newDaten->setPrevious($datenweitergabe);
        $newDaten->setCreatedAt(new DateTime());
        $newDaten->setUpdatedAt(new DateTime());
        $newDaten->setUser($user);
        return $newDaten;
    }

    function cloneDsfa(VVTDsfa $dsfa, User $user): VVTDsfa
    {
        $newDsfa = clone $dsfa;
        $newDsfa->setPrevious($dsfa);
        $newDsfa->setVvt($dsfa->getVvt());
        $newDsfa->setActiv(true);
        $newDsfa->setCreatedAt(new DateTime());
        $newDsfa->setUser($user);

        return $newDsfa;
    }

    function createForm(Datenweitergabe $datenweitergabe, Team $team): FormInterface
    {
        $stand = $this->em->getRepository(DatenweitergabeStand::class)->findActiveByTeam($team);
        $grundlagen = $this->em->getRepository(DatenweitergabeGrundlagen::class)->findActiveByTeam($team);
        $verfahren = $this->em->getRepository(VVT::class)->findBy(array('team' => $team, 'activ' => true));
        $software = $this->em->getRepository(Software::class)->findBy(array('team' => $team, 'activ' => true));

        $form = $this->formBuilder->create(DatenweitergabeType::class, $datenweitergabe, ['stand' => $stand, 'grundlage' => $grundlagen, 'kontakt' => $team->getKontakte(), 'verfahren' => $verfahren, 'software' => $software]);

        return $form;
    }

    function newDatenweitergabe(User $user, $type): Datenweitergabe
    {
        $data = new Datenweitergabe();
        $prefix = $type === 1 ? self::PREFIX_TRANSFER : self::PREFIX_PROCESSING;
        $data->setTeam($this->currentTeamService->getCurrentTeam($user));
        $data->setNummer($prefix . hexdec(uniqid()));
        $data->setActiv(true);
        $data->setCreatedAt(new DateTime());
        $data->setArt($type);
        $data->setUser($user);

        return $data;
    }

    function newDsfa(Team $team, User $user, VVT $vvt): VVTDsfa
    {
        $dsfa = new VVTDsfa();
        $dsfa->setVvt($vvt);
        $dsfa->setCreatedAt(new DateTime());
        $dsfa->setActiv(true);
        $dsfa->setUser($user);

        return $dsfa;
    }
}
