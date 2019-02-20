<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171208171018 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A12469DE2');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A712CD107');
        $this->addSql('DROP INDEX IDX_5387574A712CD107 ON events');
        $this->addSql('ALTER TABLE events DROP citizen_action_category_id');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events ADD citizen_action_category_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574A12469DE2 FOREIGN KEY (category_id) REFERENCES events_categories (id)');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574A712CD107 FOREIGN KEY (citizen_action_category_id) REFERENCES citizen_action_categories (id)');
        $this->addSql('CREATE INDEX IDX_5387574A712CD107 ON events (citizen_action_category_id)');
    }
}
