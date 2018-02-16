<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170416192241 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE facebook_profiles CHANGE email_address email_address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE facebook_profiles CHANGE email_address email_address VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
