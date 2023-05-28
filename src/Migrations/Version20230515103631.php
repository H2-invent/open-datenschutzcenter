<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Templates\CreateTable;
use App\Migrations\Templates\RelationColumn;
use App\Migrations\Util\Tables;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230515103631 extends AbstractMigration
{
    private const FIELD_LABEL = 'label';
    private const FIELD_IS_CORRECT = 'is_correct';

    public function getDescription(): string
    {
        return 'Create answer table';
    }

    public function up(Schema $schema): void
    {
        $table = CreateTable::createTable($schema, Tables::$ANSWER);

        $table->addColumn(self::FIELD_LABEL, Types::STRING)
            ->setLength(255)
            ->setNotnull(true);

        $table->addColumn(self::FIELD_IS_CORRECT, Types::BOOLEAN)
            ->setNotnull(true);

        RelationColumn::addRelation(
            table: $table,
            targetTable: $schema->getTable(Tables::$QUESTION)
        );
    }

    public function down(Schema $schema): void
    {
        foreach($schema->getTable(Tables::$QUESTIONNAIRE_QUESTION)->getIndexes() as $index) {
            $schema->getTable(Tables::$QUESTIONNAIRE_QUESTION)->dropIndex($index->getName());
        }

        $schema->dropTable(Tables::$ANSWER);
    }
}
