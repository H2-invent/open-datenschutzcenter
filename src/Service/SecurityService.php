<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use Psr\Log\LoggerInterface;
use App\Entity\User;
use App\Entity\Team;

class SecurityService
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    function teamArrayDataCheck($data, $team)
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            $message = ['typ' => 'LOGIN', 'error' => true, 'hinweis' => 'Benutzer keinem Team zugewiesen'];
            $this->logger->error($message['typ'], $message);
            return false;
        }

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if (!in_array($team, $data->getTeam()->toarray())) {
            $message = ['typ' => 'LOGIN', 'error' => true, 'hinweis' => 'Benutzer nicht in Array von Teams', 'user' => $this->getUser()->getUsername()];
            $this->logger->error($message['typ'], $message);
            return false;
        }

        return true;
    }

    function teamDataCheck($data, $team)
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            $message = ['typ' => 'LOGIN', 'error' => true, 'hinweis' => 'Benutzer keinem Team zugewiesen'];
            $this->logger->error($message['typ'], $message);
            return false;
        }

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if ($team !== $data->getTeam()) {
            $message = ['typ' => 'LOGIN', 'error' => true, 'hinweis' => 'Benutzer nicht in Team und nicht berechtigt', 'team' => $team->getName()];
            $this->logger->error($message['typ'], $message);
            return false;
        }

        return true;
    }

    function teamCheck($team)
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            $message = ['typ' => 'LOGIN', 'error' => true, 'hinweis' => 'Benutzer*in ist keinem Team zugewiesen'];
            $this->logger->error($message['typ'], $message);
            return false;
        }
        return true;
    }

    function adminCheck(User $user, Team $team)
    {
        if (!$this->teamCheck($team)) {
            return false;
        }

        // Falls Nutzer*in im Team ist UND Adminrechte für dieses Team hat
        if ($user->hasTeam($team) && $user->hasAdminRole($team)) {
            return true;
        }

        // Sonst
        $message = ['typ' => 'LOGIN', 'error' => true, 'hinweis' => 'Benutzer*in ist nicht Admin dieses Teams'];
        $this->logger->error($message['typ'], $message);
        return false;
    }
}
