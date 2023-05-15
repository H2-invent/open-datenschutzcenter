<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Templates\CreateTable;
use App\Migrations\Util\Tables;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20230515081952 extends AbstractMigration
{
    private const FIELD_LABEL = 'label';
    private const FIELD_HINT_LABEL = 'hint_label';
    private const FIELD_EVAL_VALUE = 'eval_value';
    private const FIELD_TYPE = 'type';

    public function getDescription(): string
    {
        return 'Create question table';
    }

    public function up(Schema $schema): void
    {
        $table = CreateTable::createTable($schema, Tables::$QUESTION);

        $table->addColumn(self::FIELD_LABEL, Types::STRING)
            ->setLength(255)
            ->setNotnull(true);

        $table->addColumn(self::FIELD_HINT_LABEL, Types::STRING)
            ->setLength(255)
            ->setNotnull(false);

        $table->addColumn(self::FIELD_EVAL_VALUE, Types::BOOLEAN)
            ->setNotnull(true)
            ->setDefault(1);

        $table->addColumn(self::FIELD_TYPE, Types::STRING)
            ->setLength(255)
            ->setNotnull(true);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable(Tables::$QUESTION);
    }
}
