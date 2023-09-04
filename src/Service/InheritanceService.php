<?php
namespace App\Service;

use App\Entity\Datenweitergabe;
use App\Entity\Kontakte;
use App\Entity\Policies;
use App\Entity\Software;
use App\Entity\Team;
use App\Entity\Tom;
use App\Repository\KontakteRepository;
use App\Repository\SoftwareRepository;
use Doctrine\Common\Collections\Collection;

class InheritanceService
{
    public function __construct(
        private readonly SoftwareRepository $softwareRepository,
        private readonly KontakteRepository $contactRepository,
    )
    {

    }

    public function checkSoftwareIsInherited(Software $software): bool
    {
        return  $this->softwareRepository->findIsInheritedById($software->getId());
    }

    public function checkTeamUsesSoftware(Team $team, Software $software): bool
    {
        return $this->softwareRepository->findIsUsedByTeamAndId($team, $software->getId());
    }

    public function checkTomIsInherited(Tom $tom): bool
    {
        return $this->isInheritedProcessInCollection(processes: $tom->getVvts());
    }

    public function checkTeamUsesTom(Team $team, Tom $tom): bool
    {
        return $this->isUsedByTeamInCollection(team: $team, processes: $tom->getVvts());
    }

    public function checkPolicyIsInherited(Policies $policy): bool
    {
        return $this->isInheritedProcessInCollection(processes: $policy->getProcesses());
    }

    public function checkTeamUsesPolicy(Team $team, Policies $policy): bool
    {
        return $this->isUsedByTeamInCollection(team: $team, processes: $policy->getProcesses());
    }

    public function checkContactIsInherited(Kontakte $contact): bool
    {
        if ($contact->getId()) {
            return $this->contactRepository->findIsInheritedById($contact->getId());
        }
        return false;
    }

    public function checkTeamUsesContact(Team $team, Kontakte $contact): bool
    {
        if ($contact->getId()) {
            return $this->contactRepository->findIsUsedByTeamAndId($team, $contact->getId());
        }
        return false;
    }

    public function checkTransferIsInherited(Datenweitergabe $transfer): bool
    {
        return $this->isInheritedProcessInCollection(processes: $transfer->getVerfahren());
    }

    public function checkTeamUsesTransfer(Team $team, Datenweitergabe $transfer): bool
    {
        return $this->isUsedByTeamInCollection(team: $team, processes: $transfer->getVerfahren());
    }

    // if there is a process in this collection which is inherited, return true
    private function isInheritedProcessInCollection(Collection $processes): bool
    {
        foreach($processes as $process) {
            if ($process->getActiv() && $process->isInherited()) {
                return true;
            }
        }
        return false;
    }

    // if there is a process in the collection which is not ignored, return true
    private function isUsedByTeamInCollection(Team $team, Collection $processes): bool
    {
        foreach($processes as $process) {
            if ($process->getActiv() && !$team->getIgnoredInheritances()->contains($process)) {
                return true;
            }
        }
        return false;
    }
}
