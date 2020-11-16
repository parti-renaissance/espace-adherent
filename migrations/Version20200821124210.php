<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200821124210 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A044544E891720');

        $this->addSql('ALTER TABLE territorial_council_election 
        ADD 
          uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        ADD
          election_mode VARCHAR(255) DEFAULT NULL,
        ADD 
          meeting_start_date DATETIME DEFAULT NULL, 
        ADD 
          meeting_end_date DATETIME DEFAULT NULL, 
        ADD 
          address_address VARCHAR(150) DEFAULT NULL, 
        ADD 
          address_postal_code VARCHAR(15) DEFAULT NULL, 
        ADD 
          address_city_insee VARCHAR(15) DEFAULT NULL, 
        ADD 
          address_city_name VARCHAR(255) DEFAULT NULL, 
        ADD 
          address_country VARCHAR(2) DEFAULT NULL, 
        ADD 
          address_region VARCHAR(255) DEFAULT NULL, 
        ADD 
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', 
        ADD 
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', 
        ADD 
          address_geocodable_hash VARCHAR(255) DEFAULT NULL,
        ADD 
          description LONGTEXT DEFAULT NULL, 
        ADD 
          questions LONGTEXT DEFAULT NULL
        ');

        $this->addSql('ALTER TABLE 
          committee_election 
        ADD 
          uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', 
        CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');

        $this->addSql('ALTER TABLE 
          committee_candidacy CHANGE committee_election_id committee_election_id INT UNSIGNED NOT NULL');

        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT FK_9A044544E891720 FOREIGN KEY (committee_election_id) REFERENCES committee_election (id) ON DELETE CASCADE');

        $this->addSql('UPDATE territorial_council_election SET uuid = UUID() WHERE uuid IS NULL');
        $this->addSql('UPDATE committee_election SET uuid = UUID() WHERE uuid IS NULL');

        $this->addSql('ALTER TABLE territorial_council_election CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE committee_election CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE designation ADD limited TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          committee_candidacy CHANGE committee_election_id committee_election_id INT NOT NULL');
        $this->addSql('ALTER TABLE committee_election 
            DROP uuid, 
            CHANGE id id INT AUTO_INCREMENT NOT NULL'
        );
        $this->addSql('ALTER TABLE territorial_council_election 
            DROP uuid,
            DROP meeting_start_date, 
            DROP meeting_end_date, 
            DROP election_mode, 
            DROP address_address, 
            DROP address_postal_code, 
            DROP address_city_insee, 
            DROP address_city_name, 
            DROP address_country, 
            DROP address_region, 
            DROP address_latitude, 
            DROP address_longitude, 
            DROP address_geocodable_hash,
            DROP description, 
            DROP questions');
        $this->addSql('ALTER TABLE designation DROP limited');
    }
}
