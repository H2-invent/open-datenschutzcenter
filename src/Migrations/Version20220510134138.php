<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220510134138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept ADD previous_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE loeschkonzept ADD CONSTRAINT FK_FF3E3D8C2DE62210 FOREIGN KEY (previous_id) REFERENCES loeschkonzept (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FF3E3D8C2DE62210 ON loeschkonzept (previous_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept DROP FOREIGN KEY FK_FF3E3D8C2DE62210');
        $this->addSql('DROP INDEX UNIQ_FF3E3D8C2DE62210 ON loeschkonzept');
        $this->addSql('ALTER TABLE loeschkonzept DROP previous_id');
    }
}
