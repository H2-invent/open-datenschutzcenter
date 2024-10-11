<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240807153157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add API group mapping option';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE settings ADD group_mapping INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE settings SET group_mapping = use_keycloak_groups WHERE use_keycloak_groups IS NOT NULL');
        $this->addSql('ALTER TABLE settings DROP use_keycloak_groups');
        $this->addSql('ALTER TABLE team ADD immutable TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE settings ADD use_keycloak_groups TINYINT(1) DEFAULT NULL');
        $this->addSql('UPDATE settings SET use_keycloak_groups = group_mapping WHERE group_mapping = 1');
        $this->addSql('ALTER TABLE settings DROP group_mapping');
        $this->addSql('ALTER TABLE team DROP immutable');
    }
}
