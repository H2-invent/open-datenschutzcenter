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
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class PoliciesService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
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

    function createForm(Policies $policies, Team $team)
    {
        $personen = $this->em->getRepository(VVTPersonen::class)->findByTeam($team);
        $kategorien = $this->em->getRepository(VVTDatenkategorie::class)->findByTeam($team);
        $processes = $this->em->getRepository(VVT::class)->findActiveByTeam($team);

        $form = $this->formBuilder->create(PolicyType::class, $policies, ['personen' => $personen, 'kategorien' => $kategorien, 'user' => $team->getMembers(), 'processes' => $processes]);

        return $form;
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
