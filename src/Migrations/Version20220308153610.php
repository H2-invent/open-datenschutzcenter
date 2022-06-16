<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220308153610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vvtdatenkategorie ADD loeschkonzept_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vvtdatenkategorie ADD CONSTRAINT FK_91E13AAD25E7723E FOREIGN KEY (loeschkonzept_id) REFERENCES loeschkonzept (id)');
        $this->addSql('CREATE INDEX IDX_91E13AAD25E7723E ON vvtdatenkategorie (loeschkonzept_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vvtdatenkategorie DROP FOREIGN KEY FK_91E13AAD25E7723E');
        $this->addSql('DROP INDEX IDX_91E13AAD25E7723E ON vvtdatenkategorie');
        $this->addSql('ALTER TABLE vvtdatenkategorie DROP loeschkonzept_id');
    }
}
