<?php

namespace Migrations;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180219104140 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_action_categories ADD slug VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE events_categories ADD slug VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE citizen_project_categories ADD slug VARCHAR(100) NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        foreach ($this->connection->fetchAll('SELECT id, name FROM citizen_action_categories') as $category) {
            $slugify = new Slugify();
            $slug = $slugify->slugify($category['name']);

            $this->connection->executeUpdate(
                'UPDATE citizen_action_categories SET slug = ? WHERE id = ?',
                [$slug, $category['id']]
            );
        }

        foreach ($this->connection->fetchAll('SELECT id, name FROM events_categories') as $category) {
            $slugify = new Slugify();
            $slug = $slugify->slugify($category['name']);

            $this->connection->executeUpdate(
                'UPDATE events_categories SET slug = ? WHERE id = ?',
                [$slug, $category['id']]
            );
        }

        foreach ($this->connection->fetchAll('SELECT id, name FROM citizen_project_categories') as $category) {
            $slugify = new Slugify();
            $slug = $slugify->slugify($category['name']);

            $this->connection->executeUpdate(
                'UPDATE citizen_project_categories SET slug = ? WHERE id = ?',
                [$slug, $category['id']]
            );
        }

        $this->connection->executeQuery('CREATE UNIQUE INDEX citizen_action_category_slug_unique ON citizen_action_categories (slug)');
        $this->connection->executeQuery('CREATE UNIQUE INDEX event_category_slug_unique ON events_categories (slug)');
        $this->connection->executeQuery('CREATE UNIQUE INDEX citizen_project_category_slug_unique ON citizen_project_categories (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX citizen_action_category_slug_unique ON citizen_action_categories');
        $this->addSql('ALTER TABLE citizen_action_categories DROP slug');
        $this->addSql('DROP INDEX citizen_project_category_slug_unique ON citizen_project_categories');
        $this->addSql('ALTER TABLE citizen_project_categories DROP slug');
        $this->addSql('DROP INDEX event_category_slug_unique ON events_categories');
        $this->addSql('ALTER TABLE events_categories DROP slug');
    }
}
