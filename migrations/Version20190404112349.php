<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190404112349 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          procuration_proxies 
        ADD 
          state VARCHAR(255) DEFAULT NULL, 
        ADD 
          reachable TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE 
          procuration_requests 
        ADD 
          state VARCHAR(255) DEFAULT NULL, 
        ADD 
          reachable TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies DROP state, DROP reachable');
        $this->addSql('ALTER TABLE procuration_requests DROP state, DROP reachable');
    }
}
