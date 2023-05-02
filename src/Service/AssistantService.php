<?php

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
    private RequestStack $requestStack;

    private DatenweitergabeService $dataTransferService;

    private SoftwareService $softwareService;

    private VVTService $vvtService;

    private KontakteRepository $contactRepository;

    private SoftwareRepository $softwareRepository;

    private VVTRepository $vvtRepository;

    private TranslatorInterface $translator;

    private FormFactoryInterface $formBuilder;

    // TODO: Move to separate config file
    private static array $STEPS = [
        0 => [
            'type' => SoftwareType::class,
            'key' => 'software-processing-procedure',
            'title' => 'softwareProcessingProcedure',
            'info' => 'softwareProcessingProcedureInfo',
            'newTitle' => 'createNewSoftware',
            'skip' => true,
        ],
        1 => [
            'type' => VVTType::class,
            'key' => 'processing-procedure',
            'title' => 'processingProcedure',
            'info' => 'processingProcedureInfo',
            'newTitle' => 'createNewProcedure',
            'software' => 0,
        ],
        2 => [
            'type' => KontaktType::class,
            'key' => 'contact-source',
            'title' => 'contactSource',
            'info' => 'contactSourceInfo',
            'newTitle' => 'createNewContact',
        ],
        3 => [
            'type' => DatenweitergabeType::class,
            'key' => 'order-processing',
            'title' => 'orderProcessing',
            'info' => 'orderProcessingInfo',
            'newTitle' => 'createNewOrderProcessing',
            'contact' => 0,
            'vvt' => 1,
        ],
        4 => [
            'type' => SoftwareType::class,
            'key' => 'software-transfer-procedure',
            'title' => 'softwareTransferProcedure',
            'info' => 'softwareTransferProcedureInfo',
            'newTitle' => 'createNewSoftware',
            'skip' => true,
        ],
        5 => [
            'type' => VVTType::class,
            'key' => 'transfer-procedure',
            'title' => 'transferProcedure',
            'info' => 'transferProcedureInfo',
            'newTitle' => 'createNewProcedure',
            'software' => 4,
        ],
        6 => [
            'type' => KontaktType::class,
            'key' => 'contact-dest',
            'title' => 'contactDest',
            'info' => 'contactDestInfo',
            'newTitle' => 'createNewContact',
        ],
        7 => [
            'type' => SoftwareType::class,
            'key' => 'software-transfer',
            'title' => 'softwareTransfer',
            'info' => 'softwareTransferInfo',
            'newTitle' => 'createNewSoftware',
            'skip' => true,
        ],
        8 => [
            'type' => DatenweitergabeType::class,
            'key' => 'data-transfer',
            'title' => 'dataTransfer',
            'info' => 'dataTransferInfo',
            'newTitle' => 'createNewDataTransfer',
            'vvt' => 5,
            'contact' => 6,
            'software' => 7,
        ],
    ];

    public function __construct(RequestStack $requestStack,
                                DatenweitergabeService $dataTransferService,
                                SoftwareService $softwareService,
                                VVTService $vvtService,
                                KontakteRepository $contactRepository,
                                SoftwareRepository $softwareRepository,
                                VVTRepository $vvtRepository,
                                TranslatorInterface $translator,
                                FormFactoryInterface $formBuilder
    )
    {
        $this->requestStack = $requestStack;
        $this->dataTransferService = $dataTransferService;
        $this->softwareService = $softwareService;
        $this->vvtService = $vvtService;
        $this->contactRepository = $contactRepository;
        $this->softwareRepository = $softwareRepository;
        $this->vvtRepository = $vvtRepository;
        $this->translator = $translator;
        $this->formBuilder = $formBuilder;
    }

    public function setPropertyForStep(string $id, int $step) {
        $session = $this->requestStack->getSession();
        $property = AssistantService::$STEPS[$step]['key'];
        $session->set($property, $id);
    }

    public function getPropertyForStep(int $step): ?string {
        $session = $this->requestStack->getSession();
        $property = AssistantService::$STEPS[$step]['key'];
        return $session->get($property);
    }

    public function getTitleForStep(int $step): ?string {
        $steps =  AssistantService::$STEPS;
        if (array_key_exists($step, $steps) && array_key_exists('title', $steps[$step])) {
            return $this->translator->trans(id: $steps[$step]['title'], domain: 'assistant');
        }
        return null;
    }

    public function getInfoForStep(int $step): ?string {
        $steps =  AssistantService::$STEPS;
        if (array_key_exists($step, $steps) && array_key_exists('info', $steps[$step])) {
            return $this->translator->trans(id: $steps[$step]['info'], domain: 'assistant');
        }
        return null;
    }

    public function getNewTitleForStep(int $step): ?string {
        $steps =  AssistantService::$STEPS;
        if (array_key_exists($step, $steps) && array_key_exists('newTitle', $steps[$step])) {
            return $this->translator->trans($steps[$step]['newTitle'], domain: 'assistant');
        }
        return null;
    }

    public function getElementTypeForStep(int $step): ?string {
        $steps =  AssistantService::$STEPS;
        if (array_key_exists($step, $steps) && array_key_exists('type', $steps[$step])) {
            return $steps[$step]['type'];
        }
        return null;
    }

    public function getContactForStep(int $step): ?Kontakte {
        $steps =  AssistantService::$STEPS;
        if (array_key_exists($step, $steps) && array_key_exists('contact', $steps[$step])) {
            $contactStep = $steps[$step]['contact'];
            $contactId = $this->getPropertyForStep($contactStep);
            return $contactId ? $this->contactRepository->find($contactId): null;
        }
        return null;
    }

    public function getProcedureForStep(int $step): ?VVT {
        $steps =  AssistantService::$STEPS;
        if (array_key_exists($step, $steps) && array_key_exists('vvt', $steps[$step])) {
            $procedureStep = $steps[$step]['vvt'];
            $procedureId = $this->getPropertyForStep($procedureStep);
            return $procedureId ? $this->vvtRepository->find($procedureId): null;
        }
        return null;
    }

    public function getSoftwareForStep(int $step): ?Software {
        $steps =  AssistantService::$STEPS;
        if (array_key_exists($step, $steps) && array_key_exists('software', $steps[$step])) {
            $softwareStep = $steps[$step]['software'];
            $softwareId = $this->getPropertyForStep($softwareStep);
            return $softwareId ? $this->softwareRepository->find($softwareId): null;
        }
        return null;
    }

    public function getSkipForStep(int $step): ?bool {
        $steps =  AssistantService::$STEPS;
        if (array_key_exists($step, $steps) && array_key_exists('skip', $steps[$step])) {
            return $steps[$step]['skip'];
        }
        return false;
    }

    public function getStepCount(): int {
        return count(AssistantService::$STEPS);
    }

    public function setStep(int $step) {
        $session = $this->requestStack->getSession();
        $session->set('step', $step);
    }

    public function getStep(): int {
        $session = $this->requestStack->getSession();
        $id = $session->get('step');
        return $id ? : 0;
    }

    public function clear() {
        $session = $this->requestStack->getSession();
        $session->set('step', 0);

        foreach (AssistantService::$STEPS as $step) {
            $session->remove($step['key']);
        }
    }

    public function createElementForStep(int $step, User $user, Team $team)
    {
        $item = null;

        switch ($this->getElementTypeForStep($step)) {
            case SoftwareType::class:
                return $this->softwareService->newSoftware($team, $user);
            case KontaktType::class:
                $item = new Kontakte();
                $item->setTeam($team);
                $item->setActiv(1);
                return $item;
            case DatenweitergabeType::class:
                if (AssistantService::$STEPS[$step]['title'] === 'orderProcessing') {
                    $item = $this->dataTransferService->newDatenweitergabe($user, 2, 'AVV-');
                } else {
                    $item = $this->dataTransferService->newDatenweitergabe($user, 1, 'DW-');
                }
                $this->addDependenciesToDatenweitergabe($item, $step);
                return $item;
            case VVTType::class:
                $item = $this->vvtService->newVvt($team, $user);
                $this->addDependenciesToProcedure($item, $step);
        }
        return $item;
    }

    public function getSelectDataForStep(int $step, Team $team):array
    {
        $select = [];
        switch ($this->getElementTypeForStep($step)) {
            case KontaktType::class:
                $select['selected'] = $this->getPropertyForStep($step);
                $select['label'] = $this->translator->trans(id: 'contactSelectLabel', domain: 'assistant');
                $select['items'] = $this->contactRepository->findActiveByTeam($team);
                $select['multiple'] = false;
                break;
            case SoftwareType::class:
                $select['selected'] = $this->getPropertyForStep($step);
                $select['label'] = $this->translator->trans(id: 'softwareSelectLabel', domain: 'assistant');
                $select['items'] = $this->softwareRepository->findActiveByTeam($team);
                $select['multiple'] = true;
                break;
            case VVTType::class:
                $select['selected'] = $this->getPropertyForStep($step);
                $select['label'] = $this->translator->trans(id: 'procedureSelectLabel', domain: 'assistant');
                $select['items'] = $this->vvtRepository->findActiveByTeam($team);
                $select['multiple'] = true;
                break;
        }
        return $select;
    }

    public function createForm($type, $newItem, Team $team)
    {
        switch ($type) {
            case DatenweitergabeType::class:
                return $this->dataTransferService->createForm($newItem, $team);
            case VVTType::class:
                return $this->vvtService->createForm($newItem, $team);
            default:
                return $this->formBuilder->create($type, $newItem);
        }
    }

    private function addDependenciesToDatenweitergabe(Datenweitergabe $item, $step) {
        $contact = $this->getContactForStep($step);
        $item->setKontakt($contact);
        $software = $this->getSoftwareForStep($step);
        if ($software) {
            $item->addSoftware($software);
        }
        $procedure = $this->getProcedureForStep($step);
        $item->addVerfahren($procedure);
    }

    private function addDependenciesToProcedure(VVT $procedure, $step) {
        $software = $this->getSoftwareForStep($step);
        if ($software) {
            $procedure->addSoftware($software);
        }
    }
}
