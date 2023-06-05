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
use App\Repository\DatenweitergabeRepository;
use App\Repository\KontakteRepository;
use App\Repository\SoftwareRepository;
use App\Repository\VVTRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssistantService
{
    const PROPERTY_TYPE = 'type';
    const PROPERTY_TITLE = 'title';
    const PROPERTY_INFO = 'info';
    const PROPERTY_NEW = 'newTitle';
    const PROPERTY_CONTACT = 'contact';
    const PROPERTY_PROCEDURE = 'procedure';
    const PROPERTY_SOFTWARE = 'software';
    const PROPERTY_TRANSFER_TYPE = 'transferType';
    const PROPERTY_SKIP = 'skip';

    private array $steps = [];

    public function __construct(private readonly RequestStack              $requestStack,
                                private readonly DatenweitergabeService    $dataTransferService,
                                private readonly SoftwareService           $softwareService,
                                private readonly VVTService                $vvtService,
                                private readonly KontakteRepository        $contactRepository,
                                private readonly SoftwareRepository        $softwareRepository,
                                private readonly DatenweitergabeRepository $datenweitergabeRepository,
                                private readonly VVTRepository             $vvtRepository,
                                private readonly TranslatorInterface       $translator,
                                private readonly FormFactoryInterface      $formBuilder
    )
    {
        $configDirectories = [__DIR__.'/../../config'];
        $fileLocator = new FileLocator($configDirectories);
        $stepsFile = $fileLocator->locate('assistant.yaml', null, false)[0];
        $values = Yaml::parse(file_get_contents($stepsFile));
        if (array_key_exists('steps', $values)) {
            $this->steps = $values['steps'];
        }
    }

    public function saveToSession(int $step, array|string|int $data): void
    {
        if (!is_array($data)) {
            $data = [$data];
        }
        $session = $this->requestStack->getSession();
        $key = 'step_' . $step;
        $session->set($key, serialize($data));
    }

    public function getPropertyForStep(int $step, string $key): int|string|bool|Kontakte|VVT|ArrayCollection|null {
        if (array_key_exists($step, $this->steps) && array_key_exists($key, $this->steps[$step])) {
            $value = $this->steps[$step][$key];
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

    private function getArrayFromSessionForStep(int $step): ?array {
        $session = $this->requestStack->getSession();
        $key = 'step_' . $step;
        return $session->get($key) ? unserialize($session->get($key)) : null;
    }

    private function getSingleFromSessionForStep(int $step): ?string {
        $array = $this->getArrayFromSessionForStep(step: $step);
        return $array ? strval($array[0]) : null;
    }

    private function getContactFromStep(int $step): ?Kontakte {
        $contactId = $this->getSingleFromSessionForStep($step);
        return $contactId ? $this->contactRepository->find($contactId): null;
    }

    private function getProcedureFromStep(int $step): ?VVT {
            $procedureId = $this->getSingleFromSessionForStep($step);
            return $procedureId ? $this->vvtRepository->find($procedureId): null;
    }

    private function getDatenweitergabeFromStep(int $step): ?Datenweitergabe {
        $id = $this->getSingleFromSessionForStep($step);
        return $id ? $this->datenweitergabeRepository->find($id): null;
    }

    private function getSoftwareFromStep(int $step): ArrayCollection {
        $softwareIds = $this->getArrayFromSessionForStep($step);
        $softwareArray = new ArrayCollection();
        if (is_array($softwareIds)) {
            for ($i = 0; $i < count($softwareIds); $i++) {
                $software = $this->softwareRepository->find($softwareIds[$i]);
                if ($software) {
                    $softwareArray->add($software);
                }
            }
        }
        return $softwareArray;
    }

    public function getStepCount(): int {
        return count($this->steps);
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

        for ($i = 0; $i < count($this->steps); $i++) {
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
                // if a datenweitergabe already exists for this step, use that instead
                $item = $this->getDatenweitergabeFromStep($step);
                if (!$item) {
                    if (array_key_exists(self::PROPERTY_TRANSFER_TYPE, $this->steps[$step])) {
                        $item = $this->dataTransferService->newDatenweitergabe($user, 2);
                    } else {
                        $item = $this->dataTransferService->newDatenweitergabe($user, 1);
                    }
                    $this->addDependenciesToDatenweitergabe($item, $step);
                }
                break;
            case VVTType::class:
                // if a procedure already exists for this step, use that instead
                $item = $this->getProcedureFromStep($step);
                if (!$item) {
                    $item = $this->vvtService->newVvt($team, $user);
                    $this->addDependenciesToProcedure($item, $step);
                }
        }
        return $item;
    }

    public function getSelectDataForStep(int $step, Team $team): array {
        $select = [];
        switch ($this->getPropertyForStep($step, self::PROPERTY_TYPE)) {
            case KontaktType::class:
                $select['selected'] = $this->getArrayFromSessionForStep($step);
                $select['label'] = $this->translator->trans(id: 'select.contact', domain: 'assistant');
                $select['items'] = $this->contactRepository->findActiveByTeam($team);
                $select['multiple'] = false;
                break;
            case SoftwareType::class:
                $select['selected'] = $this->getArrayFromSessionForStep($step);
                $select['label'] = $this->translator->trans(id: 'select.software', domain: 'assistant');
                $select['items'] = $this->softwareRepository->findActiveByTeam($team);
                $select['multiple'] = true;
                break;
            case VVTType::class:
                $select['selected'] = $this->getArrayFromSessionForStep($step);
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
        $softwareArray = $this->getPropertyForStep(step: $step, key: self::PROPERTY_SOFTWARE);
        foreach($softwareArray as $software) {
            $item->addSoftware($software);
        }
        $procedure = $this->getPropertyForStep(step: $step, key: self::PROPERTY_PROCEDURE);
        $item->addVerfahren($procedure);
    }

    private function addDependenciesToProcedure(VVT $procedure, $step): void {
        $softwareArray = $this->getPropertyForStep(step: $step, key: self::PROPERTY_SOFTWARE);
        foreach($softwareArray as $software) {
            $procedure->addSoftware($software);
        }
    }
}
