<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210820173926 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audience_snapshot (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
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
          has_sms_subscription TINYINT(1) DEFAULT NULL,
          scope VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_BA99FEBB9F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audience_snapshot_zone (
          audience_snapshot_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_10882DC0ACA633A8 (audience_snapshot_id),
          INDEX IDX_10882DC09F2C3FAB (zone_id),
          PRIMARY KEY(audience_snapshot_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sms_campaign (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          audience_id INT UNSIGNED DEFAULT NULL,
          administrator_id INT DEFAULT NULL,
          title VARCHAR(255) NOT NULL,
          content LONGTEXT NOT NULL,
          status VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_79E333DC848CC616 (audience_id),
          INDEX IDX_79E333DC4B09E92C (administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          audience_snapshot
        ADD
          CONSTRAINT FK_BA99FEBB9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          audience_snapshot_zone
        ADD
          CONSTRAINT FK_10882DC0ACA633A8 FOREIGN KEY (audience_snapshot_id) REFERENCES audience_snapshot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          audience_snapshot_zone
        ADD
          CONSTRAINT FK_10882DC09F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          sms_campaign
        ADD
          CONSTRAINT FK_79E333DC848CC616 FOREIGN KEY (audience_id) REFERENCES audience_snapshot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          sms_campaign
        ADD
          CONSTRAINT FK_79E333DC4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audience_snapshot_zone DROP FOREIGN KEY FK_10882DC0ACA633A8');
        $this->addSql('ALTER TABLE sms_campaign DROP FOREIGN KEY FK_79E333DC848CC616');
        $this->addSql('DROP TABLE audience_snapshot');
        $this->addSql('DROP TABLE audience_snapshot_zone');
        $this->addSql('DROP TABLE sms_campaign');
    }
}
