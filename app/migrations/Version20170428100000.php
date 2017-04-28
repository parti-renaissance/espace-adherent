<?php

namespace Migrations;

use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170428100000 extends AbstractMigration
{
    const CODES = [
        'CE001',
        'CE002',
        'CE003',
        'CE004',
        'CE005',
        'CE006',
        'CE007',
        'CE008',
        'CE009',
        'CE010',
        'CE011',
    ];

    private $events;

    public function preUp(Schema $schema)
    {
        foreach ($this->connection->fetchAll('SELECT id, category FROM events') as $event) {
            $this->events[$event['category']][] = $event['id'];
        }
    }

    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE events_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX event_category_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE events ADD category_id INT UNSIGNED DEFAULT NULL, DROP category');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574A12469DE2 FOREIGN KEY (category_id) REFERENCES events_categories (id)');
        $this->addSql('CREATE INDEX IDX_5387574A12469DE2 ON events (category_id)');
    }

    public function postUp(Schema $schema)
    {
        foreach (LoadEventCategoryData::CATEGORIES as $category) {
            $this->connection->insert('events_categories', ['name' => $category]);
        }

        $categories = $this->connection->fetchAll('SELECT id, name FROM events_categories ORDER BY id');

//        $categories = $this->connection->fetchAll('SELECT id, category FROM events');
//        if (count($this->adherents)) {
//            $this->connection->executeUpdate('UDPATE adherents SET legislative_candidate = 1 WHERE id IN(?)', [implode(',', $this->adherents)]);
//        }
//        $this->adherents = [];
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A12469DE2');
        $this->addSql('DROP INDEX IDX_5387574A12469DE2 ON events');
        $this->addSql('ALTER TABLE events ADD category VARCHAR(5) NOT NULL COLLATE utf8_unicode_ci, DROP category_id');

        $this->addSql('DROP TABLE events_categories');
    }
}
