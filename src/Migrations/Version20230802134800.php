<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230802134800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vvt_team (vvt_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_4FF1CCA1677671F9 (vvt_id), INDEX IDX_4FF1CCA1296CD8AE (team_id), PRIMARY KEY(vvt_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vvt_team ADD CONSTRAINT FK_4FF1CCA1677671F9 FOREIGN KEY (vvt_id) REFERENCES vvt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvt_team ADD CONSTRAINT FK_4FF1CCA1296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vvt_team DROP FOREIGN KEY FK_4FF1CCA1677671F9');
        $this->addSql('ALTER TABLE vvt_team DROP FOREIGN KEY FK_4FF1CCA1296CD8AE');
        $this->addSql('DROP TABLE vvt_team');
    }
}
