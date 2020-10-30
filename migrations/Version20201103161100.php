<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20201103161100 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->executeQuery(
            'INSERT INTO event_group_category (`name`, `slug`) VALUES (\'Évènements de campagne\', \'evenements-de-campagne\')',
        );

        $categoryId = $this->connection->lastInsertId();

        $this->connection->insert('events_categories', [
            'name' => 'Élections régionales',
            'slug' => 'elections-regionales',
            'event_group_category_id' => $categoryId,
        ]);

        $this->connection->insert('events_categories', [
            'name' => 'Élections départementales',
            'slug' => 'elections-departementales',
            'event_group_category_id' => $categoryId,
        ]);
    }

    public function down(Schema $schema)
    {
    }
}
