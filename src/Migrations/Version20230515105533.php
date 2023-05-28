<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Templates\CreateTable;
use App\Migrations\Templates\RelationColumn;
use App\Migrations\Util\Tables;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230515105533 extends AbstractMigration
{
    private const FIELD_LABEL = 'label';
    private const FIELD_DESCRIPTION = 'description';
    private const FIELD_PERCENTAGE_TO_PASS = 'percentage_to_pass';

    public function getDescription(): string
    {
        return 'Create questionnaire table';
    }

    public function up(Schema $schema): void
    {
        $table = CreateTable::createTable($schema, Tables::$QUESTIONNAIRE);

        $table->addColumn(self::FIELD_LABEL, Types::STRING)
            ->setLength(255)
            ->setNotnull(true);

        $table->addColumn(self::FIELD_DESCRIPTION, Types::STRING)
            ->setLength(255)
            ->setNotnull(false);

        $table->addColumn(self::FIELD_PERCENTAGE_TO_PASS, Types::FLOAT)
            ->setnotnull(true);

        RelationColumn::addRelation(
            table: $table,
            targetTable: $schema->getTable(Tables::$TEAM),
            nullable: false,
        );
    }

    public function down(Schema $schema): void
    {
        foreach($schema->getTable(Tables::$QUESTIONNAIRE)->getIndexes() as $index){
            $schema->getTable(Tables::$QUESTIONNAIRE)->dropIndex($index->getName());
        }

        $schema->dropTable(Tables::$QUESTIONNAIRE);
    }
}
