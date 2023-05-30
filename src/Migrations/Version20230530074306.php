<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\Util\Tables;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230530074306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add external dsb to admin';
    }

    public function up(Schema $schema): void
    {
        $teams = $this->connection->createQueryBuilder()
            ->select(select: 't.*')
            ->from(Tables::$TEAM, 't')
            ->fetchAllAssociative();

        $teamAdmins = $this->connection->createQueryBuilder()
            ->select(select: 'ta.*')
            ->from(Tables::$TEAM_ADMIN, 'ta')
            ->fetchAllAssociative();

        $teamMembers = $this->connection->createQueryBuilder()
            ->select(select: 'tu.*')
            ->from(Tables::$USER_TEAM, 'tu')
            ->fetchAllAssociative();

        foreach ($teams as $team) {
            if (!$this->exists(haystacks: $teamAdmins, needle: $team)) {
                $this->connection->createQueryBuilder()
                    ->insert(Tables::$TEAM_ADMIN)
                    ->setValue(column: 'team_id', value: $team['id'])
                    ->setValue(column: 'user_id', value: $team['dsb_user_id'])
                    ->executeQuery();
            }

            if(!$this->exists(haystacks: $teamMembers, needle: $team)){
                $this->connection->createQueryBuilder()
                    ->insert(Tables::$USER_TEAM)
                    ->setValue(column: 'team_id', value: $team['id'])
                    ->setValue(column: 'user_id', value: $team['dsb_user_id'])
                    ->executeQuery();
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    private function exists(array $haystacks, array $needle): bool
    {
        foreach ($haystacks as $haystack) {
            if ($needle['id'] === $haystack['team_id']
                && $needle['dsb_user_id'] === $haystack['user_id']) {
                return true;
            }
        }
        return false;
    }
}
