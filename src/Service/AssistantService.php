<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Datenweitergabe;
use App\Entity\Kontakte;
use App\Entity\Software;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVT;
use App\Form\Type\DatenweitergabeType;
use App\Form\Type\KontaktType;
use App\Form\Type\SoftwareType;
use App\Form\Type\VVTType;
use App\Repository\KontakteRepository;
use App\Repository\SoftwareRepository;
use App\Repository\VVTRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssistantService
{
    // TODO: Move to separate config file
    private static array $STEPS = [
        0 => [
            'type' => SoftwareType::class,
            'title' => 'software.processing.title',
            'info' => 'software.processing.info',
            'newTitle' => 'new.software',
            'skip' => true,
        ],
        1 => [
            'type' => VVTType::class,
            'title' => 'procedure.processing.title',
            'info' => 'procedure.processing.info',
            'newTitle' => 'new.procedure',
            'software' => 0,
        ],
        2 => [
            'type' => KontaktType::class,
            'title' => 'contact.source.title',
            'info' => 'contact.source.info',
            'newTitle' => 'new.contact',
        ],
        3 => [
            'type' => DatenweitergabeType::class,
            'title' => 'processing.title',
            'info' => 'processing.info',
            'newTitle' => 'new.processing',
            'transferType' => 2,
            'contact' => 0,
            'procedure' => 1,
            'software' => 0,
        ],
        4 => [
            'type' => SoftwareType::class,
            'title' => 'software.transfer.title',
            'info' => 'software.transfer.info',
            'newTitle' => 'new.software',
            'skip' => true,
        ],
        5 => [
            'type' => VVTType::class,
            'title' => 'procedure.transfer.title',
            'info' => 'procedure.transfer.info',
            'newTitle' => 'new.procedure',
            'software' => 4,
        ],
        6 => [
            'type' => KontaktType::class,
            'title' => 'contact.dest.title',
            'info' => 'contact.dest.info',
            'newTitle' => 'new.contact',
        ],
        7 => [
            'type' => DatenweitergabeType::class,
            'title' => 'transfer.title',
            'info' => 'transfer.info',
            'newTitle' => 'new.transfer',
            'transferType' => 1,
            'procedure' => 5,
            'contact' => 6,
            'software' => 4,
        ],
    ];

    const PROPERTY_TYPE = 'type';
    const PROPERTY_TITLE = 'title';
    const PROPERTY_INFO = 'info';
    const PROPERTY_NEW = 'newTitle';
    const PROPERTY_CONTACT = 'contact';
    const PROPERTY_PROCEDURE = 'procedure';
    const PROPERTY_SOFTWARE = 'software';
    const PROPERTY_TRANSFER_TYPE = 'transferType';
    const PROPERTY_SKIP = 'skip';

    public function __construct(private readonly RequestStack           $requestStack,
                                private readonly DatenweitergabeService $dataTransferService,
                                private readonly SoftwareService        $softwareService,
                                private readonly VVTService             $vvtService,
                                private readonly KontakteRepository     $contactRepository,
                                private readonly SoftwareRepository     $softwareRepository,
                                private readonly VVTRepository          $vvtRepository,
                                private readonly TranslatorInterface    $translator,
                                private readonly FormFactoryInterface   $formBuilder
    )
    {

    }

    public function saveToSession(int $step, string $id): void
    {
        $session = $this->requestStack->getSession();
        $key = 'step_' . $step;
        $session->set($key, $id);
    }

    public function getFromSession(int $step): ?string {
        $session = $this->requestStack->getSession();
        $key = 'step_' . $step;
        return $session->get($key);
    }

    public function getPropertyForStep(int $step, string $key): int|string|bool|Kontakte|VVT|Software|null {
        $steps =  AssistantService::$STEPS;
        if (array_key_exists($step, $steps) && array_key_exists($key, $steps[$step])) {
            $value = $steps[$step][$key];
            return match($key) {
                self::PROPERTY_CONTACT => $this->getContactFromStep($value),
                self::PROPERTY_PROCEDURE => $this->getProcedureFromStep($value),
                self::PROPERTY_SOFTWARE => $this->getSoftwareFromStep($value),
                self::PROPERTY_TITLE, self::PROPERTY_INFO, self::PROPERTY_NEW
                    => $this->translator->trans(id: $value, domain: 'assistant'),
                default => $value
            };
        }
        return null;
    }

    private function getContactFromStep(int $step): ?Kontakte {
        $contactId = $this->getFromSession($step);
        return $contactId ? $this->contactRepository->find($contactId): null;
    }

    private function getProcedureFromStep(int $step): ?VVT {
            $procedureId = $this->getFromSession($step);
            return $procedureId ? $this->vvtRepository->find($procedureId): null;
    }

    private function getSoftwareFromStep(int $step): ?Software {
        $softwareId = $this->getFromSession($step);
        return $softwareId ? $this->softwareRepository->find($softwareId): null;
    }

    public function getStepCount(): int {
        return count(AssistantService::$STEPS);
    }

    public function setStep(int $step): void {
        $session = $this->requestStack->getSession();
        $session->set('step', $step);
    }

    public function getStep(): int {
        $session = $this->requestStack->getSession();
        $id = $session->get('step');
        return $id ? : 0;
    }

    public function clear(): void {
        $session = $this->requestStack->getSession();
        $session->set('step', 0);

        for ($i = 0; $i < count(AssistantService::$STEPS); $i++) {
            $session->remove('step_' . $i);
        }
    }

    public function createElementForStep(int $step, User $user, Team $team): Software|Kontakte|VVT|Datenweitergabe|null
    {
        $item = null;

        switch ($this->getPropertyForStep($step, self::PROPERTY_TYPE)) {
            case SoftwareType::class:
                $item = $this->softwareService->newSoftware($team, $user);
                break;
            case KontaktType::class:
                $item = new Kontakte();
                $item->setTeam($team);
                $item->setActiv(1);
                break;
            case DatenweitergabeType::class:
                if (array_key_exists(self::PROPERTY_TRANSFER_TYPE, AssistantService::$STEPS[$step])) {
                    $item = $this->dataTransferService->newDatenweitergabe($user, 2);
                } else {
                    $item = $this->dataTransferService->newDatenweitergabe($user, 1);
                }
                $this->addDependenciesToDatenweitergabe($item, $step);
                break;
            case VVTType::class:
                $item = $this->vvtService->newVvt($team, $user);
                $this->addDependenciesToProcedure($item, $step);
        }
        return $item;
    }

    public function getSelectDataForStep(int $step, Team $team): array {
        $select = [];
        switch ($this->getPropertyForStep($step, 'type')) {
            case KontaktType::class:
                $select['selected'] = $this->getFromSession($step);
                $select['label'] = $this->translator->trans(id: 'select.contact', domain: 'assistant');
                $select['items'] = $this->contactRepository->findActiveByTeam($team);
                $select['multiple'] = false;
                break;
            case SoftwareType::class:
                $select['selected'] = $this->getFromSession($step);
                $select['label'] = $this->translator->trans(id: 'select.software', domain: 'assistant');
                $select['items'] = $this->softwareRepository->findActiveByTeam($team);
                $select['multiple'] = true;
                break;
            case VVTType::class:
                $select['selected'] = $this->getFromSession($step);
                $select['label'] = $this->translator->trans(id: 'select.procedure', domain: 'assistant');
                $select['items'] = $this->vvtRepository->findActiveByTeam($team);
                $select['multiple'] = true;
                break;
        }
        return $select;
    }

    public function createForm($type, $newItem, Team $team) {
        return match ($type) {
            DatenweitergabeType::class => $this->dataTransferService->createForm($newItem, $team),
            VVTType::class => $this->vvtService->createForm($newItem, $team),
            default => $this->formBuilder->create($type, $newItem)
        };
    }

    private function addDependenciesToDatenweitergabe(Datenweitergabe $item, $step): void {
        $contact = $this->getPropertyForStep(step: $step, key: self::PROPERTY_CONTACT);
        $item->setKontakt($contact);
        $software = $this->getPropertyForStep(step: $step, key: self::PROPERTY_SOFTWARE);
        if ($software) {
            $item->addSoftware($software);
        }
        $procedure = $this->getPropertyForStep(step: $step, key: self::PROPERTY_PROCEDURE);
        $item->addVerfahren($procedure);
    }

    private function addDependenciesToProcedure(VVT $procedure, $step): void {
        $software = $this->getPropertyForStep(step: $step, key: self::PROPERTY_SOFTWARE);
        if ($software) {
            $procedure->addSoftware($software);
        }
    }
}
