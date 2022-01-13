<?php

namespace App\Service;

use App\Entity\AuditTom;
use App\Entity\AuditTomZiele;
use App\Entity\Datenweitergabe;
use App\Entity\DatenweitergabeGrundlagen;
use App\Entity\DatenweitergabeStand;
use App\Entity\Policies;
use App\Entity\Team;
use App\Entity\Vorfall;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTGrundlage;
use App\Entity\VVTPersonen;
use App\Entity\VVTRisiken;
use App\Entity\VVTStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\Output;

class ConnectDefaultToTeamsService
{
    private $em;
    private $team;
    private $output;
    private $progress;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function connectDefault(Team $team, Output $output = null)
    {
        $this->team = $team;
        $this->output = $output;
        if ($output){
        $this->progress = new ProgressBar($this->output, 1);
        }else{
            $this->progress = null;
        }
        $this->personenGruppen();
        $this->risikoGruppen();
        $this->datenGruppen();
        $this->gesetzlicheGrundlagen();
        $this->status();
        $this->grundlagenDatenweitergabe();
        $this->standDatenweitergabe();
        $this->schutzZiele();
        $this->finishProgress();
    }

    private function personenGruppen()
    {
        $tmp = array();

        $personenGruppen = $this->em->getRepository(VVTPersonen::class)->findBy(array('team' => null));
        $this->increaseProgress(sizeof($personenGruppen));
        foreach ($personenGruppen as $data) {
            $pG = clone $data;
            $pG->setTeam($this->team)->setActiv(true);
            $this->em->persist($pG);
            $tmp[$data->getId()] = $pG;
            $data->setActiv(false);
            $this->em->persist($data);
            $this->advanceProgress();

        }
        $this->em->flush();

        $risiken = $this->em->getRepository(Policies::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($risiken));
        foreach ($risiken as $data) {
            foreach ($data->getPeople() as $data2) {
                if (isset($tmp[$data2->getId()])) {
                    $data->addPerson($tmp[$data2->getId()]);
                    $data->removePerson($data2);
                    $this->em->persist($data);
                }

            }
            $this->advanceProgress();
        }
        $this->em->flush();

        $vvt = $this->em->getRepository(VVT::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($vvt));
        foreach ($vvt as $data) {
            foreach ($data->getPersonengruppen() as $data2) {
                if (isset($tmp[$data2->getId()])) {
                    $data->addPersonengruppen($tmp[$data2->getId()]);
                    $data->removePersonengruppen($data2);
                    $this->em->persist($data);
                }
            }
            $this->advanceProgress();
        }
        $this->em->flush();

        $vorfall = $this->em->getRepository(Vorfall::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($vorfall));
        foreach ($vorfall as $data) {
            foreach ($data->getPersonen() as $data2) {
                if (isset($tmp[$data2->getId()])) {
                    $data->addPersonen($tmp[$data2->getId()]);
                    $data->removePersonen($data2);
                    $this->em->persist($data);
                }
            }
            $this->advanceProgress();
        }
        $this->em->flush();

    }


    private function risikoGruppen()
    {
        $tmp = array();
        $risikoGruppen = $this->em->getRepository(VVTRisiken::class)->findBy(array('team' => null));
        $this->increaseProgress(sizeof($risikoGruppen));
        foreach ($risikoGruppen as $data) {
            $rG = clone $data;
            $rG->setTeam($this->team)->setActiv(true);
            $this->em->persist($rG);
            $tmp[$data->getId()] = $rG;
            $data->setActiv(false);
            $this->em->persist($data);
            $this->advanceProgress();
        }
        $this->em->flush();
        $vvt = $this->em->getRepository(VVT::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($vvt));
        foreach ($vvt as $data) {
            foreach ($data->getRisiko() as $data2) {
                if (isset($tmp[$data2->getId()])) {
                    $data->addRisiko($tmp[$data2->getId()]);
                    $data->removeRisiko($data2);
                    $this->em->persist($data);
                }
            }
            $this->advanceProgress();
        }
        $this->em->flush();

    }

    private function datenGruppen()
    {
        $tmp = array();
        $datenkategorie = $this->em->getRepository(VVTDatenkategorie::class)->findBy(array('team' => null));
        $this->increaseProgress(sizeof($datenkategorie));
        foreach ($datenkategorie as $data) {
            $dK = clone $data;
            $dK->setTeam($this->team)->setActiv(true);
            $this->em->persist($dK);
            $tmp[$data->getId()] = $dK;
            $data->setActiv(false);
            $this->em->persist($data);
            $this->advanceProgress();
        }
        $this->em->flush();

        $risiken = $this->em->getRepository(Policies::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($risiken));

        foreach ($risiken as $data) {
            foreach ($data->getCategories() as $data2) {
                if (isset($tmp[$data2->getId()])) {
                    $data->addCategory($tmp[$data2->getId()]);
                    $data->removeCategory($data2);
                    $this->em->persist($data);
                }
            }
            $this->advanceProgress();
        }
        $this->em->flush();
        $vvt = $this->em->getRepository(VVT::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($vvt));
        foreach ($vvt as $data) {
            foreach ($data->getKategorien() as $data2) {
                if (isset($tmp[$data2->getId()])) {
                    $data->addKategorien($tmp[$data2->getId()]);
                    $data->removeKategorien($data2);
                    $this->em->persist($data);
                }
            }
            $this->advanceProgress();
        }
        $this->em->flush();
        $vorfall = $this->em->getRepository(Vorfall::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($vorfall));
        foreach ($vorfall as $data) {
            foreach ($data->getDaten() as $data2) {
                if (isset($tmp[$data2->getId()])) {
                    $data->addDaten($tmp[$data2->getId()]);
                    $data->removeDaten($data2);
                    $this->em->persist($data);
                }
            }
            $this->advanceProgress();
        }
        $this->em->flush();

    }

    private function gesetzlicheGrundlagen()
    {
        $tmp = array();
        $grundlagen = $this->em->getRepository(VVTGrundlage::class)->findBy(array('team' => null));
        $this->increaseProgress(sizeof($grundlagen));
        foreach ($grundlagen as $data) {
            $new = clone $data;
            $new->setTeam($this->team)->setActiv(true);
            $this->em->persist($new);
            $tmp[$data->getId()] = $new;
            $data->setActiv(false);
            $this->em->persist($data);
            $this->advanceProgress();
        }
        $this->em->flush();

        $vvt = $this->em->getRepository(VVT::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($vvt));
        foreach ($vvt as $data) {
            foreach ($data->getGrundlage() as $data2) {
                if (isset($tmp[$data2->getId()])) {
                    $data->addGrundlage($tmp[$data2->getId()]);
                    $data->removeGrundlage($data2);
                    $this->em->persist($data);
                }
            }
            $this->advanceProgress();
        }
        $this->em->flush();

    }

    private function status()
    {
        $tmp = array();
        $status = $this->em->getRepository(VVTStatus::class)->findBy(array('team' => null));
        $this->increaseProgress(sizeof($status));
        foreach ($status as $data) {
            $new = clone $data;
            $new->setTeam($this->team)->setActiv(true);
            $this->em->persist($new);
            $tmp[$data->getId()] = $new;
            $data->setActiv(false);
            $this->em->persist($data);
            $this->advanceProgress();
        }
        $this->em->flush();

        $vvt = $this->em->getRepository(VVT::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($vvt));
        foreach ($vvt as $data) {
            if (isset($tmp[$data->getStatus()->getId()])) {
                $data->setStatus($tmp[$data->getStatus()->getId()]);
                $this->em->persist($data);
            }
            $this->advanceProgress();
        }
        $this->em->flush();

    }

    private function grundlagenDatenweitergabe()
    {
        $tmp = array();
        $grundlagenDatenweitergabe = $this->em->getRepository(DatenweitergabeGrundlagen::class)->findBy(array('team' => null));
        $this->increaseProgress(sizeof($grundlagenDatenweitergabe));
        foreach ($grundlagenDatenweitergabe as $data) {
            $new = clone $data;
            $new->setTeam($this->team)->setActiv(true);
            $this->em->persist($new);
            $tmp[$data->getId()] = $new;
            $data->setActiv(false);
            $this->em->persist($data);
            $this->advanceProgress();
        }
        $this->em->flush();

        $datenweitergabe = $this->em->getRepository(Datenweitergabe::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($datenweitergabe));
        foreach ($datenweitergabe as $data) {
            if (isset($tmp[$data->getGrundlage()->getId()])) {
                $data->setGrundlage($tmp[$data->getGrundlage()->getId()]);
                $this->em->persist($data);
            }
            $this->advanceProgress();
        }
        $this->em->flush();

    }

    private function standDatenweitergabe()
    {
        $tmp = array();
        $defaultValue = $this->em->getRepository(DatenweitergabeStand::class)->findBy(array('team' => null));
        $this->increaseProgress(sizeof($defaultValue));
        foreach ($defaultValue as $data) {
            $new = clone $data;
            $new->setTeam($this->team)->setActiv(true);
            $this->em->persist($new);
            $tmp[$data->getId()] = $new;
            $data->setActiv(false);
            $this->em->persist($data);
            $this->advanceProgress();
        }
        $this->em->flush();
        $datenweitergabe = $this->em->getRepository(Datenweitergabe::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($datenweitergabe));
        foreach ($datenweitergabe as $data) {
            if (isset($tmp[$data->getStand()->getId()])) {
                $data->setStand($tmp[$data->getStand()->getId()]);
                $this->em->persist($data);
            }
            $this->advanceProgress();
        }
        $this->em->flush();

    }

    private function schutzZiele()
    {
        $tmp = array();
        $defaultValue = $this->em->getRepository(AuditTomZiele::class)->findBy(array('team' => null));
        $this->increaseProgress(sizeof($defaultValue));
        foreach ($defaultValue as $data) {
            $new = clone $data;
            $new->setTeam($this->team)->setActiv(true);
            $this->em->persist($new);
            $tmp[$data->getId()] = $new;
            $data->setActiv(false);
            $this->em->persist($data);
            $this->advanceProgress();
        }
        $this->em->flush();
        $auditTom = $this->em->getRepository(AuditTom::class)->findBy(array('team' => $this->team));
        $this->increaseProgress(sizeof($auditTom));
        foreach ($auditTom as $data) {
            foreach ($data->getZiele() as $data2) {
                if (isset($tmp[$data2->getId()])) {
                    $data->addZiele($tmp[$data2->getId()]);
                    $data->removeZiele($data2);
                    $this->em->persist($data);
                }
            }
            $this->advanceProgress();
        }
        $this->em->flush();

    }

    private function increaseProgress($steps)
    {
        if ($this->progress) {
            $this->progress->setMaxSteps($this->progress->getMaxSteps() + $steps);
        }
    }

    private function advanceProgress()
    {
        if ($this->progress) {
            $this->progress->advance();
        }
    }
    private function finishProgress(){
        if ($this->progress){
            $this->progress->finish();
        }
    }
}