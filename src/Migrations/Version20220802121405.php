<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220802121405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX name ON team');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C4E0A61F5E237E06 ON team (name)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_957A6479296CD8AE');
        $this->addSql('DROP INDEX IDX_957A6479296CD8AE ON user');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_957A64799E73AC3E');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_957A64796352511C');
        $this->addSql('ALTER TABLE user DROP team_id');
        $this->addSql('DROP INDEX idx_957a64799e73ac3e ON user');
        $this->addSql('CREATE INDEX IDX_8D93D6499E73AC3E ON user (akademie_user_id)');
        $this->addSql('DROP INDEX idx_957a64796352511c ON user');
        $this->addSql('CREATE INDEX IDX_8D93D6496352511C ON user (admin_user_id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_957A64799E73AC3E FOREIGN KEY (akademie_user_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_957A64796352511C FOREIGN KEY (admin_user_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT FK_BE61EAD6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT FK_BE61EAD6296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BE61EAD6A76ED395 ON user_team (user_id)');
        $this->addSql('CREATE INDEX IDX_BE61EAD6296CD8AE ON user_team (team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_c4e0a61f5e237e06 ON team');
        $this->addSql('CREATE UNIQUE INDEX name ON team (name)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499E73AC3E');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6496352511C');
        $this->addSql('ALTER TABLE user ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_957A6479296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_957A6479296CD8AE ON user (team_id)');
        $this->addSql('DROP INDEX idx_8d93d6499e73ac3e ON user');
        $this->addSql('CREATE INDEX IDX_957A64799E73AC3E ON user (akademie_user_id)');
        $this->addSql('DROP INDEX idx_8d93d6496352511c ON user');
        $this->addSql('CREATE INDEX IDX_957A64796352511C ON user (admin_user_id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499E73AC3E FOREIGN KEY (akademie_user_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496352511C FOREIGN KEY (admin_user_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE user_team DROP FOREIGN KEY FK_BE61EAD6A76ED395');
        $this->addSql('ALTER TABLE user_team DROP FOREIGN KEY FK_BE61EAD6296CD8AE');
        $this->addSql('DROP INDEX IDX_BE61EAD6A76ED395 ON user_team');
        $this->addSql('DROP INDEX IDX_BE61EAD6296CD8AE ON user_team');
    }
}
