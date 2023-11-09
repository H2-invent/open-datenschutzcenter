<?php

namespace App\Service;

use App\Entity\Team;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuService
{
    private ?UserInterface $user = null;
    private ?Team $currentTeam = null;

    public function __construct(private FactoryInterface $factory, private TranslatorInterface $translator, private Security $security, private CurrentTeamService $currentTeamService)
    {
        $this->user = $this->security->getUser();
        $this->currentTeam = $this->currentTeamService->getTeamFromSession($this->user);
    }

    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (!$this->user->getTeamDsb()->isEmpty()) {
            $menu->addChild($this->trans('externalDsb'), ['route' => 'dsb']);
        }

        if ($this->currentTeam) {
            $menu->addChild($this->trans('dashboard'), ['route' => 'dashboard']);
            $menu->addChild($this->trans('myAssignments'), ['route' => 'assign']);
            $menu->addChild($this->trans('tasks'), ['route' => 'tasks']);
        }

        return $menu;
    }

    public function createElementsMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild($this->trans('auditQuestions'), ['route' => 'audit_tom']);
        $menu->addChild($this->trans('dataCategories'), ['route' => 'app_vvtdatenkategorie_index']);
        $menu->addChild($this->trans('toms'), ['route' => 'tom']);
        $menu->addChild($this->trans('processings'), ['route' => 'vvt']);
        $menu->addChild($this->trans('deleteConcepts'), ['route' => 'app_loeschkonzept_index']);
        $menu->addChild($this->trans('policies'), ['route' => 'policies']);
        $menu->addChild($this->trans('contacts'), ['route' => 'kontakt']);
        $menu->addChild($this->trans('dataTransfers'), ['route' => 'datenweitergabe']);
        $menu->addChild($this->trans('orderProcessing'), ['route' => 'auftragsverarbeitung']);
        $menu->addChild($this->trans('software'), ['route' => 'software']);
        $menu->addChild($this->trans('forms'), ['route' => 'forms']);
        $menu->addChild($this->trans('incidents'), ['route' => 'vorfall']);
        $menu->addChild($this->trans('customerQuestions'), ['route' => 'client_requests']);
        $menu->addChild($this->trans('activities'), ['route' => 'report']);

        return $menu;
    }

    public function createAcademyMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if ($this->user->getAkademieUser()) {
            $menu->addChild($this->trans('kurse'), ['route' => 'akademie']);
        }

        return $menu;
    }

    public function createTeamAdminMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if ($this->user->hasAdminRole()) {
            $menu->addChild($this->trans('teamData'), ['route' => 'team_edit']);
            $menu->addChild($this->trans('presets'), ['route' => 'team_custom']);
            $menu->addChild($this->trans('departments'), ['route' => 'team_abteilungen']);
            $menu->addChild($this->trans('users'), ['route' => 'team_mitglieder']);
            $menu->addChild($this->trans('academy'), ['route' => 'akademie_admin']);
            $menu->addChild($this->trans('externalDsb'), ['route' => 'team_dsb']);
        }

        return $menu;
    }

    public function createAdminMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (\in_array('ROLE_ADMIN', $this->user->getRoles())) {
            $menu->addChild($this->trans('manageOdc'), ['route' => 'manage_settings']);
            $menu->addChild($this->trans('manageTeams'), ['route' => 'manage_teams']);
        }

        return $menu;
    }

    private function trans(string $id, array $parameters = []): string
    {
        return $this->translator->trans($id, $parameters, 'base');
    }
}