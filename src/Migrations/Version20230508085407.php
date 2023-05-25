<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230508085407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $team = $schema->getTable('team');
        $team->getColumn('`name`')
            ->setType(Type::getType(Types::STRING))
            ->setLength(255);
        $team->addColumn('display_name', Types::TEXT);

        $team->addUniqueIndex(['name'], 'UNQ_team_name');
    }

    public function down(Schema $schema): void
    {
        $team = $schema->getTable('team');
        echo json_encode($team->getUniqueConstraints());
        $team->dropIndex('UNQ_team_name');
        $team->getColumn('`name`')
            ->setType(Type::getType(Types::TEXT));
    }
}
