<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\VVTGrundlage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTGrundlage|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTGrundlage|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTGrundlage[]    findAll()
 * @method VVTGrundlage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTGrundlageRepository extends PresetRepository
{
    public function __construct(
        protected readonly ManagerRegistry    $registry,
        protected readonly TeamRepository     $teamRepository,
    )
    {
        parent::__construct($this->registry, $this->teamRepository,VVTGrundlage::class);
    }
}
