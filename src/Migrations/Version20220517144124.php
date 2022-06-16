<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220517144124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE loeschkonzept ADD CONSTRAINT FK_FF3E3D8CA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_FF3E3D8CA76ED395 ON loeschkonzept (user_id)');
        $this->addSql('DROP TABLE loeschkonzept_vvt');
        $this->addSql('ALTER TABLE vvt CHANGE loeschfrist loeschfrist LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept DROP FOREIGN KEY FK_FF3E3D8CA76ED395');
        $this->addSql('DROP INDEX IDX_FF3E3D8CA76ED395 ON loeschkonzept');
        $this->addSql('ALTER TABLE loeschkonzept DROP user_id');
        $this->addSql('CREATE TABLE loeschkonzept_vvt (loeschkonzept_id INT NOT NULL, vvt_id INT NOT NULL, INDEX IDX_2BA034EF677671F9 (vvt_id), INDEX IDX_2BA034EF25E7723E (loeschkonzept_id), PRIMARY KEY(loeschkonzept_id, vvt_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE loeschkonzept_vvt ADD CONSTRAINT FK_2BA034EF677671F9 FOREIGN KEY (vvt_id) REFERENCES vvt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE loeschkonzept_vvt ADD CONSTRAINT FK_2BA034EF25E7723E FOREIGN KEY (loeschkonzept_id) REFERENCES loeschkonzept (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvt CHANGE loeschfrist loeschfrist LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
