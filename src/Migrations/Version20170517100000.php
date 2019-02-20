<?php

namespace Migrations;

use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170517100000 extends AbstractMigration
{
    private $events;

    public function preUp(Schema $schema)
    {
        foreach ($this->connection->fetchAll('SELECT id, category FROM events') as $event) {
            $this->events[LoadEventCategoryData::LEGACY_EVENT_CATEGORIES[$event['category']]][] = $event['id'];
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
        foreach (LoadEventCategoryData::LEGACY_EVENT_CATEGORIES as $category) {
            $this->connection->insert('events_categories', ['name' => $category]);
        }

        foreach ($this->connection->fetchAll('SELECT id, name FROM events_categories') as $category) {
            if (isset($this->events[$category['name']])) {
                $this->connection->executeUpdate(
                    sprintf('UPDATE events SET category_id = ? WHERE id IN (%s)', implode(', ', array_fill(0, \count($this->events[$category['name']]), '?'))),
                    array_merge([$category['id']], $this->events[$category['name']])
                );
            }
        }
    }

    public function preDown(Schema $schema)
    {
        foreach ($this->connection->fetchAll('SELECT id, category_id FROM events') as $event) {
            $this->events[($event['category_id'] > 9 ? 'CE0' : 'CE00').$event['category_id']][] = $event['id'];
        }
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A12469DE2');
        $this->addSql('DROP INDEX IDX_5387574A12469DE2 ON events');
        $this->addSql('ALTER TABLE events ADD category VARCHAR(5) DEFAULT NULL COLLATE utf8_unicode_ci, DROP category_id');

        $this->addSql('DROP TABLE events_categories');
    }

    public function postDown(Schema $schema)
    {
        foreach ($this->events as $categoryId => $events) {
            $this->connection->executeUpdate(
                sprintf('UPDATE events SET category = ? WHERE id IN (%s)', implode(', ', array_fill(0, \count($events), '?'))),
                array_merge([$categoryId], $events)
            );
        }

        $this->connection->executeQuery('ALTER TABLE events MODIFY COLUMN category VARCHAR(5) NOT NULL');
        $this->connection->executeQuery('ALTER TABLE events ALTER COLUMN category DROP DEFAULT');
    }
}
