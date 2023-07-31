<?php

namespace App\Service;

use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
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

    // only available for superadmins
    public function getTeamsWithoutCurrentHierarchy($user, $current = null): ?array
    {
        if ($this->securityService->superAdminCheck($user)) {
            $availableTeams = $this->teamRepository->findAll();
            $currentAndDescendants = $this->teamRepository->getChildren(node: $current, includeNode: true);
            $result = [];

            if ($currentAndDescendants) {
                for ($i = 0; $i < count($currentAndDescendants); $i++) {
                    var_dump($currentAndDescendants[$i]->getName());
                }
                for ($i = 0; $i < count($availableTeams); $i++) {
                    if (array_search($availableTeams[$i], $currentAndDescendants) === false) {
                        $result[] = $availableTeams[$i];
                    }
                }
            }

            return $result;
        } return null;
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
}
