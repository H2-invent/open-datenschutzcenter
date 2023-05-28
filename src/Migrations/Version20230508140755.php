<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230508140755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('user')
            ->dropColumn('team_id')
            ->dropColumn('admin_user_id');
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('user')
            ->addColumn('team_id', Types::INTEGER)
            ->setNotnull(false);

        $schema->getTable('user')
            ->addColumn('admin_user_id', Types::INTEGER)
            ->setNotnull(false);
    }
}
