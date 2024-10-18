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
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;


class FormsService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface   $formBuilder,
        private CurrentTeamService     $currentTeamService,
    )
    {
    }

    public function cloneForms(Forms $forms, User $user): Forms
    {
        $newForms = clone $forms;
        $newForms->setPrevious($forms);
        $newForms->setCreatedAt(new DateTime());
        $newForms->setUpdatedAt(new DateTime());
        $newForms->setUser($user);
        return $newForms;
    }

    public function createForm(Forms $forms, Team $team): FormInterface
    {
        $departments = $this->em->getRepository(AuditTomAbteilung::class)->findBy(array('team' => $team, 'activ' => true));
        $products = $this->em->getRepository(Produkte::class)->findBy(array('team' => $team, 'activ' => true));

        $form = $this->formBuilder->create(FormsType::class, $forms, ['products' => $products, 'departments' => $departments]);

        return $form;
    }

    public function newForm(User $user): Forms
    {
        $form = new Forms();
        $form->setStatus(0);
        $form->setTeam($this->currentTeamService->getCurrentTeam($user));
        $form->setActiv(true);
        $form->setCreatedAt(new DateTime());
        $form->setUser($user);

        return $form;
    }
}
