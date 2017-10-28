<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171029162552 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE events ADD mooc_event_category_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574ABE3E9D45 FOREIGN KEY (mooc_event_category_id) REFERENCES mooc_event_categories (id)');
        $this->addSql('CREATE INDEX IDX_5387574ABE3E9D45 ON events (mooc_event_category_id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574ABE3E9D45');
        $this->addSql('DROP INDEX IDX_5387574ABE3E9D45 ON events');
        $this->addSql('ALTER TABLE events DROP mooc_event_category_id');
    }
}
