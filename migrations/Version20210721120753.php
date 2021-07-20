<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210721120753 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audience (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          zone_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          first_name VARCHAR(255) DEFAULT NULL,
          last_name VARCHAR(255) DEFAULT NULL,
          gender VARCHAR(6) DEFAULT NULL,
          age_min INT DEFAULT NULL,
          age_max INT DEFAULT NULL,
          registered_since DATE DEFAULT NULL,
          registered_until DATE DEFAULT NULL,
          is_committee_member TINYINT(1) DEFAULT NULL,
          is_certified TINYINT(1) DEFAULT NULL,
          has_email_subscription TINYINT(1) DEFAULT NULL,
          subscription_type VARCHAR(50) DEFAULT NULL,
          has_sms_subscription TINYINT(1) DEFAULT NULL,
          INDEX IDX_FDCD94189F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          audience
        ADD
          CONSTRAINT FK_FDCD94189F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audience');
    }
}
