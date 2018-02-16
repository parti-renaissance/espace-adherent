<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170329230710 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE newsletter_subscriptions ADD updated_at DATETIME NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, DROP client_ip');
        $this->addSql('UPDATE newsletter_subscriptions SET updated_at = created_at');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE newsletter_subscriptions ADD client_ip VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, DROP updated_at, DROP deleted_at');
    }
}
