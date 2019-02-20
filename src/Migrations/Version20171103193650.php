<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171103193650 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD group_id INT UNSIGNED DEFAULT NULL, ADD mooc_event_category_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AFE54D947 FOREIGN KEY (group_id) REFERENCES groups (id)');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574ABE3E9D45 FOREIGN KEY (mooc_event_category_id) REFERENCES mooc_event_categories (id)');
        $this->addSql('CREATE INDEX IDX_5387574AFE54D947 ON events (group_id)');
        $this->addSql('CREATE INDEX IDX_5387574ABE3E9D45 ON events (mooc_event_category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AFE54D947');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574ABE3E9D45');
        $this->addSql('DROP INDEX IDX_5387574AFE54D947 ON events');
        $this->addSql('DROP INDEX IDX_5387574ABE3E9D45 ON events');
        $this->addSql('ALTER TABLE events DROP group_id, DROP mooc_event_category_id');
    }
}
