<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211022155427 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_campaign_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          questioner_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          data_survey_id INT UNSIGNED DEFAULT NULL,
          status VARCHAR(25) NOT NULL,
          building VARCHAR(255) DEFAULT NULL,
          floor SMALLINT UNSIGNED DEFAULT NULL,
          door VARCHAR(255) DEFAULT NULL,
          first_name VARCHAR(255) DEFAULT NULL,
          last_name VARCHAR(255) DEFAULT NULL,
          email_address VARCHAR(255) DEFAULT NULL,
          gender VARCHAR(15) DEFAULT NULL,
          age_range VARCHAR(15) DEFAULT NULL,
          profession VARCHAR(30) DEFAULT NULL,
          to_contact TINYINT(1) DEFAULT NULL,
          to_join TINYINT(1) DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          finish_at DATETIME DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_5A3F26F7CC0DE6E1 (questioner_id),
          INDEX IDX_5A3F26F725F06C53 (adherent_id),
          INDEX IDX_5A3F26F7F639F774 (campaign_id),
          UNIQUE INDEX UNIQ_5A3F26F73C5110AB (data_survey_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F7CC0DE6E1 FOREIGN KEY (questioner_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F7F639F774 FOREIGN KEY (campaign_id) REFERENCES pap_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F73C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE pap_campaign DROP FOREIGN KEY FK_15CB2432B3FE509D');
        $this->addSql('ALTER TABLE pap_campaign CHANGE survey_id survey_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_EF50C8E8B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pap_campaign_history');
        $this->addSql('ALTER TABLE pap_campaign DROP FOREIGN KEY FK_EF50C8E8B3FE509D');
        $this->addSql('ALTER TABLE pap_campaign CHANGE survey_id survey_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_15CB2432B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id) ON DELETE
        SET
          NULL');
    }
}
