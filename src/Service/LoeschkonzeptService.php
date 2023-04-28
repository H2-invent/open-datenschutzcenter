<?php


namespace App\Service;


use App\Entity\Loeschkonzept;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVTDatenkategorie;
use App\Form\Type\LoeschkonzeptType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;


class LoeschkonzeptService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
    }

    function createForm(Loeschkonzept $loeschkonzept, Team $team): FormInterface
    {
        $vvtdatenkategories = $this->em->getRepository(VVTDatenkategorie::class)->findByTeam($team);

        $form = $this->formBuilder->create(LoeschkonzeptType::class, $loeschkonzept, ['vvtdatenkategories' => $vvtdatenkategories]);

        return $form;
    }

    function newLoeschkonzept(Team $team, User $user): Loeschkonzept
    {
        $loeschkonzept = new Loeschkonzept();
        $loeschkonzept->setTeam($team);
        $loeschkonzept->setCreateAt(new DateTimeImmutable());
        $loeschkonzept->setActiv(true);
        $loeschkonzept->setUser($user);

        return $loeschkonzept;
    }


    function cloneLoeschkonzept(Loeschkonzept $loeschkonzept): Loeschkonzept
    {
        $newLoeschkonzept = clone $loeschkonzept;
        $newLoeschkonzept->setPrevious($loeschkonzept);
        $newLoeschkonzept->setCreateAt(new DateTimeImmutable());

        return $newLoeschkonzept;
    }
}
