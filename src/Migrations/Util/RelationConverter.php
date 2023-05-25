<?php

namespace App\Migrations\Util;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

class RelationConverter
{
    public static function convert(
        Connection $connection,
        Table      $sourceTable,
        Column     $identifierColumn,
        Column     $sourceColumn,
        string     $mappingTableName,
        string    $sourceColumnName,
        string    $targetColumnName,
    ): void
    {
        $sourceEntities = $connection->createQueryBuilder()
            ->select('t.*')
            ->from('`'.$sourceTable->getName().'`', 't')
            ->fetchAllAssociative();

        foreach ($sourceEntities as $sourceEntity) {
            $foreignKey = $sourceEntity[$sourceColumn->getName()];
            $identifier = $sourceEntity[$identifierColumn->getName()];

            if($foreignKey === null || $identifier === null){
                continue;
            }

            $connection->createQueryBuilder()
                ->insert($mappingTableName)
                ->setValue(
                    column: $sourceColumnName,
                    value: $identifier
                )
                ->setValue(
                    column: $targetColumnName,
                    value: $foreignKey,
                )
            ->executeQuery();
        }
    }

    public static function createManyToManyMappingTable(
        Schema $schema,
        Table  $sourceTable,
        Table  $targetTable,
        string $mappingTableName,
        ?string $sourceColumnName = null,
        ?string $targetColumnName = null,
    ): Table
    {
        $sourceColumnName = $sourceColumnName ?? $sourceTable->getName() . '_id';
        $targetColumnName = $targetColumnName ?? $targetTable->getName() . '_id';

        $mappingTable = $schema->createTable($mappingTableName);
        $mappingTable->addColumn('id', Types::INTEGER)
            ->setNotnull(true)
            ->setAutoincrement(true);

        $mappingTable->addUniqueIndex(['id'], $mappingTable->getName() . '_IdX');
        $mappingTable->setPrimaryKey(['id'], $mappingTable->getName() . '_Id');

        $sourceColumn = $mappingTable->addColumn($sourceColumnName, Types::INTEGER)
            ->setNotnull(true);

        $targetColumn = $mappingTable->addColumn($targetColumnName, Types::INTEGER)
            ->setNotnull(true);

        $mappingTable->addForeignKeyConstraint(
            foreignTable: $sourceTable,
            foreignColumnNames: $sourceTable->getPrimaryKey()->getColumns(),
            localColumnNames: [$sourceColumnName],
            name: 'RFX_' . $sourceColumn->getName() . '_' . $mappingTableName.'_'. uniqid(),
        );

        $mappingTable->addForeignKeyConstraint(
            foreignTable: $targetTable,
            foreignColumnNames: $targetTable->getPrimaryKey()->getColumns(),
            localColumnNames: [$targetColumnName],
            name: 'RFX_' . $targetColumn->getName() . '_' . $mappingTableName.'_'.uniqid(),
        );

        return $mappingTable;
    }
}