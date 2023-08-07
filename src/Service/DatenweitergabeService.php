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
use App\Repository\SoftwareRepository;
use App\Repository\VVTRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;


class DatenweitergabeService
{
    const PREFIX_PROCESSING = 'AVV-';
    const PREFIX_TRANSFER = 'DW-';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface   $formBuilder,
        private readonly CurrentTeamService     $currentTeamService,
        private readonly VVTRepository          $processRepository,
        private readonly SoftwareRepository     $softwareRepository,
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

    function createForm(Datenweitergabe $datenweitergabe, Team $team, array $options = []): FormInterface
    {
        if (isset($options['disabled']) && $options['disabled']) {
            $teamPath = $this->em->getRepository(Team::class)->getPath($team);
            $stand = $this->em->getRepository(DatenweitergabeStand::class)->findActiveByTeamPath($teamPath);
            $grundlagen = $this->em->getRepository(DatenweitergabeGrundlagen::class)->findActiveByTeamPath($teamPath);
        } else {
            $stand = $this->em->getRepository(DatenweitergabeStand::class)->findActiveByTeam($team);
            $grundlagen = $this->em->getRepository(DatenweitergabeGrundlagen::class)->findActiveByTeam($team);
        }

        $processes = $this->processRepository->findActiveByTeam($team);
        $software = $this->softwareRepository->findActiveByTeam($team);

        return $this->formBuilder->create(DatenweitergabeType::class, $datenweitergabe, array_merge([
            'stand' => $stand,
            'grundlage' => $grundlagen,
            'kontakt' => $team->getKontakte(),
            'verfahren' => $processes,
            'software' => $software
        ], $options));
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
