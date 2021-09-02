<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210901110345 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey ADD uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('UPDATE jecoute_data_survey SET uuid = UUID()');
        $this->addSql('ALTER TABLE jecoute_data_survey CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE jecoute_data_answer DROP FOREIGN KEY FK_12FB393E3C5110AB');
        $this->addSql('ALTER TABLE jemarche_data_survey DROP FOREIGN KEY FK_8DF5D8183C5110AB');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC1911983C5110AB');

        $this->addSql('ALTER TABLE jecoute_data_answer CHANGE data_survey_id data_survey_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_data_survey CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        CHANGE
          data_survey_id data_survey_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        CHANGE
          data_survey_id data_survey_id INT UNSIGNED DEFAULT NULL');

        $this->addSql('ALTER TABLE
          jecoute_data_answer
        ADD
          CONSTRAINT FK_12FB393E3C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        ADD
          CONSTRAINT FK_8DF5D8183C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC1911983C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE
          jemarche_data_survey
        ADD
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
        ADD
          created_at DATETIME DEFAULT NULL,
        ADD
          updated_at DATETIME DEFAULT NULL');

        $this->addSql(
            'UPDATE jemarche_data_survey AS t1 INNER JOIN jecoute_data_survey AS t2 ON t2.id = t1.data_survey_id
            SET t1.created_at = t2.posted_at, t1.updated_at = t2.posted_at'
        );

        $this->addSql('ALTER TABLE
          jemarche_data_survey
        CHANGE created_at created_at DATETIME NOT NULL,
        CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey DROP uuid');

        $this->addSql('ALTER TABLE jecoute_data_answer DROP FOREIGN KEY FK_12FB393E3C5110AB');
        $this->addSql('ALTER TABLE jemarche_data_survey DROP FOREIGN KEY FK_8DF5D8183C5110AB');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC1911983C5110AB');

        $this->addSql('ALTER TABLE jecoute_data_answer CHANGE data_survey_id data_survey_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_data_survey CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE jemarche_data_survey CHANGE data_survey_id data_survey_id INT NOT NULL');
        $this->addSql('ALTER TABLE phoning_campaign_history CHANGE data_survey_id data_survey_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE
          jecoute_data_answer
        ADD
          CONSTRAINT FK_12FB393E3C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        ADD
          CONSTRAINT FK_8DF5D8183C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC1911983C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE jemarche_data_survey DROP uuid, DROP created_at, DROP updated_at');
    }
}
