<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170523141753 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE procuration_proxies CHANGE vote_office vote_office VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE procuration_requests CHANGE vote_office vote_office VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE procuration_proxies CHANGE vote_office vote_office VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE procuration_requests CHANGE vote_office vote_office VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
