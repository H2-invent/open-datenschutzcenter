<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Templates\RelationColumn;
use App\Migrations\Util\Tables;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230517065940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add questionnaire to akademieKurs';
    }

    public function up(Schema $schema): void
    {
        RelationColumn::addRelation(
            table: $schema->getTable(Tables::$AKADEMIE_KURS),
            targetTable: $schema->getTable(Tables::$QUESTIONNAIRE),
        );
    }

    public function down(Schema $schema): void
    {

    }
}
