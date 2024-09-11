<?php

namespace App\Repository;

use App\Entity\DatenweitergabeStand;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DatenweitergabeStand|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatenweitergabeStand|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatenweitergabeStand[]    findAll()
 * @method DatenweitergabeStand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatenweitergabeStandRepository extends PresetRepository
{
    public function __construct(
        protected readonly ManagerRegistry    $registry,
        TeamRepository                        $teamRepository,
    )
    {
        parent::__construct($this->registry, $teamRepository, DatenweitergabeStand::class);
    }
}
