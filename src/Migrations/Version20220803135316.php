<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220803135316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE team_admin (team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4F084436296CD8AE (team_id), INDEX IDX_4F084436A76ED395 (user_id), PRIMARY KEY(team_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team_admin ADD CONSTRAINT FK_4F084436296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_admin ADD CONSTRAINT FK_4F084436A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_957A64796352511C');
        $this->addSql('DROP INDEX IDX_8D93D6496352511C ON user');
        $this->addSql('ALTER TABLE user DROP admin_user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE team_admin');
        $this->addSql('ALTER TABLE user ADD admin_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_957A64796352511C FOREIGN KEY (admin_user_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6496352511C ON user (admin_user_id)');
    }
}
