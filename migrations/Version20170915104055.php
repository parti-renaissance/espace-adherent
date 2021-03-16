<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170915104055 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->connection->executeUpdate('UPDATE events SET citizen_initiative_category_id = NULL');
        $this->connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
        $this->connection->executeQuery('TRUNCATE TABLE citizen_initiative_categories');
        $this->connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1');
        $this->connection->executeUpdate("UPDATE events SET citizen_initiative_category_id = 1 WHERE type = 'citizen_initiative'");
    }

    public function down(Schema $schema): void
    {
    }
}
