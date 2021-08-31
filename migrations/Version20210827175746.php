<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210827175746 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE phoning_campaign_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          data_survey_id INT DEFAULT NULL,
          caller_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          type VARCHAR(10) DEFAULT NULL,
          status VARCHAR(25) NOT NULL,
          postal_code_checked TINYINT(1) DEFAULT NULL,
          call_more TINYINT(1) DEFAULT NULL,
          need_renewal TINYINT(1) DEFAULT NULL,
          become_caller TINYINT(1) DEFAULT NULL,
          begin_at DATETIME NOT NULL,
          finish_at DATETIME DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_EC1911983C5110AB (data_survey_id),
          INDEX IDX_EC191198A5626C52 (caller_id),
          INDEX IDX_EC19119825F06C53 (adherent_id),
          INDEX IDX_EC191198F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC1911983C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC191198A5626C52 FOREIGN KEY (caller_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC19119825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC191198F639F774 FOREIGN KEY (campaign_id) REFERENCES phoning_campaign (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE phoning_data_survey');
        $this->addSql('ALTER TABLE adherents ADD phoning_campaign_call_more_status TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE phoning_data_survey (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          data_survey_id INT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          campaign_id INT UNSIGNED NOT NULL,
          type VARCHAR(10) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          status VARCHAR(25) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          postal_code_checked TINYINT(1) NOT NULL,
          call_more TINYINT(1) NOT NULL,
          need_renewal TINYINT(1) NOT NULL,
          become_caller TINYINT(1) NOT NULL,
          begin_at DATETIME NOT NULL,
          finish_at DATETIME DEFAULT NULL,
          INDEX IDX_16BB9BA525F06C53 (adherent_id),
          UNIQUE INDEX UNIQ_16BB9BA53C5110AB (data_survey_id),
          INDEX IDX_16BB9BA5F639F774 (campaign_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          phoning_data_survey
        ADD
          CONSTRAINT FK_16BB9BA525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_data_survey
        ADD
          CONSTRAINT FK_16BB9BA53C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_data_survey
        ADD
          CONSTRAINT FK_16BB9BA5F639F774 FOREIGN KEY (campaign_id) REFERENCES phoning_campaign (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE phoning_campaign_history');
        $this->addSql('ALTER TABLE adherents DROP phoning_campaign_call_more_status');
    }
}
