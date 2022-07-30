<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AkademieBuchungen;
use App\Entity\AkademieKurse;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;


class AkademieService
{
    private EntityManagerInterface $em;
    private Environment $twig;
    private NotificationService $notificationService;
    private CurrentTeamService $currentTeamService;

    public function __construct(Environment $engine, EntityManagerInterface $entityManager, NotificationService $notificationService, CurrentTeamService $currentTeamService)
    {
        $this->em = $entityManager;
        $this->twig = $engine;
        $this->notificationService = $notificationService;
        $this->currentTeamService = $currentTeamService;
    }

    function addUser(AkademieKurse $kurs, $daten)
    {

        $buchung = new AkademieBuchungen();
        $buchung->setKurs($kurs);
        $buchung->setAbgeschlossen(false);
        $buchung->setVorlage($daten['wiedervorlage']);
        $buchung->setZugewiesen($daten['zugewiesen']);
        $buchung->setInvitation(false);

        foreach ($daten['user'] as $user) {
            $buchung->setUser($user);
            $this->em->persist($buchung);
            $this->em->flush();
            if ($daten['invite'] === true) {
                $team = $this->currentTeamService->getTeamFromSession($user);
                $content = $this->twig->render('email/neuerKurs.html.twig', ['buchung' => $buchung, 'team' => $team]);
                $buchung->setInvitation(true);
                $this->notificationService->sendNotificationAkademie($buchung, $content);
            }
            $this->em->persist($buchung);
        }
        $this->em->flush();

    }

    function removeKurs(Team $team, AkademieKurse $kurs)
    {
        $buchungen = $this->em->getRepository(AkademieBuchungen::class)->findBuchungenByTeam($team, $kurs);

        if (in_array($team, $kurs->getTeam()->toarray())) {
            $kurs->removeTeam($team);
            foreach ($buchungen as $buchung) {
                $this->em->remove($buchung);
            }
            $this->em->flush();
        }
        return true;
    }
}
