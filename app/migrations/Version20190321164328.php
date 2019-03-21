<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190321164328 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD linked_in_page_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE adherents a
        JOIN referent r ON r.email_address = a.email_address AND r.first_name = a.first_name AND r.last_name = a.last_name
        SET a.description       = r.description,
            a.twitter_page_url  = r.twitter_page_url,
            a.facebook_page_url = r.facebook_page_url,
            a.media_id = r.media_id
        WHERE a.managed_area_id > 0
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP linked_in_page_url');
    }
}
