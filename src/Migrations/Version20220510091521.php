<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220510091521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept ADD team_id INT NOT NULL');
        $this->addSql('ALTER TABLE loeschkonzept ADD CONSTRAINT FK_FF3E3D8C296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_FF3E3D8C296CD8AE ON loeschkonzept (team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept DROP FOREIGN KEY FK_FF3E3D8C296CD8AE');
        $this->addSql('DROP INDEX IDX_FF3E3D8C296CD8AE ON loeschkonzept');
        $this->addSql('ALTER TABLE loeschkonzept DROP team_id');
    }
}
