<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180105212824 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE timeline_measures CHANGE global major TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE timeline_themes_measures MODIFY id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP id, DROP featured');
        $this->addSql('ALTER TABLE timeline_themes_measures ADD PRIMARY KEY (measure_id, theme_id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE timeline_measures CHANGE major global TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE timeline_themes_measures ADD id BIGINT AUTO_INCREMENT NOT NULL UNIQUE FIRST, ADD featured TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE timeline_themes_measures ADD PRIMARY KEY (id)');
    }
}
