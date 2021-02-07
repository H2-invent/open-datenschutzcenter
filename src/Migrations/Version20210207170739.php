<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210207170739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vvtstatus ADD team_id INT DEFAULT NULL, ADD activ TINYINT(1) NOT NULL');
        $this->addSql('UPDATE vvtstatus SET activ = true');
        $this->addSql('ALTER TABLE vvtstatus ADD CONSTRAINT FK_DD6A0661296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_DD6A0661296CD8AE ON vvtstatus (team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vvtstatus DROP FOREIGN KEY FK_DD6A0661296CD8AE');
        $this->addSql('DROP INDEX IDX_DD6A0661296CD8AE ON vvtstatus');
        $this->addSql('ALTER TABLE vvtstatus DROP team_id, DROP activ');
    }
}
