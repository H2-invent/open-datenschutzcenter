<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AuditTomZiele;
use App\Entity\DatenweitergabeGrundlagen;
use App\Entity\DatenweitergabeStand;
use App\Entity\Produkte;
use App\Entity\Team;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTGrundlage;
use App\Entity\VVTPersonen;
use App\Entity\VVTRisiken;
use App\Entity\VVTStatus;
use App\Repository\AuditTomZieleRepository;
use App\Repository\DatenweitergabeGrundlagenRepository;
use App\Repository\DatenweitergabeStandRepository;
use App\Repository\ProdukteRepository;
use App\Repository\VVTGrundlageRepository;
use App\Repository\VVTPersonenRepository;
use App\Repository\VVTRisikenRepository;
use App\Repository\VVTStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class TeamService
{
    public function __construct(
        private readonly EntityManagerInterface              $em,
        private readonly UrlGeneratorInterface               $router,
        private readonly TranslatorInterface                 $translator,
        private readonly VVTStatusRepository                 $vVTstatusRepository,
        private readonly VVTRisikenRepository                $riskRepository,
        private readonly VVTPersonenRepository               $personRepository,
        private readonly VVTGrundlageRepository              $vVTGroundRepository,
        private readonly ProdukteRepository                  $productRepository,
        private readonly DatenweitergabeStandRepository      $dwStatusRepository,
        private readonly DatenweitergabeGrundlagenRepository $dwGroundRepository,
        private readonly AuditTomZieleRepository             $auditGoalRepository
    )
    {

    }

    /**
     * @throws Exception If $type is not a valid team type
     */
    public function create($type, $id, Team $team): object
    {
        switch ($type) {
            case 1:
                if ($id) {
                    $data1 = $this->personRepository->find($id);
                } else {
                    $data1 = new VVTPersonen();
                }
                break;
            case 2:
                if ($id) {
                    $data1 = $this->em->getRepository(VVTDatenkategorie::class)->find($id);
                } else {
                    $data1 = new VVTDatenkategorie();
                }
                break;
            case 3:
                if ($id) {
                    $data1 = $this->riskRepository->find($id);
                } else {
                    $data1 = new VVTRisiken();
                }
                break;
            case 4:
                if ($id) {
                    $data1 = $this->vVTGroundRepository->find($id);
                } else {
                    $data1 = new VVTGrundlage();
                }
                break;
            case 5:
                if ($id) {
                    $data1 = $this->productRepository->find($id);
                } else {
                    $data1 = new Produkte();
                }
                break;
            case 6:
                if ($id) {
                    $data1 = $this->vVTstatusRepository->find($id);
                } else {
                    $data1 = new VVTStatus();
                }
                break;
            case 11:
                if ($id) {
                    $data1 = $this->dwGroundRepository->find($id);
                } else {
                    $data1 = new DatenweitergabeGrundlagen();
                }
                break;
            case 12:
                if ($id) {
                    $data1 = $this->dwStatusRepository->find($id);
                } else {
                    $data1 = new DatenweitergabeStand();
                }
                break;
            case 21:
                if ($id) {
                    $data1 = $this->auditGoalRepository->find($id);
                } else {
                    $data1 = new AuditTomZiele();
                }
                break;
            default:
                throw new Exception(sprintf('`%s` is an invalid team type.', strval($type)), 1);

        }

        $data1->setActiv(true);
        $data1->setTeam($team);

        return $data1;
    }

    /**
     * @throws Exception If $type is not a valid team type
     */
    public function delete($type, $id): ?object
    {
        switch ($type) {
            case 1:
                $data = $this->personRepository->findOneBy(array('id' => $id));
                break;
            case 2:
                $data = $this->em->getRepository(VVTDatenkategorie::class)->findOneBy(array('id' => $id));
                break;
            case 3:
                $data = $this->riskRepository->findOneBy(array('id' => $id));
                break;
            case 4:
                $data = $this->vVTGroundRepository->findOneBy(array('id' => $id));
                break;
            case 5:
                $data = $this->productRepository->findOneBy(array('id' => $id));
                break;
            case 6:
                $data = $this->vVTstatusRepository->findOneBy(array('id' => $id));
                break;
            case 11:
                $data = $this->dwGroundRepository->findOneBy(array('id' => $id));
                break;
            case 12:
                $data = $this->dwStatusRepository->findOneBy(array('id' => $id));
                break;
            case 21:
                $data = $this->auditGoalRepository->findOneBy(array('id' => $id));
                break;

            default:
                throw new Exception(sprintf('`%s` is an invalid team type.', strval($type)), 1);

        }

        return $data;
    }

    public function show(Team $team): array
    {

        $data = array();

        $id = 1;
        $data1 = $this->personRepository->findAllByTeam($team);
        $data[$id]['title'] = $this->translator->trans(id: 'groupOfPeople.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'groupOfPeople.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'groupOfPeople.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        $data[$id]['type'] = $id;
        foreach ($data1 as $item) {
            $data[$id]['data'][$item->getId()]['id'] = $item->getId();
            $data[$id]['data'][$item->getId()]['name'] = $item->getName();
            $data[$id]['data'][$item->getId()]['deactivate'] = $this->router->generate('team_custom_deativate', ['id' => $item->getId(), 'type' => $id]);
            $data[$id]['data'][$item->getId()]['edit'] = $this->router->generate('team_custom_create', ['id' => $item->getId(), 'title' => $data[$id]['titleEdit'], 'type' => $id]);
            $data[$id]['data'][$item->getId()]['default'] = false;
            $data[$id]['data'][$item->getId()]['object'] = $item;
            if ($item->getTeam() === null) {
                $data[$id]['data'][$item->getId()]['default'] = true;
            }
        }

        $id = 3;
        $data1 = $this->riskRepository->findAllByTeam($team);
        $data[$id]['title'] = $this->translator->trans(id: 'risk.processing', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'risk.new', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'risk.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            $data[$id]['data'][$item->getId()]['id'] = $item->getId();
            $data[$id]['data'][$item->getId()]['name'] = $item->getName();
            $data[$id]['data'][$item->getId()]['deactivate'] = $this->router->generate('team_custom_deativate', ['id' => $item->getId(), 'type' => $id]);
            $data[$id]['data'][$item->getId()]['edit'] = $this->router->generate('team_custom_create', ['id' => $item->getId(), 'title' => $data[$id]['titleEdit'], 'type' => $id]);
            $data[$id]['data'][$item->getId()]['default'] = false;
            $data[$id]['data'][$item->getId()]['object'] = $item;
            if ($item->getTeam() === null) {
                $data[$id]['data'][$item->getId()]['default'] = true;
            }
        }

        $id = 4;
        $data1 = $this->vVTGroundRepository->findAllByTeam($team);
        $data[$id]['title'] = $this->translator->trans(id: 'legalBase.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'legalBase.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'legalBase.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            $data[$id]['data'][$item->getId()]['id'] = $item->getId();
            $data[$id]['data'][$item->getId()]['name'] = $item->getName();
            $data[$id]['data'][$item->getId()]['deactivate'] = $this->router->generate('team_custom_deativate', ['id' => $item->getId(), 'type' => $id]);
            $data[$id]['data'][$item->getId()]['edit'] = $this->router->generate('team_custom_create', ['id' => $item->getId(), 'title' => $data[$id]['titleEdit'], 'type' => $id]);
            $data[$id]['data'][$item->getId()]['default'] = false;
            $data[$id]['data'][$item->getId()]['object'] = $item;
            if ($item->getTeam() === null) {
                $data[$id]['data'][$item->getId()]['default'] = true;
            }
        }

        $id = 5;
        $data1 = $this->productRepository->findAllByTeam($team);
        $data[$id]['title'] = $this->translator->trans(id: 'product.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'product.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'product.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            $data[$id]['data'][$item->getId()]['id'] = $item->getId();
            $data[$id]['data'][$item->getId()]['name'] = $item->getName();
            $data[$id]['data'][$item->getId()]['deactivate'] = $this->router->generate('team_custom_deativate', ['id' => $item->getId(), 'type' => $id]);
            $data[$id]['data'][$item->getId()]['edit'] = $this->router->generate('team_custom_create', ['id' => $item->getId(), 'title' => $data[$id]['titleEdit'], 'type' => $id]);
            $data[$id]['data'][$item->getId()]['default'] = false;
            $data[$id]['data'][$item->getId()]['object'] = $item;
            if ($item->getTeam() === null) {
                $data[$id]['data'][$item->getId()]['default'] = true;
            }
        }


        $id = 6;
        $data1 = $this->vVTstatusRepository->findAllByTeam($team);
        $data[$id]['title'] = $this->translator->trans(id: 'processingState.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'processingState.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'processingState.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            $data[$id]['data'][$item->getId()]['id'] = $item->getId();
            $data[$id]['data'][$item->getId()]['name'] = $item->getName();
            $data[$id]['data'][$item->getId()]['deactivate'] = $this->router->generate('team_custom_deativate', ['id' => $item->getId(), 'type' => $id]);
            $data[$id]['data'][$item->getId()]['edit'] = $this->router->generate('team_custom_create', ['id' => $item->getId(), 'title' => $data[$id]['titleEdit'], 'type' => $id]);
            $data[$id]['data'][$item->getId()]['default'] = false;
            $data[$id]['data'][$item->getId()]['object'] = $item;
            if ($item->getTeam() === null) {
                $data[$id]['data'][$item->getId()]['default'] = true;
            }
        }

        $id = 11;
        $data1 = $this->dwGroundRepository->findAllByTeam($team);
        $data[$id]['title'] = $this->translator->trans(id: 'dataTransfer.base.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'dataTransfer.base.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'dataTransfer.base.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            $data[$id]['data'][$item->getId()]['id'] = $item->getId();
            $data[$id]['data'][$item->getId()]['name'] = $item->getName();
            $data[$id]['data'][$item->getId()]['deactivate'] = $this->router->generate('team_custom_deativate', ['id' => $item->getId(), 'type' => $id]);
            $data[$id]['data'][$item->getId()]['edit'] = $this->router->generate('team_custom_create', ['id' => $item->getId(), 'title' => $data[$id]['titleEdit'], 'type' => $id]);
            $data[$id]['data'][$item->getId()]['default'] = false;
            $data[$id]['data'][$item->getId()]['object'] = $item;
            if ($item->getTeam() === null) {
                $data[$id]['data'][$item->getId()]['default'] = true;
            }
        }

        $id = 12;
        $data1 = $this->dwStatusRepository->findAllByTeam($team);
        $data[$id]['title'] = $this->translator->trans(id: 'dataTransfer.state.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'dataTransfer.state.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'dataTransfer.state.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            $data[$id]['data'][$item->getId()]['id'] = $item->getId();
            $data[$id]['data'][$item->getId()]['name'] = $item->getName();
            $data[$id]['data'][$item->getId()]['deactivate'] = $this->router->generate('team_custom_deativate', ['id' => $item->getId(), 'type' => $id]);
            $data[$id]['data'][$item->getId()]['edit'] = $this->router->generate('team_custom_create', ['id' => $item->getId(), 'title' => $data[$id]['titleEdit'], 'type' => $id]);
            $data[$id]['data'][$item->getId()]['default'] = false;
            $data[$id]['data'][$item->getId()]['object'] = $item;
            if ($item->getTeam() === null) {
                $data[$id]['data'][$item->getId()]['default'] = true;
            }
        }


        $id = 21;
        $data1 = $this->auditGoalRepository->findAllByTeam($team);
        $data[$id]['title'] = $this->translator->trans(id: 'auditGoal.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'auditGoal.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'auditGoal.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            $data[$id]['data'][$item->getId()]['id'] = $item->getId();
            $data[$id]['data'][$item->getId()]['name'] = $item->getName();
            $data[$id]['data'][$item->getId()]['deactivate'] = $this->router->generate('team_custom_deativate', ['id' => $item->getId(), 'type' => $id]);
            $data[$id]['data'][$item->getId()]['edit'] = $this->router->generate('team_custom_create', ['id' => $item->getId(), 'title' => $data[$id]['titleEdit'], 'type' => $id]);
            $data[$id]['data'][$item->getId()]['default'] = false;
            $data[$id]['data'][$item->getId()]['object'] = $item;
            if ($item->getTeam() === null) {
                $data[$id]['data'][$item->getId()]['default'] = true;
            }
        }


        return $data;
    }
}
