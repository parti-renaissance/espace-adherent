<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210930164036 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          permanent TINYINT(1) DEFAULT \'0\' NOT NULL,
        CHANGE
          team_id team_id INT UNSIGNED DEFAULT NULL,
        CHANGE
          audience_id audience_id INT UNSIGNED DEFAULT NULL,
        CHANGE
          finish_at finish_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          phoning_campaign
        DROP
          permanent,
        CHANGE
          team_id team_id INT UNSIGNED NOT NULL,
        CHANGE
          audience_id audience_id INT UNSIGNED NOT NULL,
        CHANGE
          finish_at finish_at DATETIME NOT NULL');
    }
}
