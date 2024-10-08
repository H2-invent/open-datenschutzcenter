<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241008090501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer CHANGE question_id question_id INT NOT NULL');
        $this->addSql('ALTER TABLE participation CHANGE akademie_buchungen_id akademie_buchungen_id INT DEFAULT NULL, CHANGE passed passed TINYINT(1) NOT NULL, CHANGE state state VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE participation_answer CHANGE participation_id participation_id INT NOT NULL, CHANGE questionnaire_id questionnaire_id INT NOT NULL, CHANGE question_id question_id INT NOT NULL, CHANGE answer_id answer_id INT NOT NULL');
        $this->addSql('ALTER TABLE question CHANGE eval_value eval_value DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE questionnaire CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE team RENAME INDEX unq_team_name TO UNIQ_C4E0A61F5E237E06');
        $this->addSql('ALTER TABLE team_admin MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE team_admin DROP FOREIGN KEY RFX_team_id_team_admin_6704f59cda29d');
        $this->addSql('ALTER TABLE team_admin DROP FOREIGN KEY RFX_user_id_team_admin_6704f59cda242');
        $this->addSql('DROP INDEX team_admin_IdX ON team_admin');
        $this->addSql('DROP INDEX `primary` ON team_admin');
        $this->addSql('ALTER TABLE team_admin DROP id');
        $this->addSql('ALTER TABLE team_admin ADD CONSTRAINT FK_4F084436296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_admin ADD CONSTRAINT FK_4F084436A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_admin ADD PRIMARY KEY (team_id, user_id)');
        $this->addSql('ALTER TABLE user RENAME INDEX idx_957a64799e73ac3e TO IDX_8D93D6499E73AC3E');
        $this->addSql('ALTER TABLE user_team MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE user_team DROP FOREIGN KEY RFX_team_id_user_team_6704f59cda128');
        $this->addSql('ALTER TABLE user_team DROP FOREIGN KEY RFX_user_id_user_team_6704f59cd9f23');
        $this->addSql('DROP INDEX user_team_IdX ON user_team');
        $this->addSql('DROP INDEX `primary` ON user_team');
        $this->addSql('ALTER TABLE user_team DROP id');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT FK_BE61EAD6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT FK_BE61EAD6296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_team ADD PRIMARY KEY (user_id, team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer CHANGE question_id question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participation CHANGE akademie_buchungen_id akademie_buchungen_id INT NOT NULL, CHANGE state state VARCHAR(255) DEFAULT \'assigned\' NOT NULL, CHANGE passed passed TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE participation_answer CHANGE participation_id participation_id INT DEFAULT NULL, CHANGE questionnaire_id questionnaire_id INT DEFAULT NULL, CHANGE question_id question_id INT DEFAULT NULL, CHANGE answer_id answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question CHANGE eval_value eval_value DOUBLE PRECISION DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE questionnaire CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE team RENAME INDEX uniq_c4e0a61f5e237e06 TO UNQ_team_name');
        $this->addSql('ALTER TABLE team_admin DROP FOREIGN KEY FK_4F084436296CD8AE');
        $this->addSql('ALTER TABLE team_admin DROP FOREIGN KEY FK_4F084436A76ED395');
        $this->addSql('ALTER TABLE team_admin ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE team_admin ADD CONSTRAINT RFX_team_id_team_admin_6704f59cda29d FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE team_admin ADD CONSTRAINT RFX_user_id_team_admin_6704f59cda242 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX team_admin_IdX ON team_admin (id)');
        $this->addSql('ALTER TABLE user RENAME INDEX idx_8d93d6499e73ac3e TO IDX_957A64799E73AC3E');
        $this->addSql('ALTER TABLE user_team DROP FOREIGN KEY FK_BE61EAD6A76ED395');
        $this->addSql('ALTER TABLE user_team DROP FOREIGN KEY FK_BE61EAD6296CD8AE');
        $this->addSql('ALTER TABLE user_team ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT RFX_team_id_user_team_6704f59cda128 FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT RFX_user_id_user_team_6704f59cd9f23 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX user_team_IdX ON user_team (id)');
    }
}
