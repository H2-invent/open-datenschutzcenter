<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Datenweitergabe;
use App\Entity\Kontakte;
use App\Entity\Policies;
use App\Entity\Software;
use App\Entity\Team;
use App\Entity\Tom;
use App\Entity\User;
use App\Entity\VVT;
use App\Repository\DatenweitergabeRepository;
use App\Repository\KontakteRepository;
use App\Repository\PoliciesRepository;
use App\Repository\SoftwareRepository;
use App\Repository\TeamRepository;
use App\Repository\TomRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityService
{
    public function __construct(
        private readonly LoggerInterface           $logger,
        private readonly TranslatorInterface       $translator,
        private readonly TeamRepository            $teamRepository,
        private readonly DatenweitergabeRepository $transferRepository,
        private readonly TomRepository             $tomRepository,
        private readonly SoftwareRepository        $softwareRepository,
        private readonly KontakteRepository        $contactRepository,
        private readonly PoliciesRepository        $policyRepository,
    )
    {
    }

    public function adminCheck(User $user, Team $team): bool
    {
        if (!$this->teamCheck($team)) {
            return false;
        }

        // If user is super admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // If user has admin rights for this team
        if ($user->hasAdminRole($team)) {
            return true;
        }

        // Else
        $message = [
            'typ' => 'LOGIN',
            'error' => true,
            'hinweis' => $this->translator->trans(id: 'error.userIsNotTeamAdministrator', domain: 'general'),
        ];
        $this->logger->error($message['typ'], $message);
        return false;
    }

    public function superAdminCheck(User $user): bool
    {
        // If user is super admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // else
        $message = [
            'typ' => 'LOGIN',
            'error' => true,
            'hinweis' => $this->translator->trans(id: 'error.userNotAuthorizedToManageTeam', domain: 'general'),
        ];
        $this->logger->error($message['typ'], $message);
        return false;
    }

    public function teamArrayDataCheck($data, $team): bool
    {
        if (!$this->teamCheck($team)) {
            return false;
        }

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if (!in_array($team, $data->getTeam()->toarray())) {
            $message = [
                'typ' => 'LOGIN',
                'error' => true,
                'hinweis' => $this->translator->trans(id: 'error.userNotFoundInArray', domain: 'general'),
                'user' => $this->getUser()->getUsername()];
            $this->logger->error($message['typ'], $message);
            return false;
        }

        return true;
    }

    public function teamCheck($team): bool
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            $message = [
                'typ' => 'LOGIN',
                'error' => true,
                'hinweis' => $this->translator->trans(id: 'error.userWithoutTeam', domain: 'general'),
            ];
            $this->logger->error($message['typ'], $message);
            return false;
        }
        return true;
    }

    public function teamDataCheck($data, $team): bool
    {
        if (!$this->teamCheck($team)) {
            return false;
        }

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if ($team === $data->getTeam()) {
            $this->logAccessDenied($team);
        }

        return true;
    }

    public function checkTeamAccessToProcess(VVT $process, $team): bool
    {
        $teamPath = $team ? $this->teamRepository->getPath($team) : null;
        $processTeam = $process->getTeam();

        if ($processTeam === $team || in_array($processTeam, $teamPath) && $process->isInherited()) {
            return true;
        }

        $this->logAccessDenied($team);
        return false;
    }

    public function checkTeamAccessToTransfer(Datenweitergabe $transfer, Team $team): bool
    {
        if (in_array($transfer, $this->transferRepository->findActiveByTeam($team))) {
            return true;
        }

        $this->logAccessDenied($team);
        return false;
    }

    public function checkTeamAccessToTom(Tom $tom, Team $team): bool
    {
        if (in_array($tom, $this->tomRepository->findActiveByTeam($team))) {
            return true;
        }

        $this->logAccessDenied($team);
        return false;
    }

    public function checkTeamAccessToSoftware(Software $software, Team $team): bool
    {
        if (in_array($software, $this->softwareRepository->findActiveByTeam($team))) {
            return true;
        }

        $this->logAccessDenied($team);
        return false;
    }

    public function checkTeamAccessToContact(Kontakte $contact, Team $team): bool
    {
        if (in_array($contact, $this->contactRepository->findActiveByTeam($team))) {
            return true;
        }

        $this->logAccessDenied($team);
        return false;
    }

    public function checkTeamAccessToPolicy(Policies $policy, Team $team): bool
    {
        if (in_array($policy, $this->policyRepository->findActiveByTeam($team))) {
            return true;
        }

        $this->logAccessDenied($team);
        return false;
    }

    private function logAccessDenied(Team $team): void {
        $message = [
            'typ' => 'LOGIN',
            'error' => true,
            'hinweis' => $this->translator->trans(id: 'error.userNotInTeamAccessDenied', domain: 'general'),
            'team' => $team->getName(),
        ];
        $this->logger->error($message['typ'], $message);
    }
}
