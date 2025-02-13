<?php

namespace App\Repository;

use App\Entity\VVTRisiken;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTRisiken|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTRisiken|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTRisiken[]    findAll()
 * @method VVTRisiken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTRisikenRepository extends PresetRepository
{
    public function __construct(
        protected readonly ManagerRegistry    $registry,
        TeamRepository                        $teamRepository,
    )
    {
        parent::__construct($this->registry, $teamRepository, VVTRisiken::class);
    }
}
