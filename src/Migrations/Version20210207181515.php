<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210207181515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if(!$schema->getTable('datenweitergabe_stand')->hasColumn('team_id')){
            $this->addSql('ALTER TABLE datenweitergabe_stand ADD team_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE datenweitergabe_stand ADD CONSTRAINT FK_AB9EF50F296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
            $this->addSql('CREATE INDEX IDX_AB9EF50F296CD8AE ON datenweitergabe_stand (team_id)');
        }
        if(!$schema->getTable('datenweitergabe_stand')->hasColumn('activ')){
            $this->addSql('ALTER TABLE datenweitergabe_stand ADD activ TINYINT(1) NOT NULL');
            $this->addSql('UPDATE datenweitergabe_stand SET activ = true');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE datenweitergabe_stand DROP FOREIGN KEY FK_AB9EF50F296CD8AE');
        $this->addSql('DROP INDEX IDX_AB9EF50F296CD8AE ON datenweitergabe_stand');
        $this->addSql('ALTER TABLE datenweitergabe_stand DROP team_id, DROP activ');
    }
}
