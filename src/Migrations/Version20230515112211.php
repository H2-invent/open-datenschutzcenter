<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Templates\CreateTable;
use App\Migrations\Templates\RelationColumn;
use App\Migrations\Util\Tables;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230515112211 extends AbstractMigration
{
    private const FIELD_STEP = 'step';

    public function getDescription(): string
    {
        return 'Create questionnaire_question table';
    }

    public function up(Schema $schema): void
    {
        $table = CreateTable::createTable($schema, Tables::$QUESTIONNAIRE_QUESTION);

        $table->addColumn(self::FIELD_STEP, Types::INTEGER)
            ->setNotnull(true);

        foreach ([Tables::$QUESTION, Tables::$QUESTIONNAIRE] as $targetTableName) {
            RelationColumn::addRelation(
                table: $table,
                targetTable: $schema->getTable($targetTableName),
                nullable: false,
            );
        }
    }

    public function down(Schema $schema): void
    {
        foreach($schema->getTable(Tables::$QUESTIONNAIRE_QUESTION)->getIndexes() as $index) {
            $schema->getTable(Tables::$QUESTIONNAIRE_QUESTION)->dropIndex($index->getName());
        }

        $schema->dropTable(Tables::$QUESTIONNAIRE_QUESTION);
    }
}
