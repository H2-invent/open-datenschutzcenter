<?php

namespace App\Twig;

use App\Entity\Datenweitergabe;
use App\Entity\Kontakte;
use App\Entity\Policies;
use App\Entity\Software;
use App\Entity\Team;
use App\Entity\Tom;
use App\Service\InheritanceService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class InheritanceExtension extends AbstractExtension
{
    public function __construct(
        private readonly InheritanceService $inheritanceService
    )
    {

    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('softwareInherited', [$this, 'checkSoftwareIsInherited']),
            new TwigFunction('teamUsesSoftware', [$this, 'checkTeamUsesSoftware']),
            new TwigFunction('tomInherited', [$this, 'checkTomIsInherited']),
            new TwigFunction('teamUsesTom', [$this, 'checkTeamUsesTom']),
            new TwigFunction('policyInherited', [$this, 'checkPolicyIsInherited']),
            new TwigFunction('teamUsesPolicy', [$this, 'checkTeamUsesPolicy']),
            new TwigFunction('contactInherited', [$this, 'checkContactIsInherited']),
            new TwigFunction('teamUsesContact', [$this, 'checkTeamUsesContact']),
            new TwigFunction('transferInherited', [$this, 'checkTransferIsInherited']),
            new TwigFunction('teamUsesTransfer', [$this, 'checkTeamUsesTransfer']),
        ];
    }

    public function checkSoftwareIsInherited(Software $software): bool
    {
        return $this->inheritanceService->checkSoftwareIsInherited(software: $software);
    }

    public function checkTeamUsesSoftware(Team $team, Software $software): bool
    {
        return $this->inheritanceService->checkTeamUsesSoftware(team: $team, software: $software);
    }

    public function checkTomIsInherited(Tom $tom): bool
    {
        return $this->inheritanceService->checkTomIsInherited(tom: $tom);
    }

    public function checkTeamUsesTom(Team $team, Tom $tom): bool
    {
        return $this->inheritanceService->checkTeamUsesTom(team: $team, tom: $tom);
    }

    public function checkPolicyIsInherited(Policies $policy): bool
    {
        return $this->inheritanceService->checkPolicyIsInherited(policy: $policy);
    }

    public function checkTeamUsesPolicy(Team $team, Policies $policy): bool
    {
        return $this->inheritanceService->checkTeamUsesPolicy(team: $team, policy: $policy);
    }

    public function checkContactIsInherited(Kontakte $contact): bool
    {
        return $this->inheritanceService->checkContactIsInherited(contact: $contact);
    }

    public function checkTeamUsesContact(Team $team, Kontakte $contact): bool
    {
        return $this->inheritanceService->checkTeamUsesContact(team: $team, contact: $contact);
    }

    public function checkTransferIsInherited(Datenweitergabe $transfer): bool
    {
        return $this->inheritanceService->checkTransferIsInherited(transfer: $transfer);
    }

    public function checkTeamUsesTransfer(Team $team, Datenweitergabe $transfer): bool
    {
        return $this->inheritanceService->checkTeamUsesTransfer(team: $team, transfer: $transfer);
    }
}
