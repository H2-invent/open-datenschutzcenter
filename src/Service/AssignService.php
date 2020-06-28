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
        $assignVvt = array();
        $assignAudit = array();
        $assign = array();
        try {
            if ($request->get('vvt')) {
                $assignVvt = $user->getAssignedVvts()->toarray();
            }
            if ($request->get('audit')) {
                $assignAudit = $user->getAssignedAudits()->toarray();
            }

            $assign = new ArrayCollection(array_merge($assignAudit, $assignVvt));
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
                    $content = $this->twig->render('email/assignementVvt.html.twig', ['assign' => $vvt->getName(), 'data' => $vvt]);
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
                    $content = $this->twig->render('email/assignementAudit.html.twig', ['assign' => $audit->getFrage(), 'data' => $audit]);
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
}
