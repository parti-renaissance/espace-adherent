<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170216040153 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE newsletter_subscriptions CHANGE postal_code postal_code VARCHAR(11) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE newsletter_subscriptions CHANGE postal_code postal_code VARCHAR(11) NOT NULL COLLATE utf8_unicode_ci');
    }
}
