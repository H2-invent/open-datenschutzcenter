<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Templates\CreateTable;
use App\Migrations\Templates\RelationColumn;
use App\Migrations\Util\Tables;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230515123533 extends AbstractMigration
{
    private const FIELD_COMPLETED_AT = 'completed_at';
    private const FIELD_STATE = 'state';
    private const FIELD_PASSED = 'passed';

    public function getDescription(): string
    {
        return 'Create participation table';
    }

    public function up(Schema $schema): void
    {
        $table = CreateTable::createTable($schema, Tables::$PARTICIPATION);

        $table->addColumn(self::FIELD_PASSED, Types::BOOLEAN)
            ->setNotnull(false);

        $table->addColumn(self::FIELD_STATE, Types::STRING)
            ->setNotnull(true)
            ->setDefault('assigned');

        $table->addColumn(self::FIELD_COMPLETED_AT, Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setDefault(null);

        RelationColumn::addRelation(
            table: $table,
            targetTable: $schema->getTable(Tables::$ACADEMY_BILLING),
            nullable: false,
        );

        RelationColumn::addRelation(
            table: $table,
            targetTable: $schema->getTable(Tables::$QUESTIONNAIRE),
            nullable: false,
        );
    }

    public function down(Schema $schema): void
    {
        foreach($schema->getTable(Tables::$PARTICIPATION)->getIndexes() as $index) {
            $schema->getTable(Tables::$PARTICIPATION)->dropIndex($index->getName());
        }

        $schema->dropTable(Tables::$PARTICIPATION);
    }
}
