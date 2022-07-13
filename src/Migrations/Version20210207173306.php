<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210207173306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        if(!$schema->getTable('datenweitergabe_grundlagen')->hasColumn('team_id')){
            $this->addSql('ALTER TABLE datenweitergabe_grundlagen ADD team_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE datenweitergabe_grundlagen ADD CONSTRAINT FK_CA5A157A296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
            $this->addSql('CREATE INDEX IDX_CA5A157A296CD8AE ON datenweitergabe_grundlagen (team_id)');
        }
        if(!$schema->getTable('datenweitergabe_grundlagen')->hasColumn('activ')){
            $this->addSql('ALTER TABLE datenweitergabe_grundlagen ADD activ TINYINT(1) NOT NULL');
            $this->addSql('UPDATE datenweitergabe_grundlagen SET activ = true');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE datenweitergabe_grundlagen DROP FOREIGN KEY FK_CA5A157A296CD8AE');
        $this->addSql('DROP INDEX IDX_CA5A157A296CD8AE ON datenweitergabe_grundlagen');
        $this->addSql('ALTER TABLE datenweitergabe_grundlagen DROP team_id, DROP activ');
    }
}
