<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Team;
use Symfony\Component\Routing\RouterInterface;


class SecurityService
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;

    }

    function teamDataCheck($data, Team $team)
    {
        $this->teamCheck($team);

        //Sicherheitsfunktion, dass nur eigene Daten bearbeitet werden können
        if ($data->getTeam() !== $team) {
            return $this->router->generate('vvt');
        }
    }

    function teamCheck(Team $team)
    {
        //Sicherheitsfunktion, dass ein Team vorhanden ist
        if ($team === null) {
            return $this->router->generate('fos_user_security_logout');
        }
    }
}
