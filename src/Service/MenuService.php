<?php

namespace App\Service;

use App\Entity\Team;
use App\Entity\User;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuService
{
    /**
     * @var (UserInterface&User)|null
     */
    private ?UserInterface $user = null;
    private ?Team $currentTeam = null;

    public function __construct(
        private FactoryInterface $factory,
        private TranslatorInterface $translator,
        private Security $security,
        private CurrentTeamService $currentTeamService,
        private RequestStack $requestStack,
    ) {
        $this->user = $this->security->getUser();
        $this->currentTeam = $this->currentTeamService->getCurrentTeam($this->user);
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
            $menu->addChild($this->trans('assistant'), ['route' => 'assistant']);
            $menu->addChild($this->trans('reports'), ['route' => 'bericht']);
        }

        $this->handleCurrentItem($menu);

        return $menu;
    }

    public function createElementsMenu(array $options): ?ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (!$this->user->getTeams()->isEmpty()) {
            $menu->addChild($this->trans('auditQuestions'), ['route' => 'audit_tom']);
            $menu->addChild($this->trans('dataCategories'), ['route' => 'app_vvtdatenkategorie_index']);
            $menu->addChild($this->trans('toms'), ['route' => 'tom']);
            $menu->addChild($this->trans('processings'), ['route' => 'vvt']);
            $menu->addChild($this->trans('dsfa'), ['route' => 'dsfa']);
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

            $this->handleCurrentItem($menu);

            return $menu;
        }

        return $menu;
    }

    public function createAcademyMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if ($this->user->getAkademieUser()) {
            $menu->addChild($this->trans('academyCourses', [], 'academy'), ['route' => 'akademie']);
        }

        $this->handleCurrentItem($menu);

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

        $this->handleCurrentItem($menu);

        return $menu;
    }

    public function createAdminMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (\in_array('ROLE_ADMIN', $this->user->getRoles())) {
            $menu->addChild($this->trans('manageOdc'), ['route' => 'manage_settings']);
            $menu->addChild($this->trans('manageTeams'), ['route' => 'manage_teams']);
        }

        $this->handleCurrentItem($menu);

        return $menu;
    }

    private function trans(string $id, array $parameters = [], $domain = 'base'): string
    {
        return $this->translator->trans($id, $parameters, $domain);
    }

    /**
     * This sets the current item when we are on a subpage that is not in the menu tree.
     *
     * @param ItemInterface $menu
     * @return void
     */
    private function handleCurrentItem(ItemInterface &$menu): void
    {
        $currentRoutePrefix = $this->getRoutePrefix($this->requestStack->getCurrentRequest()->get('_route'));

        foreach ($menu->getChildren() as &$child) {
            $route = $this->standardizeRouteName($child->getExtra('routes')[0]['route']);
            if (\preg_match("/^$currentRoutePrefix\_/", $route . '_')) {
                $child->setCurrent(true);
            }
        }
    }

    private function getRoutePrefix(string $route): string
    {
        $parts = \explode('_', $this->standardizeRouteName($route));

        if ('team_create' === $route) {
            return 'manage_teams';
        }

        if (\str_starts_with($route, 'team')) {
            return $route;
        }

        if (\str_starts_with($route, 'akademie')) {
            return $route . '_';
        }

        if (\str_starts_with($route, 'manage')) {
            return $route;
        }

        return $parts[0];
    }

    /**
     * Standardizes the route names.
     *
     * This method is intended to be temporarily. We should eventually standardize the route names.
     *
     * @param string $route
     * @return string
     */
    private function standardizeRouteName(string $route): string
    {
        $route = \preg_replace('/^tasks/', 'task', $route);
        $route = \preg_replace('/^policies/', 'policy', $route);
        $route = \preg_replace('/^app_/', '', $route);

        return $route;
    }
}
