<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Templates\CreateTable;
use App\Migrations\Templates\RelationColumn;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230515103631 extends AbstractMigration
{
    private const TABLE = 'answer';
    private const FIELD_LABEL = 'label';
    private const FIELD_IS_CORRECT = 'is_correct';
    private const QUESTION_TABLE = 'question';

    public function getDescription(): string
    {
        return 'Create answer table';
    }

    public function up(Schema $schema): void
    {
        $table = CreateTable::createTable($schema, self::TABLE);

        $table->addColumn(self::FIELD_LABEL, Types::STRING)
            ->setLength(255)
            ->setNotnull(true);

        $table->addColumn(self::FIELD_IS_CORRECT, Types::BOOLEAN)
            ->setNotnull(true);

        RelationColumn::addRelation(
            table: $table,
            targetTable: $schema->getTable(self::QUESTION_TABLE)
        );
    }

    public function down(Schema $schema): void
    {
        RelationColumn::dropRelation(
            table: $schema->getTable(self::TABLE),
            targetTable: $schema->getTable(self::QUESTION_TABLE),
        );

        $schema->dropTable(self::TABLE);
    }
}
