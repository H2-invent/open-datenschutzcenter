<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220528095821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vvtdatenkategorie ADD clone_of_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vvtdatenkategorie ADD CONSTRAINT FK_91E13AAD97A8D4D6 FOREIGN KEY (clone_of_id) REFERENCES vvtdatenkategorie (id)');
        $this->addSql('CREATE INDEX IDX_91E13AAD97A8D4D6 ON vvtdatenkategorie (clone_of_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vvtdatenkategorie DROP FOREIGN KEY FK_91E13AAD97A8D4D6');
        $this->addSql('DROP INDEX IDX_91E13AAD97A8D4D6 ON vvtdatenkategorie');
        $this->addSql('ALTER TABLE vvtdatenkategorie DROP clone_of_id');
    }
}
