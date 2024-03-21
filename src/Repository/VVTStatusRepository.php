<?php

namespace App\Repository;

use App\Entity\VVTStatus;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTStatus[]    findAll()
 * @method VVTStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTStatusRepository extends PresetRepository
{
    public function __construct(
        protected readonly ManagerRegistry    $registry,
        protected readonly TeamRepository     $teamRepository,
    )
    {
        parent::__construct($this->registry, $this->teamRepository, VVTStatus::class);
    }
}
