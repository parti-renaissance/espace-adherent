<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210824164818 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jemarche_data_survey (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          data_survey_id INT NOT NULL,
          device_id INT UNSIGNED DEFAULT NULL,
          first_name VARCHAR(50) DEFAULT NULL,
          last_name VARCHAR(50) DEFAULT NULL,
          email_address VARCHAR(255) DEFAULT NULL,
          agreed_to_stay_in_contact TINYINT(1) NOT NULL,
          agreed_to_contact_for_join TINYINT(1) NOT NULL,
          agreed_to_treat_personal_data TINYINT(1) NOT NULL,
          postal_code VARCHAR(5) DEFAULT NULL,
          profession VARCHAR(30) DEFAULT NULL,
          age_range VARCHAR(15) DEFAULT NULL,
          gender VARCHAR(15) DEFAULT NULL,
          gender_other VARCHAR(50) DEFAULT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          UNIQUE INDEX UNIQ_8DF5D8183C5110AB (data_survey_id),
          INDEX IDX_8DF5D81894A4C7D4 (device_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phoning_data_survey (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          data_survey_id INT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          type VARCHAR(10) DEFAULT NULL,
          status VARCHAR(25) NOT NULL,
          postal_code_checked TINYINT(1) NOT NULL,
          call_more TINYINT(1) NOT NULL,
          need_renewal TINYINT(1) NOT NULL,
          become_caller TINYINT(1) NOT NULL,
          begin_at DATETIME NOT NULL,
          finish_at DATETIME DEFAULT NULL,
          UNIQUE INDEX UNIQ_16BB9BA53C5110AB (data_survey_id),
          INDEX IDX_16BB9BA525F06C53 (adherent_id),
          INDEX IDX_16BB9BA5F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        ADD
          CONSTRAINT FK_8DF5D8183C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        ADD
          CONSTRAINT FK_8DF5D81894A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          phoning_data_survey
        ADD
          CONSTRAINT FK_16BB9BA53C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_data_survey
        ADD
          CONSTRAINT FK_16BB9BA525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_data_survey
        ADD
          CONSTRAINT FK_16BB9BA5F639F774 FOREIGN KEY (campaign_id) REFERENCES phoning_campaign (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jemarche_data_survey');
        $this->addSql('DROP TABLE phoning_data_survey');
    }
}
