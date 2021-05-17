<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210517124456 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          geo_borough
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_canton
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_city_community
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_consular_district
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_country
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_custom_zone
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_department
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_district
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_foreign_district
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_region
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          geo_zone
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_borough DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_canton DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_city DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_city_community DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_consular_district DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_country DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_custom_zone DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_department DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_district DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_foreign_district DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_region DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE geo_zone DROP latitude, DROP longitude');
    }
}
