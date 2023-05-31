<?php

namespace App\Service;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentTeamService
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getCurrentAdminTeam(User $user): Team
    {
        return $this->findTeam($user->getAdminRoles());
    }

    public function getTeamFromSession(User $user): ?Team
    {
        return $this->findTeam($user->getTeams());
    }

    public function switchToTeam($team): void
    {
        $session = $this->requestStack->getSession();
        $session->set('team', $team);
    }

    private function findTeam(Collection $teams): ?Team
    {
        $session = $this->requestStack->getSession();
        $id = $session->get('team');

        if ($id) {
            foreach ($teams as $team) {
                if (strval($team->getId()) === $id) {
                    return $team;
                }
            }
        }
        return $teams->get(0);
    }
}
