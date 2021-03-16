<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190204155307 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ideas_workshop_idea_notification_dates (last_date DATETIME DEFAULT NULL, caution_last_date DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO ideas_workshop_idea_notification_dates VALUES (NULL, NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ideas_workshop_idea_notification_dates');
    }
}
