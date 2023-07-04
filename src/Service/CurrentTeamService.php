<?php

namespace App\Service;

use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentTeamService
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly SecurityService $securityService,
        private readonly TeamRepository $teamRepository
    )
    {

    }

    public function getCurrentAdminTeam(User $user): ?Team
    {
        return $this->getCurrentTeamFromArray($user->getAdminRoles());
    }

    public function getCurrentTeam(User $user): ?Team
    {
        return $this->getCurrentTeamFromArray(availableTeams: $user->getTeams());
    }

    public function switchToTeam(string $team): void
    {
        $session = $this->requestStack->getSession();
        $session->set('team', $team);
    }

    public function getCurrentAndChildren($user, $current = null) : ?array {
        $current ?: $this->getTeamFromSession(availableTeams: $user->getTeams());
        if ($current) {
            $children = $current->getChildren();
            $teams = [$current];
            if ($children) {
                $teams = array_merge($teams, $children->toArray());
            }
            return $teams;
        }
        return null;
    }

    public function getAvailableWithoutCurrentAndChildren($user, $current = null): array
    {
        $availableTeams = $this->getAvailableTeamsForUser($user);
        $currentTeams = $this->getCurrentAndChildren($user, $current);
        $result = [];

        if ($currentTeams) {
            for ($i = 0; $i < count($availableTeams); $i++) {
                if (array_search($availableTeams[$i], $currentTeams) === false) {
                    $result[] = $availableTeams[$i];
                }
            }
        }

        return $result;
    }

    private function getCurrentTeamFromArray($availableTeams) {
        if (!count($availableTeams)){
            return  null;
        }

        $team = $this->getTeamFromSession(availableTeams: $availableTeams);

        if (!$team) {
            $team =  $availableTeams->get(0);
            $this->switchToTeam($team);
        }

        return $team;
    }

    private function getTeamFromSession(Collection $availableTeams): ?Team
    {
        $session = $this->requestStack->getSession();
        $id = $session->get('team');

        if ($id) {
            foreach ($availableTeams as $team) {
                if (strval($team->getId()) === $id) {
                    return $team;
                }
            }
        }

        return null;
    }

    private function getAvailableTeamsForUser($user) {
        if ($this->securityService->superAdminCheck($user)) {
            $teams = $this->teamRepository->findAll();
        } else {
            $teams = $user->getTeams();
        }
        return $teams;
    }
}
