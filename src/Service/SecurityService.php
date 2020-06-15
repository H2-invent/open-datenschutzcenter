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


class SecurityService
{

    function userDataCheck($data, Team $team, User $user)
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            return false;
        }

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if ($data->getUser() !== $user) {
            return false;
        }
        return true;
    }

    function teamDataCheck($data, Team $team)
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            return false;
        }

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if (!in_array($team, $data->getTeam()->toarray())) {
            return false;
        }

        return true;
    }

    function teamCheck(Team $team)
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            return false;
        }
        return true;
    }
}
