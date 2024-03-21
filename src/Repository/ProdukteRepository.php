<?php

namespace App\Repository;

use App\Entity\Produkte;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Produkte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produkte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produkte[]    findAll()
 * @method Produkte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProdukteRepository extends PresetRepository
{
    public function __construct(
        protected readonly ManagerRegistry    $registry,
        protected readonly TeamRepository     $teamRepository,
    )
    {
        parent::__construct($this->registry, $this->teamRepository, Produkte::class);
    }
}
