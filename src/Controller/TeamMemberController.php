<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\AkademieKurse;
use App\Entity\AuditTomAbteilung;
use App\Entity\AuditTomZiele;
use App\Entity\Team;
use App\Entity\User;
use App\Form\Type\AbteilungType;
use App\Form\Type\DsbType;
use App\Form\Type\NewMemberType;
use App\Form\Type\NewType;
use App\Form\Type\TeamType;
use App\Repository\AkademieKurseRepository;
use App\Repository\SettingsRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\InviteService;
use App\Service\SecurityService;
use App\Service\TeamService;
use App\Service\CurrentTeamService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TeamMemberController extends AbstractController
{
    /**
     * @Route("/team_mitglieder", name="team_mitglieder")
     * @param Request $request
     * @param InviteService $inviteService
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param TeamRepository $teamRepository
     * @param SettingsRepository $settingsRepository
     * @param SecurityService $securityService
     * @param CurrentTeamService $currentTeamService
     * @return Response
     */
    public function mitgliederAdd(Request $request,
                                  InviteService $inviteService,
                                  EntityManagerInterface $em,
                                  TranslatorInterface $translator,
                                  TeamRepository $teamRepository,
                                  SettingsRepository $settingsRepository,
                                  SecurityService $securityService,
                                  CurrentTeamService $currentTeamService
    ) : Response
    {
        $user = $this->getUser();
        $teamId = $request->get('id');
        $currentTeam = null;
        $settings = $settingsRepository->findOne();
        $useKeycloakGroups = $settings ? $settings->getUseKeycloakGroups() : false;

        if ($teamId) {
            $team = $teamRepository->find($teamId);
        } else {
            $team = $currentTeamService->getCurrentAdminTeam($user);
            $currentTeam = $team;
        }

        $temp = array_merge($team->getMembers()->toArray(), $team->getAdmins()->toArray());
        $members = [];
        foreach($temp as $member) {
            if (!in_array($member, $members)) {
                $members[] = $member;
            }
        }

        if ($securityService->adminCheck($user, $team) === false) {
            return $this->redirectToRoute('team_abteilungen');
        }

        $newMember = array();
        $form = $this->createForm(NewMemberType::class, $newMember);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $newMembers = $form->getData();
            $lines = explode("\n", $newMembers['member']);

            if (!empty($lines)) {
                foreach ($lines as $line) {
                    $newMember = trim($line);
                    $newUser = $inviteService->newUser($newMember);
                    $newUser->addTeam($team);
                    $em->persist($newUser);
                }
                $em->flush();
                if ($teamId) {
                    return $this->redirectToRoute('team_mitglieder', ['id' => $teamId]);
                }
                return $this->redirectToRoute('team_mitglieder');
            }
        }
        return $this->render('team/member.html.twig', [
            'currentTeam' => $currentTeam,
            'adminArea' => true,
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $translator->trans('manageUsers'),
            'team' => $team,
            'members' => $members,
            'useKeycloakGroups' => $useKeycloakGroups,
        ]);
    }

    /**
     * @Route("/team_mitglieder/create", name="team_mitglieder_create")
     * @param Request $request
     * @param SecurityService $securityService
     * @param InviteService $inviteService
     * @param EntityManagerInterface $em
     * @param TeamRepository $teamRepository
     * @param CurrentTeamService $currentTeamService
     * @return Response
     */
    public function teamMemberCreate(Request $request,
                                     SecurityService $securityService,
                                     InviteService $inviteService,
                                     EntityManagerInterface $em,
                                     TeamRepository $teamRepository,
                                     CurrentTeamService $currentTeamService
    ) : Response
    {
        $user = $this->getUser();
        $teamId = $request->get('id');
        $team = $teamId ? $teamRepository->find($teamId) : $currentTeamService->getCurrentAdminTeam($user);
        $target = $this->generateUrl('team_mitglieder');

        if ($securityService->adminCheck($user, $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $newMember = array();
        $form = $this->createForm(NewMemberType::class, $newMember);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newMembers = $form->getData();
            $lines = explode("\n", $newMembers['member']);

            if (!empty($lines)) {
                foreach ($lines as $line) {
                    $newMember = trim($line);
                    $user = $inviteService->newUser($newMember);

                    switch ($request->get('type')) {
                        case 'admin':
                            if (!$user->hasAdminRole($team)) {
                                $team->addAdmin($user);
                                $em->persist($team);
                            }
                            break;
                        case 'academy':
                            if ($user->getAkademieUser() === null) {
                                $user->setAkademieUser($team);
                                $em->persist($user);
                            }
                            $target = $this->generateUrl('akademie_admin') . '#user';
                            break;
                        default:
                            if (!$user->hasTeam($team)) {
                                $user->addTeam($team);
                                $em->persist($user);
                            }
                            if ($teamId) {
                                $target = $this->generateUrl('team_mitglieder', ['id' => $teamId]);
                            }
                    }
                }
            }
            $em->flush();
            return $this->redirect($target);
        }

        return $this->render('team/modalViewUser.html.twig', [
            'form' => $form->createView(),
            'teamId' => $teamId,
            'title' => $request->get('title'),
            'type' => $request->get('type')
        ]);
    }

    /**
     * @Route("/team_mitglieder/remove", name="team_mitglieder_remove")
     * @param Request $request
     * @param SecurityService $securityService
     * @param TeamRepository $teamRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @param CurrentTeamService $currentTeamService
     * @return Response
     */
    public function mitgliederRemove(Request $request,
                                     SecurityService $securityService,
                                     TeamRepository $teamRepository,
                                     UserRepository $userRepository,
                                     EntityManagerInterface $em,
                                     CurrentTeamService $currentTeamService
    ) : Response
    {
        $user = $this->getUser();
        $teamId = $request->get('teamId');
        $team = $teamId ? $teamRepository->find($teamId) : $currentTeamService->getCurrentAdminTeam($user);

        if ($securityService->adminCheck($user, $team) === false) {
            return $this->redirectToRoute('team_mitglieder');
        }

        $member = $userRepository->findOneBy(array('id' => $request->get('memberId')));

        switch ($request->get('type')) {
            case 'academy':
                $member->setAkademieUser(null);
                $target = $this->generateUrl('akademie_admin') . '#user';
                break;
            default:
                if ($member !== $user && $member->hasTeam($team)) {
                    $member->removeTeam($team);
                    $team->removeAdmin($member);
                }
                if ($teamId) {
                    $target = $this->generateUrl('team_mitglieder', ['id' => $teamId]);
                } else {
                    $target = $this->generateUrl('team_mitglieder');
                }
        }

        $em->persist($member);
        $em->persist($team);
        $em->flush();
        return $this->redirect($target);
    }

    /**
     * @Route("/team_mitglieder/admin", name="team_mitglieder_admin")
     * @param Request $request
     * @param SecurityService $securityService
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @param TeamRepository $teamRepository
     * @param CurrentTeamService $currentTeamService
     * @return Response
     */
    public function adminToggle(Request $request,
                                SecurityService $securityService,
                                UserRepository $userRepository,
                                EntityManagerInterface $em,
                                TeamRepository $teamRepository,
                                CurrentTeamService $currentTeamService
    ) : Response
    {
        $user = $this->getUser();
        $teamId = $request->get('teamId');
        $team = $teamId ? $teamRepository->find($teamId) : $currentTeamService->getCurrentAdminTeam($user);

        if ($securityService->adminCheck($user, $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $member = $userRepository->findOneBy(array('id' => $request->get('memberId')));

        // Only superadmins can promote themselves
        if (in_array('ROLE_ADMIN', $user->getRoles()) || ($user !== $member && $member->hasTeam($team))) {
            if ($member->hasAdminRole($team)) {
                $team->removeAdmin($member);
            } else {
                $team->addAdmin($member);
            }
        }

        $em->persist($team);
        $em->flush();

        if ($teamId) {
            return $this->redirectToRoute('team_mitglieder', ['id' => $teamId]);
        }
        return $this->redirectToRoute('team_mitglieder');
    }

    /**
     * @Route("/akademie/admin", name="akademie_admin")
     * @param SecurityService $securityService
     * @param CurrentTeamService $currentTeamService
     * @param AkademieKurseRepository $academyCourseRepository
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function academyAdmin(SecurityService $securityService,
                                 CurrentTeamService $currentTeamService,
                                 AkademieKurseRepository $academyCourseRepository,
                                 TranslatorInterface $translator
    ) : Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);

        // Admin Route only
        if (!$securityService->adminCheck($user, $team)) {
            return $this->redirectToRoute('dashboard');
        }
        $kurse = $academyCourseRepository->findKurseByTeam($team);

        return $this->render('team/academy.html.twig', [
            'currentTeam' => $team,
            'title' => $translator->trans('manageAcademy'),
            'team' => $team,
            'data' => $team->getAkademieUsers(),
            'kurse' => $kurse,
        ]);
    }

    /**
     * @Route("/ext_team_dsb", name="team_dsb")
     * @param Request $request
     * @param InviteService $inviteService
     * @param SecurityService $securityService
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @param CurrentTeamService $currentTeamService
     * @return Response
     */
    public function dsbAdd(Request $request,
                           InviteService $inviteService,
                           SecurityService $securityService,
                           EntityManagerInterface $em,
                           UserRepository $userRepository,
                           TranslatorInterface $translator,
                           CurrentTeamService $currentTeamService
    ) : Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentAdminTeam($user);

        if (!$securityService->adminCheck($user, $team)) {
            return $this->redirectToRoute('dashboard');
        }

        $form = $this->createForm(DsbType::class);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            $dsb = $form->getData();
            $email = $dsb['dsb'];
            $user = $userRepository->findOneBy(array('email' => $email));
            if (!$user) {
                $user = $inviteService->newUser($email, $team);
            }
            if (!$team->getDsbUser()) {
                $team->setDsbUser($user);
                $em->persist($team);
            }

            $em->flush();
            return $this->redirectToRoute('team_dsb', ['snack' => $translator->trans('dsbAdded')]);
        }
        return $this->render('team/dsb.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $translator->trans('manageExternalDsb'),
            'data' => $team->getDsbUser(),
            'snack' => $request->get('snack'),
            'currentTeam' => $team,
            'adminArea' => true,
        ]);
    }

    /**
     * @Route("/team_dsb/remove", name="team_dsb_remove")
     * @param Request $request
     * @param SecurityService $securityService
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $em
     * @param CurrentTeamService $currentTeamService
     * @return Response
     */
    public function dsbRemove(Request $request,
                              SecurityService $securityService,
                              UserRepository $userRepository,
                              TranslatorInterface $translator,
                              EntityManagerInterface $em,
                              CurrentTeamService $currentTeamService
    ) : Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);

        if ($securityService->adminCheck($user, $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $user = $userRepository->findOneBy(array('id' => $request->get('id')));

        if ($team->getDsbUser() === $user) {
            $snack = $translator->trans('cannotRemoveSelfExtDsb');
            if ($this->getUser() !== $team->getDsbUser()) {
                $user->removeTeam($team);
                $team->removeAdmin($user);
                $user->setAkademieUser(null);
                $snack = $translator->trans('extDsbRemoved');
            }
            $team->setDsbUser(null);
        }
        $em->persist($team);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('team_dsb', ['snack' => $snack]);
    }
}
