<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentTeamService
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function switchToTeam($team) {
        $session = $this->requestStack->getSession();
        $session->set('team', $team);
    }

    public function getTeamFromSession(User $user) {
        $session = $this->requestStack->getSession();
        $teamName = $session->get('team');
        $teams = $user -> getTeams();

        if ($teamName) {
            foreach ($teams as $team) {
                if ($team->getName() === $teamName) {
                    return $team;
                }
            }
        }

        return $teams->get(0) ;
    }
}
