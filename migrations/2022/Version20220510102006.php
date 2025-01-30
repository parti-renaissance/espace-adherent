<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220510102006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies DROP french_request_available, DROP foreign_request_available');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          procuration_proxies
        ADD
          french_request_available TINYINT(1) DEFAULT \'1\' NOT NULL,
        ADD
          foreign_request_available TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
