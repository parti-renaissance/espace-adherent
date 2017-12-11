<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171203025556 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE referent_managed_users_message CHANGE query_area_code query_area_code LONGTEXT NOT NULL, CHANGE query_city query_city LONGTEXT NOT NULL, CHANGE query_id query_id LONGTEXT NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE referent_managed_users_message CHANGE query_area_code query_area_code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE query_city query_city VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE query_id query_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
