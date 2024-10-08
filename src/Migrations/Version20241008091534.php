<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241008091534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audit_tom_ziele_team (audit_tom_ziele_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_69170ACF24B29C5F (audit_tom_ziele_id), INDEX IDX_69170ACF296CD8AE (team_id), PRIMARY KEY(audit_tom_ziele_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE datenweitergabe_grundlagen_team (datenweitergabe_grundlagen_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_1D5E1D65832C7682 (datenweitergabe_grundlagen_id), INDEX IDX_1D5E1D65296CD8AE (team_id), PRIMARY KEY(datenweitergabe_grundlagen_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE datenweitergabe_stand_team (datenweitergabe_stand_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_105357981361291D (datenweitergabe_stand_id), INDEX IDX_10535798296CD8AE (team_id), PRIMARY KEY(datenweitergabe_stand_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produkte_team (produkte_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_4E9D9226F5AB0B87 (produkte_id), INDEX IDX_4E9D9226296CD8AE (team_id), PRIMARY KEY(produkte_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vvt_team (vvt_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_4FF1CCA1677671F9 (vvt_id), INDEX IDX_4FF1CCA1296CD8AE (team_id), PRIMARY KEY(vvt_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vvtgrundlage_team (vvtgrundlage_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_B09EF10C80257D66 (vvtgrundlage_id), INDEX IDX_B09EF10C296CD8AE (team_id), PRIMARY KEY(vvtgrundlage_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vvtpersonen_team (vvtpersonen_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_F430C11097064E08 (vvtpersonen_id), INDEX IDX_F430C110296CD8AE (team_id), PRIMARY KEY(vvtpersonen_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vvtrisiken_team (vvtrisiken_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_ACD5CD76DF7C8617 (vvtrisiken_id), INDEX IDX_ACD5CD76296CD8AE (team_id), PRIMARY KEY(vvtrisiken_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vvtstatus_team (vvtstatus_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_692A2281D4694B3A (vvtstatus_id), INDEX IDX_692A2281296CD8AE (team_id), PRIMARY KEY(vvtstatus_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audit_tom_ziele_team ADD CONSTRAINT FK_69170ACF24B29C5F FOREIGN KEY (audit_tom_ziele_id) REFERENCES audit_tom_ziele (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE audit_tom_ziele_team ADD CONSTRAINT FK_69170ACF296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE datenweitergabe_grundlagen_team ADD CONSTRAINT FK_1D5E1D65832C7682 FOREIGN KEY (datenweitergabe_grundlagen_id) REFERENCES datenweitergabe_grundlagen (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE datenweitergabe_grundlagen_team ADD CONSTRAINT FK_1D5E1D65296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE datenweitergabe_stand_team ADD CONSTRAINT FK_105357981361291D FOREIGN KEY (datenweitergabe_stand_id) REFERENCES datenweitergabe_stand (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE datenweitergabe_stand_team ADD CONSTRAINT FK_10535798296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produkte_team ADD CONSTRAINT FK_4E9D9226F5AB0B87 FOREIGN KEY (produkte_id) REFERENCES produkte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produkte_team ADD CONSTRAINT FK_4E9D9226296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvt_team ADD CONSTRAINT FK_4FF1CCA1677671F9 FOREIGN KEY (vvt_id) REFERENCES vvt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvt_team ADD CONSTRAINT FK_4FF1CCA1296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvtgrundlage_team ADD CONSTRAINT FK_B09EF10C80257D66 FOREIGN KEY (vvtgrundlage_id) REFERENCES vvtgrundlage (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvtgrundlage_team ADD CONSTRAINT FK_B09EF10C296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvtpersonen_team ADD CONSTRAINT FK_F430C11097064E08 FOREIGN KEY (vvtpersonen_id) REFERENCES vvtpersonen (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvtpersonen_team ADD CONSTRAINT FK_F430C110296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvtrisiken_team ADD CONSTRAINT FK_ACD5CD76DF7C8617 FOREIGN KEY (vvtrisiken_id) REFERENCES vvtrisiken (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvtrisiken_team ADD CONSTRAINT FK_ACD5CD76296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvtstatus_team ADD CONSTRAINT FK_692A2281D4694B3A FOREIGN KEY (vvtstatus_id) REFERENCES vvtstatus (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vvtstatus_team ADD CONSTRAINT FK_692A2281296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE audit_tom_ziele ADD inherited TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE datenweitergabe_grundlagen ADD inherited TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE datenweitergabe_stand ADD inherited TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE produkte ADD inherited TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE settings ADD group_mapping INT DEFAULT 0 NOT NULL, DROP use_keycloak_groups');
        $this->addSql('DROP INDEX UNIQ_C4E0A61F5E237E06 ON team');
        $this->addSql('ALTER TABLE team ADD tree_root INT DEFAULT NULL, ADD parent_id INT DEFAULT NULL, ADD lvl INT DEFAULT NULL, ADD lft INT DEFAULT NULL, ADD rgt INT DEFAULT NULL, ADD immutable TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FA977936C FOREIGN KEY (tree_root) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F727ACA70 FOREIGN KEY (parent_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C4E0A61FA977936C ON team (tree_root)');
        $this->addSql('CREATE INDEX IDX_C4E0A61F727ACA70 ON team (parent_id)');
        $this->addSql('CREATE UNIQUE INDEX UNQ_team_name_and_immutable ON team (name, immutable)');
        $this->addSql('ALTER TABLE vvt ADD inherited TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE vvtgrundlage ADD inherited TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE vvtpersonen ADD inherited TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE vvtrisiken ADD inherited TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE vvtstatus ADD inherited TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audit_tom_ziele_team DROP FOREIGN KEY FK_69170ACF24B29C5F');
        $this->addSql('ALTER TABLE audit_tom_ziele_team DROP FOREIGN KEY FK_69170ACF296CD8AE');
        $this->addSql('ALTER TABLE datenweitergabe_grundlagen_team DROP FOREIGN KEY FK_1D5E1D65832C7682');
        $this->addSql('ALTER TABLE datenweitergabe_grundlagen_team DROP FOREIGN KEY FK_1D5E1D65296CD8AE');
        $this->addSql('ALTER TABLE datenweitergabe_stand_team DROP FOREIGN KEY FK_105357981361291D');
        $this->addSql('ALTER TABLE datenweitergabe_stand_team DROP FOREIGN KEY FK_10535798296CD8AE');
        $this->addSql('ALTER TABLE produkte_team DROP FOREIGN KEY FK_4E9D9226F5AB0B87');
        $this->addSql('ALTER TABLE produkte_team DROP FOREIGN KEY FK_4E9D9226296CD8AE');
        $this->addSql('ALTER TABLE vvt_team DROP FOREIGN KEY FK_4FF1CCA1677671F9');
        $this->addSql('ALTER TABLE vvt_team DROP FOREIGN KEY FK_4FF1CCA1296CD8AE');
        $this->addSql('ALTER TABLE vvtgrundlage_team DROP FOREIGN KEY FK_B09EF10C80257D66');
        $this->addSql('ALTER TABLE vvtgrundlage_team DROP FOREIGN KEY FK_B09EF10C296CD8AE');
        $this->addSql('ALTER TABLE vvtpersonen_team DROP FOREIGN KEY FK_F430C11097064E08');
        $this->addSql('ALTER TABLE vvtpersonen_team DROP FOREIGN KEY FK_F430C110296CD8AE');
        $this->addSql('ALTER TABLE vvtrisiken_team DROP FOREIGN KEY FK_ACD5CD76DF7C8617');
        $this->addSql('ALTER TABLE vvtrisiken_team DROP FOREIGN KEY FK_ACD5CD76296CD8AE');
        $this->addSql('ALTER TABLE vvtstatus_team DROP FOREIGN KEY FK_692A2281D4694B3A');
        $this->addSql('ALTER TABLE vvtstatus_team DROP FOREIGN KEY FK_692A2281296CD8AE');
        $this->addSql('DROP TABLE audit_tom_ziele_team');
        $this->addSql('DROP TABLE datenweitergabe_grundlagen_team');
        $this->addSql('DROP TABLE datenweitergabe_stand_team');
        $this->addSql('DROP TABLE produkte_team');
        $this->addSql('DROP TABLE vvt_team');
        $this->addSql('DROP TABLE vvtgrundlage_team');
        $this->addSql('DROP TABLE vvtpersonen_team');
        $this->addSql('DROP TABLE vvtrisiken_team');
        $this->addSql('DROP TABLE vvtstatus_team');
        $this->addSql('ALTER TABLE audit_tom_ziele DROP inherited');
        $this->addSql('ALTER TABLE datenweitergabe_grundlagen DROP inherited');
        $this->addSql('ALTER TABLE datenweitergabe_stand DROP inherited');
        $this->addSql('ALTER TABLE produkte DROP inherited');
        $this->addSql('ALTER TABLE settings ADD use_keycloak_groups TINYINT(1) DEFAULT NULL, DROP group_mapping');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FA977936C');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F727ACA70');
        $this->addSql('DROP INDEX IDX_C4E0A61FA977936C ON team');
        $this->addSql('DROP INDEX IDX_C4E0A61F727ACA70 ON team');
        $this->addSql('DROP INDEX UNQ_team_name_and_immutable ON team');
        $this->addSql('ALTER TABLE team DROP tree_root, DROP parent_id, DROP lvl, DROP lft, DROP rgt, DROP immutable');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C4E0A61F5E237E06 ON team (name)');
        $this->addSql('ALTER TABLE vvt DROP inherited');
        $this->addSql('ALTER TABLE vvtgrundlage DROP inherited');
        $this->addSql('ALTER TABLE vvtpersonen DROP inherited');
        $this->addSql('ALTER TABLE vvtrisiken DROP inherited');
        $this->addSql('ALTER TABLE vvtstatus DROP inherited');
    }
}
