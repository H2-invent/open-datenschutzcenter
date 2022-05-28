<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220526163752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vvtdatenkategorie ADD previous_id INT DEFAULT NULL, ADD user_id INT NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE vvtdatenkategorie ADD CONSTRAINT FK_91E13AAD2DE62210 FOREIGN KEY (previous_id) REFERENCES vvtdatenkategorie (id)');
        $this->addSql('ALTER TABLE vvtdatenkategorie ADD CONSTRAINT FK_91E13AADA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_91E13AAD2DE62210 ON vvtdatenkategorie (previous_id)');
        $this->addSql('CREATE INDEX IDX_91E13AADA76ED395 ON vvtdatenkategorie (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vvtdatenkategorie DROP FOREIGN KEY FK_91E13AAD2DE62210');
        $this->addSql('ALTER TABLE vvtdatenkategorie DROP FOREIGN KEY FK_91E13AADA76ED395');
        $this->addSql('DROP INDEX UNIQ_91E13AAD2DE62210 ON vvtdatenkategorie');
        $this->addSql('DROP INDEX IDX_91E13AADA76ED395 ON vvtdatenkategorie');
        $this->addSql('ALTER TABLE vvtdatenkategorie DROP previous_id, DROP user_id, DROP created_at');
    }
}
