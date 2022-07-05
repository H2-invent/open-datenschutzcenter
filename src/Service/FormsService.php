<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AuditTomAbteilung;
use App\Entity\Forms;
use App\Entity\Produkte;
use App\Entity\Team;
use App\Entity\User;
use App\Form\Type\FormsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class FormsService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
    }

    function newForm(User $user)
    {
        $form = new Forms();
        $form->setStatus(0);
        $form->setTeam($user->getTeams()[0]);
        $form->setActiv(true);
        $form->setCreatedAt(new \DateTime());
        $form->setUser($user);

        return $form;
    }

    function createForm(Forms $forms, Team $team)
    {
        $departments = $this->em->getRepository(AuditTomAbteilung::class)->findBy(array('team' => $team, 'activ' => true));
        $products = $this->em->getRepository(Produkte::class)->findBy(array('team' => $team, 'activ' => true));

        $form = $this->formBuilder->create(FormsType::class, $forms, ['products' => $products, 'departments' => $departments]);

        return $form;
    }

    function cloneForms(Forms $forms, User $user)
    {
        $newForms = clone $forms;
        $newForms->setPrevious($forms);
        $newForms->setCreatedAt(new \DateTime());
        $newForms->setUpdatedAt(new \DateTime());
        $newForms->setUser($user);
        return $newForms;
    }
}
