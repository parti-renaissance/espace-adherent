<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170322112107 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_requests CHANGE postal_code postal_code VARCHAR(15) DEFAULT NULL, CHANGE vote_postal_code vote_postal_code VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_proxies CHANGE postal_code postal_code VARCHAR(15) DEFAULT NULL, CHANGE vote_postal_code vote_postal_code VARCHAR(15) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies CHANGE postal_code postal_code VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, CHANGE vote_postal_code vote_postal_code VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE procuration_requests CHANGE postal_code postal_code VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, CHANGE vote_postal_code vote_postal_code VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
    }
}
