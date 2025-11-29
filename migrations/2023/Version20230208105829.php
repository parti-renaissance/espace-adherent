<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230208105829 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE general_meeting_report (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          title VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          date DATETIME NOT NULL,
          file_path VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          visibility VARCHAR(30) NOT NULL,
          UNIQUE INDEX UNIQ_6BA05833D17F50A6 (uuid),
          INDEX IDX_6BA058339DF5350C (created_by_administrator_id),
          INDEX IDX_6BA05833CF1918FF (updated_by_administrator_id),
          INDEX IDX_6BA0583385C9D733 (created_by_adherent_id),
          INDEX IDX_6BA05833DF6CFDC9 (updated_by_adherent_id),
          INDEX IDX_6BA058339F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          general_meeting_report
        ADD
          CONSTRAINT FK_6BA058339DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          general_meeting_report
        ADD
          CONSTRAINT FK_6BA05833CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          general_meeting_report
        ADD
          CONSTRAINT FK_6BA0583385C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          general_meeting_report
        ADD
          CONSTRAINT FK_6BA05833DF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          general_meeting_report
        ADD
          CONSTRAINT FK_6BA058339F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE general_meeting_report');
    }
}
