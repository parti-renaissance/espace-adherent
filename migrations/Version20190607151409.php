<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190607151409 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE referent_managed_users_message 
            SET query_area_code = CONCAT_WS(', ', query_area_code, query_city)
            WHERE query_area_code != '' AND query_city != ''"
        );

        $this->addSql(
            "UPDATE referent_managed_users_message 
            SET query_area_code = query_city
            WHERE query_area_code = '' AND query_city != ''"
        );

        $this->addSql('ALTER TABLE referent_managed_users_message DROP query_city');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          referent_managed_users_message 
        ADD 
          query_city LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
    }
}
