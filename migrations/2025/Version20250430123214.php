<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250430123214 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_session ADD ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          app_session_push_token_link
        CHANGE
          last_active_date last_activity_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE push_token CHANGE last_active_date last_activity_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_session DROP ip');
        $this->addSql('ALTER TABLE
          app_session_push_token_link
        CHANGE
          last_activity_date last_active_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE push_token CHANGE last_activity_date last_active_date DATETIME DEFAULT NULL');
    }
}
