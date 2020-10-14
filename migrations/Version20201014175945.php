<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201014175945 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council_convocation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          territorial_council_id INT UNSIGNED DEFAULT NULL, 
          political_committee_id INT UNSIGNED DEFAULT NULL, 
          created_by_id INT UNSIGNED DEFAULT NULL, 
          meeting_start_date DATETIME NOT NULL, 
          meeting_end_date DATETIME NOT NULL, 
          description LONGTEXT NOT NULL, 
          mode VARCHAR(255) NOT NULL, 
          meeting_url VARCHAR(255) DEFAULT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          address_address VARCHAR(150) DEFAULT NULL, 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
          address_city_name VARCHAR(255) DEFAULT NULL, 
          address_country VARCHAR(2) DEFAULT NULL, 
          address_region VARCHAR(255) DEFAULT NULL, 
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', 
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL, 
          INDEX IDX_A9919BF0AAA61A99 (territorial_council_id), 
          INDEX IDX_A9919BF0C7A72 (political_committee_id), 
          INDEX IDX_A9919BF0B03A8386 (created_by_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          territorial_council_convocation 
        ADD 
          CONSTRAINT FK_A9919BF0AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id)');
        $this->addSql('ALTER TABLE 
          territorial_council_convocation 
        ADD 
          CONSTRAINT FK_A9919BF0C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id)');
        $this->addSql('ALTER TABLE 
          territorial_council_convocation 
        ADD 
          CONSTRAINT FK_A9919BF0B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE territorial_council_convocation');
    }
}
