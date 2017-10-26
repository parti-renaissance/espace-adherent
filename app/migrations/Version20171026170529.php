<?php

namespace Migrations;

use AppBundle\DataFixtures\ORM\LoadMoocEventCategoryData;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171026170529 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE mooc_event_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX mooc_event_category_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function postUp(Schema $schema)
    {
        foreach (LoadMoocEventCategoryData::MOOC_EVENT_CATEGORIES as $category) {
            $this->connection->insert('mooc_event_categories', ['name' => $category]);
        }
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE mooc_event_categories');
    }
}
