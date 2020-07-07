<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200706145513 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          designation CHANGE candidacy_end_date candidacy_end_date DATETIME DEFAULT NULL, 
          CHANGE vote_start_date vote_start_date DATETIME DEFAULT NULL, 
          CHANGE vote_end_date vote_end_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          designation CHANGE candidacy_end_date candidacy_end_date DATETIME NOT NULL, 
          CHANGE vote_start_date vote_start_date DATETIME NOT NULL, 
          CHANGE vote_end_date vote_end_date DATETIME NOT NULL');
    }
}
