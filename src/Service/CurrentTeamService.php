<?php

namespace App\Service;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentTeamService
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param $team
     */
    public function switchToTeam($team) {
        $session = $this->requestStack->getSession();
        $session->set('team', $team);
    }

    /**
     * @param $teams
     * @return Team
     */
    private function findTeam($teams) {
        $session = $this->requestStack->getSession();
        $teamName = $session->get('team');

        if ($teamName) {
            foreach ($teams as $team) {
                if ($team->getName() === $teamName) {
                    return $team;
                }
            }
        }
        return $teams->get(0);
    }

    /**
     * @param User $user
     * @return Team
     */
    public function getTeamFromSession(User $user) {
        return $this->findTeam($user->getTeams());
    }

    /**
     * @param User $user
     * @return Team
     */
    public function getCurrentAdminTeam(User $user) {
        return $this->findTeam($user->getAdminRoles());
    }
}
