<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221106232616 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commitment CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE jecoute_choice CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE jecoute_resource_link CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE jecoute_survey_question CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE mooc_chapter CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE mooc_elements CHANGE position position INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE commitment CHANGE position position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE jecoute_choice CHANGE position position SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE jecoute_resource_link CHANGE position position SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE jecoute_survey_question CHANGE position position SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE mooc_chapter CHANGE position position SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE mooc_elements CHANGE position position SMALLINT NOT NULL');
    }
}
