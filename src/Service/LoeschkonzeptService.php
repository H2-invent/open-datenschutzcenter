<?php


namespace App\Service;


use App\Entity\Loeschkonzept;
use App\Entity\VVTDatenkategorie;
use App\Entity\Team;
use App\Form\Type\LoeschkonzeptType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class LoeschkonzeptService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
    }

    function createForm(Loeschkonzept $loeschkonzept, Team $team)
    {
        $vvtdatenkategories = $this->em->getRepository(VVTDatenkategorie::class)->findByTeam($team);

        $form = $this->formBuilder->create(LoeschkonzeptType::class, $loeschkonzept, ['vvtdatenkategories' => $vvtdatenkategories]);

        return $form;
    }


    function cloneLoeschkonzept(Loeschkonzept $loeschkonzept)
    {
        $newVvt = clone $loeschkonzept;
        $newVvt->setPrevious($loeschkonzept);
        $loeschkonzept->setActiv(false);
        $newVvt->setActiv(true);
        $newVvt->setCreateAt(new \DateTimeImmutable());
        return $newVvt;
    }
}