<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241128231215 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3122E5FF4');
        $this->addSql('ALTER TABLE consular_managed_area DROP FOREIGN KEY FK_7937A51292CA96FD');
        $this->addSql('DROP TABLE consular_district');
        $this->addSql('DROP TABLE consular_managed_area');
        $this->addSql('DROP INDEX UNIQ_562C7DA3122E5FF4 ON adherents');
        $this->addSql('ALTER TABLE
          adherents
        DROP
          consular_managed_area_id,
        DROP
          remind_sent,
        DROP
          global_notification_sent_at,
        DROP
          activation_reminded_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE consular_district (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          countries LONGTEXT CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
            cities LONGTEXT CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
            code VARCHAR(255) CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
            number SMALLINT NOT NULL,
            points JSON DEFAULT NULL,
            UNIQUE INDEX UNIQ_77152B8877153098 (code),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE consular_managed_area (
          id INT AUTO_INCREMENT NOT NULL,
          consular_district_id INT UNSIGNED DEFAULT NULL,
          INDEX IDX_7937A51292CA96FD (consular_district_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          consular_managed_area
        ADD
          CONSTRAINT FK_7937A51292CA96FD FOREIGN KEY (consular_district_id) REFERENCES consular_district (id) ON
        UPDATE
          NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          consular_managed_area_id INT DEFAULT NULL,
        ADD
          remind_sent TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          global_notification_sent_at DATETIME DEFAULT NULL,
        ADD
          activation_reminded_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA3122E5FF4 FOREIGN KEY (consular_managed_area_id) REFERENCES consular_managed_area (id) ON
        UPDATE
          NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3122E5FF4 ON adherents (consular_managed_area_id)');
    }
}
