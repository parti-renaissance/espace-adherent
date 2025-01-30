<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240724122946 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sms_campaign DROP FOREIGN KEY FK_79E333DC4B09E92C');
        $this->addSql('ALTER TABLE sms_campaign DROP FOREIGN KEY FK_79E333DC848CC616');
        $this->addSql('DROP TABLE sms_campaign');
        $this->addSql('DROP TABLE sms_stop_history');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE sms_campaign (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          audience_id INT UNSIGNED DEFAULT NULL,
          administrator_id INT DEFAULT NULL,
          title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          STATUS VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          recipient_count INT DEFAULT NULL,
          response_payload LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          external_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          sent_at DATETIME DEFAULT NULL,
          adherent_count INT DEFAULT NULL,
          INDEX IDX_79E333DC4B09E92C (administrator_id),
          UNIQUE INDEX UNIQ_79E333DC848CC616 (audience_id),
          UNIQUE INDEX UNIQ_79E333DCD17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE sms_stop_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          event_date DATETIME DEFAULT NULL,
          campaign_external_id INT DEFAULT NULL,
          receiver VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_E761AF89D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          sms_campaign
        ADD
          CONSTRAINT FK_79E333DC4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          sms_campaign
        ADD
          CONSTRAINT FK_79E333DC848CC616 FOREIGN KEY (audience_id) REFERENCES audience_snapshot (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
