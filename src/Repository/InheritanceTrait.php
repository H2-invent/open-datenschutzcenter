<?php

namespace App\Repository;

use App\Entity\Revisionable;
use App\Entity\Team;
use App\Entity\VVT;

trait InheritanceTrait {

    abstract public function findAllByTeam(Team $team): array;
    abstract public function findActiveByTeam(Team $team): array;
    abstract protected function getVvtByClone(Revisionable $clone): ?VVT;

    /**
     * Find entities owned by given Team or inherited by ancestor team, when its vvt enables inheritance
     * Information about vvt inheritance settings supplied in annotation
     * Returns also those inherited that are ignored by current team
     * @param Team $team
     * @param bool $annotated
     * @return array
     */
    public function findLatestByTeam(Team $team, bool $annotated = true): array
    {
        if ($annotated) {
            $mapper = function (Revisionable $entity) use ($team) {
                if ($entity->getActiv()) {
                    return array_merge(
                        ['entity' => $entity],
                        $entity->isVvtInheriting($team),
                    );
                }
                // as this must be a cloned Revisionable, it can only be referenced by a single vvt
                $vvt = $this->getVvtByClone($entity);
                return [
                    'entity' => $this->findLatest($entity->getCloneOf()),
                    'isVvtInheriting' => $vvt?->isInherited() ?? false,
                    'isVvtInheritanceEnabled' => !$vvt?->getIgnoredInTeams()->contains($team) ?? false,
                ];
            };
        } else {
            $mapper = function (Revisionable $entity) use ($team) {
                if ($entity->getActiv()) {
                    return $entity;
                }
                return $this->findLatest($entity->getCloneOf());
            };
        }

        return array_map(
            $mapper,
            $this->findAllByTeam($team)
        );
    }

    /**
     * Returns the current team entities and those inherited, which vvts has enabled inheritance and which are not ignored by current team
     * @param Team $team
     * @return Revisionable[]
     */
    public function findLatestActiveByTeam(Team $team): array
    {
        // map categories to their latest version
        $mapper = function (Revisionable $entity) use ($team) {
            if ($entity->getTeam() === $team) {
                return $entity;
            }
            return $this->findLatest($entity->getCloneOf());
        };

        // filter by active (the latest version might be inactive e.g. when the entity was deleted)
        $filter = function (Revisionable $entity) {
            return $entity->getActiv();
        };

        return array_values(
            array_filter(
                array_map(
                    $mapper,
                    $this->findActiveByTeam($team)
                ),
                $filter
            )
        );
    }

    /**
     *
     * @param Revisionable $entity
     * @return Revisionable
     */
    private function findLatest(Revisionable $entity): Revisionable {
        $successor = $this->findOneBy(['previous' => $entity->getId()]);
        if ($successor) {
            return $this->findLatest($successor);
        }
        return $entity;
    }
}