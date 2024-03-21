<?php

namespace App\Repository;

use App\Entity\DatenweitergabeGrundlagen;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DatenweitergabeGrundlagen|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatenweitergabeGrundlagen|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatenweitergabeGrundlagen[]    findAll()
 * @method DatenweitergabeGrundlagen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatenweitergabeGrundlagenRepository extends PresetRepository
{
    public function __construct(
        protected readonly ManagerRegistry    $registry,
        protected readonly TeamRepository     $teamRepository,
    )
    {
        parent::__construct($this->registry, $this->teamRepository, DatenweitergabeGrundlagen::class);
    }
}
