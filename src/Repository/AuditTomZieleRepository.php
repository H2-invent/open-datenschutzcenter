<?php

namespace App\Repository;

use App\Entity\AuditTomZiele;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuditTomZiele|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditTomZiele|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditTomZiele[]    findAll()
 * @method AuditTomZiele[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditTomZieleRepository extends PresetRepository
{
    public function __construct(
        protected readonly ManagerRegistry $registry,
        TeamRepository                     $teamRepository,
    )
    {
        parent::__construct($this->registry, $teamRepository, AuditTomZiele::class);
    }
}
