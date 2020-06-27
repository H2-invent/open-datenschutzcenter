<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AuditTom;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVT;
use App\Form\Type\AssignType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;


class AssignService
{
    private $em;
    private $formBuilder;
    private $router;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder, RouterInterface $router)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
        $this->router = $router;
    }

    function createForm($data, Team $team)
    {
        $form = $this->formBuilder->create(AssignType::class, $data, ['user' => $team->getMembers()]);
        return $form;
    }

    function assignVvt($request)
    {
        try {
            $vvt = $this->em->getRepository(VVT::class)->find($request->get('id'));

            if ($vvt->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                $vvt->setAssignedUser($user);
            } else {
                $vvt->setAssignedUser(null);
            }
            $this->em->persist($vvt);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    function assignAudit($request)
    {
        try {
            $audit = $this->em->getRepository(AuditTom::class)->find($request->get('id'));

            if ($audit->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                $audit->setAssignedUser($user);
            } else {
                $audit->setAssignedUser(null);
            }
            $this->em->persist($audit);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
