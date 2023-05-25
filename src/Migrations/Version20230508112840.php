<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Util\RelationConverter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230508112840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $user = $schema->getTable('user');
        $team = $schema->getTable('team');

        RelationConverter::createManyToManyMappingTable(
            $schema,
            $user,
            $team,
            'user_team',
        );

        RelationConverter::createManyToManyMappingTable(
            $schema,
            $user,
            $team,
            'team_admin',
        );

        $user->removeForeignKey('FK_957A6479296CD8AE');
        $user->removeForeignKey('FK_957A64796352511C');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
