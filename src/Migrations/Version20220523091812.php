<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220523091812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE loeschkonzept_vvtdatenkategorie (loeschkonzept_id INT NOT NULL, vvtdatenkategorie_id INT NOT NULL, INDEX IDX_953419125E7723E (loeschkonzept_id), INDEX IDX_953419145450705 (vvtdatenkategorie_id), PRIMARY KEY(loeschkonzept_id, vvtdatenkategorie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE loeschkonzept_vvtdatenkategorie ADD CONSTRAINT FK_953419125E7723E FOREIGN KEY (loeschkonzept_id) REFERENCES loeschkonzept (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE loeschkonzept_vvtdatenkategorie ADD CONSTRAINT FK_953419145450705 FOREIGN KEY (vvtdatenkategorie_id) REFERENCES vvtdatenkategorie (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE loeschkonzept_vvtdatenkategorie');
    }
}
