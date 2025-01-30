<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240327081758 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          events
        CHANGE
          visio_url visio_url LONGTEXT DEFAULT NULL,
        CHANGE
          live_url live_url LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          events
        CHANGE
          visio_url visio_url VARCHAR(255) DEFAULT NULL,
        CHANGE
          live_url live_url VARCHAR(255) DEFAULT NULL');
    }
}
