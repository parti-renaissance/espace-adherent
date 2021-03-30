<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170614200628 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users CHANGE postal_code postal_code VARCHAR(15) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users CHANGE postal_code postal_code VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
    }
}
