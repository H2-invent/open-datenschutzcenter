<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AuditTomZiele;
use App\Entity\DatenweitergabeStand;
use App\Entity\Produkte;
use App\Entity\Team;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTGrundlage;
use App\Entity\VVTPersonen;
use App\Entity\VVTRisiken;
use App\Entity\VVTStatus;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\DatenweitergabeGrundlagen;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class TeamService
{
    private $em;
    private $router;
    private $translator;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->em = $entityManager;
        $this->router = $urlGenerator;
        $this->translator = $translator;
    }

    public function create($type, $id, Team $team): object
    {
        switch ($type) {
            case 1:
                if ($id) {
                    $data1 = $this->em->getRepository(VVTPersonen::class)->find($id);
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
                    $data1 = $this->em->getRepository(VVTRisiken::class)->find($id);
                } else {
                    $data1 = new VVTRisiken();
                }
                break;
            case 4:
                if ($id) {
                    $data1 = $this->em->getRepository(VVTGrundlage::class)->find($id);
                } else {
                    $data1 = new VVTGrundlage();
                }
                break;
            case 5:
                if ($id) {
                    $data1 = $this->em->getRepository(Produkte::class)->find($id);
                } else {
                    $data1 = new Produkte();
                }
                break;
            case 6:
                if ($id) {
                    $data1 = $this->em->getRepository(VVTStatus::class)->find($id);
                } else {
                    $data1 = new VVTStatus();
                }
                break;
            case 11:
                if ($id) {
                    $data1 = $this->em->getRepository(DatenweitergabeGrundlagen::class)->find($id);
                } else {
                    $data1 = new DatenweitergabeGrundlagen();
                }
                break;
            case 12:
                if ($id) {
                    $data1 = $this->em->getRepository(DatenweitergabeStand::class)->find($id);
                } else {
                    $data1 = new DatenweitergabeStand();
                }
                break;
            case 21:
                if ($id) {
                    $data1 = $this->em->getRepository(AuditTomZiele::class)->find($id);
                } else {
                    $data1 = new AuditTomZiele();
                }
                break;
            default:

                break;

        }

        $data1->setActiv(true);
        $data1->setTeam($team);

        return $data1;
    }

    public function delete($type, $id): ?object
    {
        switch ($type) {
            case 1:
                $data = $this->em->getRepository(VVTPersonen::class)->findOneBy(array('id' => $id));
                break;
            case 2:
                $data = $this->em->getRepository(VVTDatenkategorie::class)->findOneBy(array('id' => $id));
                break;
            case 3:
                $data = $this->em->getRepository(VVTRisiken::class)->findOneBy(array('id' => $id));
                break;
            case 4:
                $data = $this->em->getRepository(VVTGrundlage::class)->findOneBy(array('id' => $id));
                break;
            case 5:
                $data = $this->em->getRepository(Produkte::class)->findOneBy(array('id' => $id));
                break;
            case 6:
                $data = $this->em->getRepository(VVTStatus::class)->findOneBy(array('id' => $id));
                break;
            case 11:
                $data = $this->em->getRepository(DatenweitergabeGrundlagen::class)->findOneBy(array('id' => $id));
                break;
            case 12:
                $data = $this->em->getRepository(DatenweitergabeStand::class)->findOneBy(array('id' => $id));
                break;
            case 21:
                $data = $this->em->getRepository(AuditTomZiele::class)->findOneBy(array('id' => $id));
                break;

            default:
                break;

        }

        return $data;
    }

    public function show(Team $team): array
    {

        $data = array();

        $id = 1;
        $data1 = $this->em->getRepository(VVTPersonen::class)->findBy(array('activ' => true));
        $data[$id]['title'] = $this->translator->trans(id: 'groupOfPeople.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'groupOfPeople.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'groupOfPeople.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        $data[$id]['type'] = $id;
        foreach ($data1 as $item) {
            if ($item->getTeam() === $team ) {
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
        }

        $id = 3;
        $data1 = $this->em->getRepository(VVTRisiken::class)->findBy(array('activ' => true));
        $data[$id]['title'] = $this->translator->trans(id: 'risk.processing', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'risk.new', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'risk.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            if ($item->getTeam() === $team ) {
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
        }

        $id = 4;
        $data1 = $this->em->getRepository(VVTGrundlage::class)->findBy(array('activ' => true));
        $data[$id]['title'] = $this->translator->trans(id: 'legalBase.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'legalBase.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'legalBase.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            if ($item->getTeam() === $team ) {
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
        }

        $id = 5;
        $data1 = $this->em->getRepository(Produkte::class)->findBy(array('activ' => true));
        $data[$id]['title'] = $this->translator->trans(id: 'product.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'product.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'product.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            if ($item->getTeam() === $team ) {
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
        }


        $id = 6;
        $data1 = $this->em->getRepository(VVTStatus::class)->findBy(array('activ' => true));
        $data[$id]['title'] = $this->translator->trans(id: 'processingState.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'processingState.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'processingState.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            if ($item->getTeam() === $team ) {
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
        }

        $id = 11;
        $data1 = $this->em->getRepository(DatenweitergabeGrundlagen::class)->findBy(array('activ' => true));
        $data[$id]['title'] = $this->translator->trans(id: 'dataTransfer.base.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'dataTransfer.base.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'dataTransfer.base.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            if ($item->getTeam() === $team ) {
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
        }

        $id = 12;
        $data1 = $this->em->getRepository(DatenweitergabeStand::class)->findBy(array('activ' => true));
        $data[$id]['title'] = $this->translator->trans(id: 'dataTransfer.state.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'dataTransfer.state.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'dataTransfer.state.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            if ($item->getTeam() === $team ) {
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
        }


        $id = 21;
        $data1 = $this->em->getRepository(AuditTomZiele::class)->findBy(array('activ' => true));
        $data[$id]['title'] = $this->translator->trans(id: 'auditGoal.word', domain: 'service');
        $data[$id]['titleNew'] = $this->translator->trans(id: 'auditGoal.add', domain: 'service');
        $data[$id]['titleEdit'] = $this->translator->trans(id: 'auditGoal.edit', domain: 'service');
        $data[$id]['newLink'] = $this->router->generate('team_custom_create', ['title' => $data[$id]['titleNew'], 'type' => $id]);
        foreach ($data1 as $item) {
            if ($item->getTeam() === $team) {
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
        }


        return $data;
    }
}
