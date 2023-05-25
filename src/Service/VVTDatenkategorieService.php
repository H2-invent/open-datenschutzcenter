<?php


namespace App\Service;


use App\Entity\Loeschkonzept;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVTDatenkategorie;
use App\Form\Type\VVTDatenkategorieType;
use DateTimeImmutable;
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

    function cloneVVTDatenkategorie(VVTDatenkategorie $vVTDatenkategorie)
    {
        $newVVTDatenkategorie = clone $vVTDatenkategorie;
        $newVVTDatenkategorie->setPrevious($vVTDatenkategorie);
        $newVVTDatenkategorie->setCreatedAt(new DateTimeImmutable());
        $newVVTDatenkategorie->setActiv(true);
        return $newVVTDatenkategorie;
    }

    function createChild(VVTDatenkategorie $vVTDatenkategorie)
    {
        //first we clone the datenkategorie
        $childVVTDatenkategorie = new VVTDatenkategorie();
        $childVVTDatenkategorie->setCloneOf($vVTDatenkategorie);
        $childVVTDatenkategorie->setCreatedAt(new DateTimeImmutable())
            ->setPrevious(null)
            ->setName($vVTDatenkategorie->getName())
            ->setDatenarten($vVTDatenkategorie->getDatenarten())
            ->setTeam($vVTDatenkategorie->getTeam())
            ->setUser($vVTDatenkategorie->getUser())
            ->setActiv(false);

        // we clone the löschkonzept
        $loeschkonzept = $vVTDatenkategorie->getLastLoeschkonzept();
        if ($loeschkonzept) {
            $childLoeschkonzept = new Loeschkonzept();
            $childLoeschkonzept->setUser($loeschkonzept->getUser())
                ->setTeam($loeschkonzept->getTeam())
                ->setPrevious(null)
                ->setCloneOf($loeschkonzept)
                ->setUser($loeschkonzept->getUser())
                ->setActiv(false)
                ->setBeschreibung($loeschkonzept->getBeschreibung())
                ->setCreateAt(new DateTimeImmutable())
                ->setLoeschbeauftragter($loeschkonzept->getLoeschbeauftragter())
                ->setLoeschfrist($loeschkonzept->getLoeschfrist())
                ->setSpeicherorte($loeschkonzept->getSpeicherorte())
                ->setStandartlf($loeschkonzept->getStandartlf());

            $childVVTDatenkategorie->addLoeschkonzept($childLoeschkonzept);
        }
        return $childVVTDatenkategorie;
    }

    function createForm(VVTDatenkategorie $vVTDatenkategorie, Team $team)
    {
        $form = $this->formBuilder->create(VVTDatenkategorieType::class, $vVTDatenkategorie);

        return $form;
    }

    function findLatestKategorie(VVTDatenkategorie $VVTDatenkategorie): ?VVTDatenkategorie
    {
        $act = $VVTDatenkategorie;
        for (; ;) {
            $next = $this->em->getRepository(VVTDatenkategorie::class)->findOneBy(array('previous' => $act));
            if ($next == null) {
                return $act;
            }
            $act = $next;
        }

    }

    function newVVTDatenkategorie(Team $team, User $user)
    {
        $vVTDatenkategorie = new VVTDatenkategorie();
        $vVTDatenkategorie->setTeam($team);
        $vVTDatenkategorie->setCreatedAt(new DateTimeImmutable());
        $vVTDatenkategorie->setActiv(true);
        $vVTDatenkategorie->setUser($user);

        return $vVTDatenkategorie;
    }

}
