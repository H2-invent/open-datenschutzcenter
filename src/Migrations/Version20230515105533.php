<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Templates\CreateTable;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230515105533 extends AbstractMigration
{
    private const TABLE = 'questionnaire';
    private const FIELD_LABEL = 'label';
    private const FIELD_DESCRIPTION_LABEL = 'description_label';
    private const FIELD_PERCENTAGE_TO_PASS = 'percentage_to_pass';

    public function getDescription(): string
    {
        return 'Create questionnaire table';
    }

    public function up(Schema $schema): void
    {
        $table = CreateTable::createTable($schema, self::TABLE);

        $table->addColumn(self::FIELD_LABEL, Types::STRING)
            ->setLength(255)
            ->setNotnull(true);

        $table->addColumn(self::FIELD_DESCRIPTION_LABEL, Types::STRING)
            ->setLength(255)
            ->setNotnull(false);

        $table->addColumn(self::FIELD_PERCENTAGE_TO_PASS, Types::FLOAT)
            ->setnotnull(true);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable(self::TABLE);
    }
}
