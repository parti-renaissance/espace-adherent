<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170614200628 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users CHANGE postal_code postal_code VARCHAR(15) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users CHANGE postal_code postal_code VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
    }
}
