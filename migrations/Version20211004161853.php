<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211004161853 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jemarche_data_survey DROP FOREIGN KEY FK_8DF5D8183C5110AB');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        ADD
          CONSTRAINT FK_8DF5D8183C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC1911983C5110AB');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC1911983C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jemarche_data_survey DROP FOREIGN KEY FK_8DF5D8183C5110AB');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        ADD
          CONSTRAINT FK_8DF5D8183C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phoning_campaign_history DROP FOREIGN KEY FK_EC1911983C5110AB');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          CONSTRAINT FK_EC1911983C5110AB FOREIGN KEY (data_survey_id) REFERENCES jecoute_data_survey (id) ON DELETE CASCADE');
    }
}
