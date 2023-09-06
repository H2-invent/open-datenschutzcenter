<?php


namespace App\Service;


use App\Entity\Loeschkonzept;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVTDatenkategorie;
use App\Form\Type\LoeschkonzeptType;
use App\Repository\VVTDatenkategorieRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;


class LoeschkonzeptService
{
    private $em;
    private $formBuilder;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
    }

    function cloneLoeschkonzept(Loeschkonzept $loeschkonzept): Loeschkonzept
    {
        $newLoeschkonzept = clone $loeschkonzept;
        $newLoeschkonzept->setPrevious($loeschkonzept);
        $newLoeschkonzept->setCreateAt(new DateTimeImmutable());

        return $newLoeschkonzept;
    }

    function createForm(Loeschkonzept $loeschkonzept, Team $team, array $options = []): FormInterface
    {
        /** @var VVTDatenkategorieRepository $categoryRepository */
        $categoryRepository = $this->em->getRepository(VVTDatenkategorie::class);
        if ((array_key_exists('disabled', $options) && $options['disabled'])) {
            $categories = $categoryRepository->findAllByTeam($team);
        } else {
            $categories = $categoryRepository->findLatestActiveByTeam($team);
        }
        $options['vvtdatenkategories'] = $categories;

        return $this->formBuilder->create(LoeschkonzeptType::class, $loeschkonzept, $options);
    }

    function newLoeschkonzept(Team $team, User $user): Loeschkonzept
    {
        $loeschkonzept = new Loeschkonzept();
        $loeschkonzept->setTeam($team);
        $loeschkonzept->setCreateAt(new DateTimeImmutable());
        $loeschkonzept->setActiv(true);
        $loeschkonzept->setUser($user);

        return $loeschkonzept;
    }
}
