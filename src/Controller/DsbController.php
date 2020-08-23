<?php

namespace App\Controller;

use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DsbController extends AbstractController
{
    /**
     * @Route("/ext-dsb", name="dsb")
     */
    public function index()
    {
        $dsbTeams = $this->getUser()->getTeamDsb();
        if (count($dsbTeams) < 1) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dsb/index.html.twig', [
            'teams' => $dsbTeams,
        ]);
    }

    /**
     * @Route("/ext-dsb/change", name="dsb_change")
     */
    public function change(Request $request)
    {
        $team = $this->getDoctrine()->getRepository(Team::class)->find($request->get('id'));

        if (in_array($team, $this->getUser()->getTeamDsb()->toarray())) {
            $user = $this->getUser();
            $user->setTeam($team);
            $user->setAdminUser($team);
            $user->setAkademieUser($team);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('dashboard', ['snack' => 'Team gewächselt']);
        }
        return $this->redirectToRoute('dashboard', ['snack' => 'DSB passt nicht zu diesem Team. Benutzer kann das Team nicht wechseln.']);
    }
}
