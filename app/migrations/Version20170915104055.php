<?php

namespace Migrations;

use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeCategoryData;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170915104055 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->connection->executeUpdate('UPDATE events SET citizen_initiative_category_id = NULL');
        $this->connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
        $this->connection->executeQuery('TRUNCATE TABLE citizen_initiative_categories');
        $this->connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1');

        foreach (LoadCitizenInitiativeCategoryData::CITIZEN_INITIATIVE_CATEGORIES as $category) {
            $this->connection->insert('citizen_initiative_categories', ['name' => $category]);
        }

        $this->connection->executeUpdate("UPDATE events SET citizen_initiative_category_id = 1 WHERE type = 'citizen_initiative'");
    }

    public function down(Schema $schema)
    {
    }
}
