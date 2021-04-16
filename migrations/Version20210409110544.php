<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210409110544 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_data_survey
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey DROP latitude, DROP longitude');
    }
}
