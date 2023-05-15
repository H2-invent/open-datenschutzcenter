<?php
declare(strict_types=1);

namespace App\Migrations\Templates;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

class CreateTable
{
    private static string $FIELD_ID = 'id';
    private static string $FIELD_CREATED_AT = 'created_at';
    private static string $FIELD_UPDATED_AT = 'updated_at';

    /** @throws SchemaException */
    public static function createTable(Schema $schema, string $tableName): Table
    {
        $table = $schema->createTable($tableName);

        $table->addColumn(self::$FIELD_ID, Types::INTEGER)
            ->setNotnull(true)
            ->setAutoincrement(true);

        foreach([self::$FIELD_CREATED_AT, self::$FIELD_UPDATED_AT] as $field){
            $table->addColumn($field, Types::DATETIME_IMMUTABLE)
                ->setNotnull(true);
        }

        $table->setPrimaryKey([self::$FIELD_ID, $tableName.'_PK']);

        return $table;
    }
}