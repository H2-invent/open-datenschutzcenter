<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240816105509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Avoid name conflicts for API generated teams by modifying the team unique constraint';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNQ_team_name ON team');
        $this->addSql('CREATE UNIQUE INDEX UNQ_team_name_and_immutable ON team (name, immutable)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNQ_team_name_and_immutable ON team');
        $this->addSql('CREATE UNIQUE INDEX UNQ_team_name ON team (name)');
    }
}
