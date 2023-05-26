<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230518145342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add roles to user';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('user');
        $table->addColumn('roles', Types::JSON)
            ->setNotnull(false)
            ->setDefault(null);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('user');
        $table->dropColumn('roles');
    }
}
