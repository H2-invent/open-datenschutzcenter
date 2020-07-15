<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AuditTom;
use App\Entity\Datenweitergabe;
use App\Entity\Forms;
use App\Entity\Policies;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVT;
use App\Entity\VVTDsfa;
use App\Form\Type\AssignType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;


class AssignService
{
    private $em;
    private $formBuilder;
    private $router;
    private $notificationService;
    private $twig;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder, RouterInterface $router, NotificationService $notificationService, Environment $engine)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
        $this->router = $router;
        $this->notificationService = $notificationService;
        $this->twig = $engine;
    }

    function assign($request, User $user)
    {
        $assignDatenweitergabe = $user->getAssignedDatenweitergaben()->toarray();
        $assignVvt = $user->getAssignedVvts()->toarray();
        $assignAudit = $user->getAssignedAudits()->toarray();
        $assignDsfa = $user->getAssignedDsfa()->toarray();

        $assign = array();
        try {
            if ($request->get('vvt')) {
                $assignVvt = array();
            }
            if ($request->get('audit')) {
                $assignAudit = array();
            }
            if ($request->get('dsfa')) {
                $assignDsfa = array();
            }
            if ($request->get('daten')) {
                $assignDatenweitergabe = array();
            }

            $assign = new ArrayCollection(array_merge($assignAudit, $assignVvt, $assignDsfa, $assignDatenweitergabe));

            return $assign;

        } catch (\Exception $exception) {
            return $assign;
        }
    }

    function createForm($data, Team $team)
    {
        $form = $this->formBuilder->create(AssignType::class, $data, ['user' => $team->getMembers()]);
        return $form;
    }

    function assignVvt($request, VVT $vvt)
    {
        try {

            if ($vvt->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($vvt->getTeam() === $user->getTeam()) {
                    $vvt->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementVvt.html.twig', ['assign' => $vvt->getName(), 'data' => $vvt, 'team' => $user->getTeam()]);
                    $this->notificationService->sendNotificationAssign($content, $user);
                }
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

    function assignAudit($request, AuditTom $audit)
    {
        try {

            if ($audit->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($audit->getTeam() === $user->getTeam()) {
                    $audit->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementAudit.html.twig', ['assign' => $audit->getFrage(), 'data' => $audit, 'team' => $user->getTeam()]);
                    $this->notificationService->sendNotificationAssign($content, $user);
                }
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

    function assignDatenweitergabe($request, Datenweitergabe $datenweitergabe)
    {
        try {
            if ($datenweitergabe->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($datenweitergabe->getTeam() === $user->getTeam()) {
                    $datenweitergabe->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementDatenweitergabe.html.twig', ['assign' => $datenweitergabe->getGegenstand(), 'data' => $datenweitergabe, 'team' => $user->getTeam()]);
                    $this->notificationService->sendNotificationAssign($content, $user);
                }
            } else {
                $datenweitergabe->setAssignedUser(null);
            }
            $this->em->persist($datenweitergabe);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    function assignDsfa($request, VVTDsfa $dsfa)
    {
        try {

            if ($dsfa->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($dsfa->getVvt()->getTeam() === $user->getTeam()) {
                    $dsfa->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementDsfa.html.twig', ['assign' => $dsfa->getVvt()->getName(), 'data' => $dsfa, 'team' => $user->getTeam()]);
                    $this->notificationService->sendNotificationAssign($content, $user);
                }
            } else {
                $dsfa->setAssignedUser(null);
            }
            $this->em->persist($dsfa);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    function assignForm($request, Forms $forms)
    {
        try {

            if ($forms->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($forms->getTeam() === $user->getTeam()) {
                    $forms->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementForm.html.twig', ['assign' => $forms->getTitle(), 'data' => $forms, 'team' => $user->getTeam()]);
                    $this->notificationService->sendNotificationAssign($content, $user);
                }
            } else {
                $forms->setAssignedUser(null);
            }
            $this->em->persist($forms);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    function assignPolicy($request, Policies $policies)
    {
        try {

            if ($policies->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($policies->getTeam() === $user->getTeam()) {
                    $policies->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementPolicy.html.twig', ['assign' => $policies->getScope(), 'data' => $policies, 'team' => $user->getTeam()]);
                    $this->notificationService->sendNotificationAssign($content, $user);
                }
            } else {
                $policies->setAssignedUser(null);
            }
            $this->em->persist($policies);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
