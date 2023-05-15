<?php
declare(strict_types=1);

namespace App\Migrations\Templates;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

class RelationColumn
{
    private static $INDEX_FORMAT = '%s_%s_FK';

    /** @throws SchemaException */
    public static function addRelation(Table $table, Table $targetTable, string $targetColumn = 'id'): void
    {
        $columnName = $targetTable->getName() . '_' . $targetColumn;

        $table->addColumn($columnName, Types::INTEGER)
            ->setNotnull(true);

        $table->addForeignKeyConstraint(
            foreignTable: $targetTable,
            localColumnNames: [$columnName],
            foreignColumnNames: [$targetColumn],
            name: sprintf(self::$INDEX_FORMAT, $table->getName(), $targetTable->getName()),
        );
    }

    /** @throws SchemaException */
    public static function dropRelation(Table $table, Table $targetTable, string $targetColumn = 'id'): void
    {
        $columnName = $targetTable->getName() . '_' . $targetColumn;

        $table->dropIndex(sprintf(self::$INDEX_FORMAT, $table->getName(), $targetTable->getName()));
        $table->dropColumn($columnName);
    }
}