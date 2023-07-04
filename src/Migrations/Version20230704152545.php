<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230704152545 extends AbstractMigration
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
        $this->addSql('ALTER TABLE team ADD tree_root INT DEFAULT NULL, ADD parent_id INT DEFAULT NULL, ADD lvl INT DEFAULT NULL, ADD lft INT DEFAULT NULL, ADD rgt INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FA977936C FOREIGN KEY (tree_root) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F727ACA70 FOREIGN KEY (parent_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C4E0A61FA977936C ON team (tree_root)');
        $this->addSql('CREATE INDEX IDX_C4E0A61F727ACA70 ON team (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question CHANGE eval_value eval_value DOUBLE PRECISION DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE participation_answer CHANGE participation_id participation_id INT DEFAULT NULL, CHANGE questionnaire_id questionnaire_id INT DEFAULT NULL, CHANGE question_id question_id INT DEFAULT NULL, CHANGE answer_id answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participation CHANGE akademie_buchungen_id akademie_buchungen_id INT NOT NULL, CHANGE state state VARCHAR(255) DEFAULT \'assigned\' NOT NULL, CHANGE passed passed TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE questionnaire CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE answer CHANGE question_id question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FA977936C');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F727ACA70');
        $this->addSql('DROP INDEX IDX_C4E0A61FA977936C ON team');
        $this->addSql('DROP INDEX IDX_C4E0A61F727ACA70 ON team');
        $this->addSql('ALTER TABLE team DROP tree_root, DROP parent_id, DROP lvl, DROP lft, DROP rgt');
    }
}
