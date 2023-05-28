<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Templates\CreateTable;
use App\Migrations\Templates\RelationColumn;
use App\Migrations\Util\Tables;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230515132941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create participation_answer table';
    }

    public function up(Schema $schema): void
    {
        $table = CreateTable::createTable($schema, Tables::$PARTICIPATION_ANSWER);
        $relationTables = [
            Tables::$PARTICIPATION,
            Tables::$QUESTIONNAIRE,
            Tables::$QUESTION,
            Tables::$ANSWER,
        ];

        foreach ($relationTables as $targetTableName) {
            RelationColumn::addRelation(
                table: $table,
                targetTable: $schema->getTable($targetTableName),
            );
        }
    }

    public function down(Schema $schema): void
    {
        foreach($schema->getTable(Tables::$PARTICIPATION_ANSWER)->getIndexes() as $index) {
            $schema->getTable(Tables::$PARTICIPATION_ANSWER)->dropIndex($index->getName());
        }

        $schema->dropTable(Tables::$PARTICIPATION_ANSWER);
    }
}
