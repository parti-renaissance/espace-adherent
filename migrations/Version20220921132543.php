<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220921132543 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        CHANGE
          vote_statuses_convocation_sent_at global_notification_sent_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        CHANGE
          global_notification_sent_at vote_statuses_convocation_sent_at DATETIME DEFAULT NULL');
    }
}
