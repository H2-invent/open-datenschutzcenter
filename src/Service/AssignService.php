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
use App\Entity\Software;
use App\Entity\Task;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vorfall;
use App\Entity\VVT;
use App\Entity\VVTDsfa;
use App\Form\Type\AssignType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;


class AssignService
{
    private EntityManagerInterface $em;
    private FormFactoryInterface $formBuilder;
    private NotificationService $notificationService;
    private Environment $twig;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder, NotificationService $notificationService, Environment $engine)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
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
        if (count($team->getMembers()) > 0) {
            $teamMembers = $team->getMembers();
            if (!in_array($team->getDsbUser(), $teamMembers->toArray())) {
                if ($team->getDsbUser()) {
                    $teamMembers->add($team->getDsbUser());
                }
            }
        } else {
            $teamMembers = array();
        }
        $form = $this->formBuilder->create(AssignType::class, $data, ['user' => $teamMembers]);
        return $form;
    }

    function assignVvt($request, VVT $vvt)
    {
        try {

            if ($vvt->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($user && $user->hasTeam($vvt->getTeam())) {
                    $vvt->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementVvt.html.twig', [
                        'assign' => $vvt->getName(),
                        'data' => $vvt,
                        'team' => $vvt->getTeam(),
                    ]);
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
                if ($user && $user->hasTeam($audit->getTeam())) {
                    $audit->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementAudit.html.twig', [
                        'assign' => $audit->getFrage(),
                        'data' => $audit,
                        'team' => $audit->getTeam(),
                    ]);
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
                if ($user && $user->hasTeam($datenweitergabe->getTeam())) {
                    $datenweitergabe->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementDatenweitergabe.html.twig', [
                        'assign' => $datenweitergabe->getGegenstand(),
                        'data' => $datenweitergabe,
                        'team' => $datenweitergabe->getTeam(),
                    ]);
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
            $data = $request->get('assign');
            $user = $this->em->getRepository(User::class)->find($data['user']);
            $team = $dsfa->getVvt()->getTeam();
            if ($user && $user->hasTeam($team)) {
                $dsfa->setAssignedUser($user);
                $user->addAssignedDsfa($dsfa);
                var_dump('foo');
                $content = $this->twig->render('email/assignementDsfa.html.twig', [
                    'assign' => $dsfa->getVvt()->getName(),
                    'data' => $dsfa,
                    'team' => $team,
                ]);
                $this->notificationService->sendNotificationAssign($content, $user);
            }
            $this->em->persist($dsfa);
            $this->em->persist($dsfa);
            $this->em->flush();
            return true;
        } catch (\Exception $exception) {
            var_dump($exception);
            die();
            return false;
        }
    }

    function assignForm($request, Forms $forms)
    {
        try {

            if ($forms->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($user && $user->hasTeam($forms->getTeam())) {
                    $forms->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementForm.html.twig', [
                        'assign' => $forms->getTitle(),
                        'data' => $forms,
                        'team' => $forms->getTeam(),
                    ]);
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
                if ($user && $user->hasTeam($policies->getTeam())) {
                    $policies->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementPolicy.html.twig', [
                        'assign' => $policies->getScope(),
                        'data' => $policies,
                        'team' => $policies->getTeam(),
                    ]);
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

    function assignSoftware($request, Software $software)
    {
        try {

            if ($software->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($user && $user->hasTeam($software->getTeam())) {
                    $software->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementSoftware.html.twig', [
                        'assign' => $software->getName(),
                        'data' => $software,
                        'team' => $software->getTeam()
                    ]);
                    $this->notificationService->sendNotificationAssign($content, $user);
                }
            } else {
                $software->setAssignedUser(null);
            }
            $this->em->persist($software);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    function assignVorfall($request, Vorfall $vorfall)
    {
        try {

            if ($vorfall->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($user && $user->hasTeam($vorfall->getTeam())) {
                    $vorfall->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementVorfall.html.twig', [
                        'assign' => $vorfall->getFakten(),
                        'data' => $vorfall,
                        'team' => $vorfall->getTeam()
                    ]);
                    $this->notificationService->sendNotificationAssign($content, $user);
                }
            } else {
                $vorfall->setAssignedUser(null);
            }
            $this->em->persist($vorfall);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    function assignTask($request, Task $task)
    {
        try {

            if ($task->getAssignedUser() == null) {
                $data = $request->get('assign');
                $user = $this->em->getRepository(User::class)->find($data['user']);
                if ($user && $user->hasTeam($task->getTeam())) {
                    $task->setAssignedUser($user);
                    $content = $this->twig->render('email/assignementTask.html.twig', ['assign' => $task->getTitle(), 'data' => $task, 'team' => $task->getTeam()]);
                    $this->notificationService->sendNotificationAssign($content, $user);
                }
            } else {
                $task->setAssignedUser(null);
            }
            $this->em->persist($task);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
