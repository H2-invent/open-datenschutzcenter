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
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept DROP FOREIGN KEY FK_FF3E3D8CA76ED395');
        $this->addSql('DROP INDEX IDX_FF3E3D8CA76ED395 ON loeschkonzept');
        $this->addSql('ALTER TABLE loeschkonzept DROP user_id');
    }
}
