<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


class SecurityService
{
    function teamArrayDataCheck($data, $team)
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

    function teamDataCheck($data, $team)
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            return false;
        }

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if ($team !== $data->getTeam()) {
            return false;
        }

        return true;
    }

    function teamCheck($team)
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            return false;
        }
        return true;
    }
}
