<?php


namespace App\Service;


use App\Entity\VVTDatenkategorie;
use App\Entity\Loeschkonzept;
use App\Entity\Team;
use App\Entity\User;
use App\Form\Type\VVTDatenkategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class VVTDatenkategorieService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
    }

    function createForm(VVTDatenkategorie $vVTDatenkategorie, Team $team)
    {
        $form = $this->formBuilder->create(VVTDatenkategorieType::class, $vVTDatenkategorie);

        return $form;
    }

    function newVVTDatenkategorie(Team $team, User $user)
    {
        $vVTDatenkategorie = new VVTDatenkategorie();
        $vVTDatenkategorie->setTeam($team);
        $vVTDatenkategorie->setCreatedAt(new \DateTimeImmutable());
        $vVTDatenkategorie->setActiv(true);
        $vVTDatenkategorie->setUser($user);

        return $vVTDatenkategorie;
    }


    function cloneVVTDatenkategorie(VVTDatenkategorie $vVTDatenkategorie)
    {
        $newVVTDatenkategorie = clone $vVTDatenkategorie;
        $newVVTDatenkategorie->setPrevious($vVTDatenkategorie);
        $newVVTDatenkategorie->setCreatedAt(new \DateTimeImmutable());
        $newVVTDatenkategorie->setActiv(true);
        return $newVVTDatenkategorie;
    }

    function createChild(VVTDatenkategorie $vVTDatenkategorie)
    {
        $childVVTDatenkategorie = clone $vVTDatenkategorie;
    }
}