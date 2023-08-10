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
use App\Repository\AuditTomAbteilungRepository;
use App\Repository\DatenweitergabeRepository;
use App\Repository\ProdukteRepository;
use App\Repository\SoftwareRepository;
use App\Repository\TomRepository;
use App\Repository\VVTDatenkategorieRepository;
use App\Repository\VVTGrundlageRepository;
use App\Repository\VVTPersonenRepository;
use App\Repository\VVTRisikenRepository;
use App\Repository\VVTStatusRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;


class VVTService
{
    public function __construct(
        private readonly FormFactoryInterface        $formBuilder,
        private readonly TomRepository               $tomRepository,
        private readonly SoftwareRepository          $softwareRepository,
        private readonly DatenweitergabeRepository   $transferRepository,
        private readonly VVTStatusRepository         $processStatusRepository,
        private readonly VVTPersonenRepository       $processPeopleRepository,
        private readonly VVTDatenkategorieRepository $processCategoryRepository,
        private readonly VVTGrundlageRepository      $processBasisRepository,
        private readonly VVTRisikenRepository        $processRiskRepository,
        private readonly AuditTomAbteilungRepository $departmentRepository,
        private readonly ProdukteRepository          $productRepository,
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

    function createForm(VVT $VVT, Team $team, array $options = []): FormInterface
    {
        $transfers = $this->transferRepository->findActiveByTeam($team);
        $tom = $this->tomRepository->findActiveByTeam($team);
        $software = $this->softwareRepository->findActiveByTeam($team);
        $statuses = $this->processStatusRepository->findActiveByTeam($team);
        $people = $this->processPeopleRepository->findByTeam($team);
        $categories = $this->processCategoryRepository->findByTeam($team);
        $bases = $this->processBasisRepository->findByTeam($team);
        $risks = $this->processRiskRepository->findByTeam($team);
        $departments = $this->departmentRepository->findActiveByTeam($team);
        $products = $this->productRepository->findActiveByTeam($team);

        return $this->formBuilder->create(VVTType::class, $VVT, array_merge(
            [
                'personen' => $people,
                'kategorien' => $categories,
                'risiken' => $risks,
                'status' => $statuses,
                'grundlage' => $bases,
                'user' => $team->getMembers(),
                'daten' => $transfers,
                'tom' => $tom,
                'abteilung' => $departments,
                'produkte' => $products,
                'software' => $software
            ],
            $options
        ));
    }

    function newDsfa(User $user, VVT $vvt): VVTDsfa
    {
        $dsfa = new VVTDsfa();
        $dsfa->setVvt($vvt);
        $dsfa->setCreatedAt(new DateTime());
        $dsfa->setActiv(true);
        $dsfa->setUser($user);

        return $dsfa;
    }

    function newVvt(Team $team, User $user): VVT
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
