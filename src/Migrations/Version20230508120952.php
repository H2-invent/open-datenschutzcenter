<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Util\RelationConverter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230508120952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $user = $schema->getTable('user');

        RelationConverter::convert(
            $this->connection,
            $user,
            $user->getColumn('id'),
            $user->getColumn('team_id'),
            'user_team',
            'user_id',
            'team_id',
        );

        RelationConverter::convert(
            $this->connection,
            $user,
            $user->getColumn('id'),
            $user->getColumn('admin_user_id'),
            'team_admin',
            'user_id',
            'team_id',
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
