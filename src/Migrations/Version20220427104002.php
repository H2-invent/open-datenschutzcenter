<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220427104002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept CHANGE datenart datenart TEXT NOT NULL, CHANGE standartlf standartlf TINYTEXT NOT NULL, CHANGE loeschfrist loeschfrist TINYTEXT NOT NULL, CHANGE speicherorte speicherorte TEXT NOT NULL, CHANGE loeschbeauftragter loeschbeauftragter TINYTEXT NOT NULL, CHANGE beschreibung beschreibung TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE vvtdatenkategorie ADD datenarten LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE loeschkonzept CHANGE datenart datenart VARCHAR(511) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE standartlf standartlf VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE loeschfrist loeschfrist VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE speicherorte speicherorte VARCHAR(511) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE loeschbeauftragter loeschbeauftragter VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE beschreibung beschreibung VARCHAR(1023) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE vvtdatenkategorie DROP datenarten');
    }
}
