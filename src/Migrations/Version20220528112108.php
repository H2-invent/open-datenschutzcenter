<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220528112108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept ADD clone_of_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE loeschkonzept ADD CONSTRAINT FK_FF3E3D8C97A8D4D6 FOREIGN KEY (clone_of_id) REFERENCES loeschkonzept (id)');
        $this->addSql('CREATE INDEX IDX_FF3E3D8C97A8D4D6 ON loeschkonzept (clone_of_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept DROP FOREIGN KEY FK_FF3E3D8C97A8D4D6');
        $this->addSql('DROP INDEX IDX_FF3E3D8C97A8D4D6 ON loeschkonzept');
        $this->addSql('ALTER TABLE loeschkonzept DROP clone_of_id');
    }
}
