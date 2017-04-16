<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170416220534 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DROP INDEX facebook_profile_email_address ON facebook_profiles');
    }

    public function down(Schema $schema)
    {
        $this->addSql('CREATE UNIQUE INDEX facebook_profile_email_address ON facebook_profiles (email_address)');
    }
}
