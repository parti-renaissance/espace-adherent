<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250114174700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_group_category ADD alert LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE events_categories ADD alert LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_group_category DROP alert');
        $this->addSql('ALTER TABLE events_categories DROP alert');
    }
}
