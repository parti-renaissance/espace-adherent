<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170525190824 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE legislative_candidates ADD geojson LONGTEXT DEFAULT NULL, DROP latitude, DROP longitude');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE legislative_candidates ADD latitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', ADD longitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', DROP geojson');
    }
}
