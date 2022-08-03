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

    /**
     * return team in session if user is admin, otherwise return team for which user is admin
     */
    public function getCurrentAdminTeam(User $user) {
        $team = $this->getTeamFromSession($user);
        if ($user->hasAdminRole($team)) {
            return $team;
        }

        $team = $user->getAdminRoles() ? $user->getAdminRoles()->get(0) : null;
        if ($team) {
            $this->switchToTeam($team->getName());
        }
        return $team;
    }
}
