<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180122164120 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE committees CHANGE address_country address_country VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents CHANGE gender gender VARCHAR(6) DEFAULT NULL, CHANGE position position VARCHAR(20) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', CHANGE address_country address_country VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE donations CHANGE address_country address_country VARCHAR(2) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE adherents CHANGE gender gender VARCHAR(6) NOT NULL COLLATE utf8_unicode_ci, CHANGE position position VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, CHANGE interests interests JSON NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\', CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE committees CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE donations CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
    }
}
