<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210902172856 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE phoning_campaign ADD survey_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA4B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id)');
        $this->addSql('CREATE INDEX IDX_C3882BA4B3FE509D ON phoning_campaign (survey_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE phoning_campaign DROP FOREIGN KEY FK_C3882BA4B3FE509D');
        $this->addSql('DROP INDEX IDX_C3882BA4B3FE509D ON phoning_campaign');
        $this->addSql('ALTER TABLE phoning_campaign DROP survey_id');
    }
}
