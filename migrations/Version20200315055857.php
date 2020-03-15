<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200315055857 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE election_city_card ADD risk TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE 
          election_city_candidate 
        ADD 
          profile VARCHAR(255) DEFAULT NULL, 
        ADD 
          investiture_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE election_city_candidate DROP profile, DROP investiture_type');
        $this->addSql('ALTER TABLE election_city_card DROP risk');
    }
}
