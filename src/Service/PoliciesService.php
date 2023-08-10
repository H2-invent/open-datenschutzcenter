<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Policies;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTPersonen;
use App\Form\Type\PolicyType;
use App\Repository\VVTDatenkategorieRepository;
use App\Repository\VVTPersonenRepository;
use App\Repository\VVTRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class PoliciesService
{
    public function __construct(
        private readonly FormFactoryInterface        $formBuilder,
        private readonly VVTRepository               $processRepository,
        private readonly VVTPersonenRepository       $processPeopleRepository,
        private readonly VVTDatenkategorieRepository $processCategoryRepository,
    )
    {
    }

    function clonePolicy(Policies $policy, User $user)
    {
        $newPolicy = clone $policy;
        $newPolicy->setPrevious($policy);
        $newPolicy->setApproved(false);
        $newPolicy->setApprovedBy(null);
        $newPolicy->setActiv(true);
        $newPolicy->setUser($user);
        $newPolicy->setCreatedAt(new DateTime());
        return $newPolicy;
    }

    function createForm(Policies $policies, Team $team, array $options = [])
    {
        $processes = $this->processRepository->findActiveByTeam($team);
        $people = $this->processPeopleRepository->findByTeam($team);
        $categories = $this->processCategoryRepository->findByTeam($team);

        return $this->formBuilder->create(PolicyType::class, $policies, array_merge([
            'personen' => $people,
            'kategorien' => $categories,
            'user' => $team->getMembers(),
            'processes' => $processes
        ], $options));
    }

    function newPolicy(Team $team, User $user)
    {
        $vvt = new Policies();
        $vvt->setTeam($team);
        $vvt->setCreatedAt(new DateTime());
        $vvt->setActiv(true);
        $vvt->setUser($user);

        return $vvt;
    }
}
