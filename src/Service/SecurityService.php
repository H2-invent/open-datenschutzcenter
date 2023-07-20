<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Team;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityService
{
    private $logger;

    public function __construct(LoggerInterface $logger, private TranslatorInterface $translator)
    {
        $this->logger = $logger;
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

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if ($team !== $data->getTeam()) {
            $message = [
                'typ' => 'LOGIN',
                'error' => true,
                'hinweis' => $this->translator->trans(id: 'error.userNotInTeamAccessDenied', domain: 'general'),
                'team' => $team->getName(),
            ];
            $this->logger->error($message['typ'], $message);
            return false;
        }

        return true;
    }

    /**
     * @param $data
     * @param Team[]|null $teamPath
     * @return bool
     */
    public function teamPathDataCheck($data, ?array $teamPath): bool
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if (!$teamPath) {
            $message = [
                'typ' => 'LOGIN',
                'error' => true,
                'hinweis' => $this->translator->trans(id: 'error.userWithoutTeam', domain: 'general'),
            ];
            $this->logger->error($message['typ'], $message);
            return false;
        }

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if (!in_array($data->getTeam(), $teamPath)) {
            $message = [
                'typ' => 'LOGIN',
                'error' => true,
                'hinweis' => $this->translator->trans(id: 'error.userNotInTeamAccessDenied', domain: 'general'),
                'team' => $teamPath,
            ];
            $this->logger->error($message['typ'], $message);
            return false;
        }

        return true;
    }
}
