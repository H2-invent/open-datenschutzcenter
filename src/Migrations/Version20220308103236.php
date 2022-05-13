<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220308103236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE loeschkonzept (id INT AUTO_INCREMENT NOT NULL, datenart VARCHAR(511) NOT NULL, standartlf VARCHAR(255) NOT NULL, loeschfrist VARCHAR(255) NOT NULL, speicherorte VARCHAR(511) NOT NULL, loeschbeauftragter VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE loeschkonzept_vvt (loeschkonzept_id INT NOT NULL, vvt_id INT NOT NULL, INDEX IDX_2BA034EF25E7723E (loeschkonzept_id), INDEX IDX_2BA034EF677671F9 (vvt_id), PRIMARY KEY(loeschkonzept_id, vvt_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE loeschkonzept_vvt ADD CONSTRAINT FK_2BA034EF25E7723E FOREIGN KEY (loeschkonzept_id) REFERENCES loeschkonzept (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE loeschkonzept_vvt ADD CONSTRAINT FK_2BA034EF677671F9 FOREIGN KEY (vvt_id) REFERENCES vvt (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept_vvt DROP FOREIGN KEY FK_2BA034EF25E7723E');
        $this->addSql('DROP TABLE loeschkonzept');
        $this->addSql('DROP TABLE loeschkonzept_vvt');
    }
}
