<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220705145408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'allow multiple teams per user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_team ( user_id INT(11) NOT NULL , team_id INT(11) NOT NULL , PRIMARY KEY (user_id, team_id))');
        $this->addSql('ALTER TABLE team ADD keycloak_user_group LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user DROP team_id');
        $this->addSql('ALTER TABLE fos_user RENAME TO user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_team');
        $this->addSql('ALTER TABLE team DROP keycloak_user_group');
        $this->addSql('ALTER TABLE user RENAME TO fos_user');
        $this->addSql('ALTER TABLE fos_user ADD team_id INT DEFAULT NULL, ADD activ TINYINT(1) NOT NULL');
    }
}
