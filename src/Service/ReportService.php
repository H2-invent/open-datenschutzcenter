<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Report;
use App\Entity\Team;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;


class ReportService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    function newReport(Team $team)
    {
        $report = new Report();
        $report->setTeam($team);
        $report->setDate(new DateTime());
        $report->setActiv(true);
        $report->setCreatedAt(new DateTime());

        return $report;
    }
}
