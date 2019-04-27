<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190304170145 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          ideas_workshop_idea 
        ADD 
          extensions_count SMALLINT UNSIGNED NOT NULL, 
        ADD 
          last_extension_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP extensions_count, DROP last_extension_date');
    }
}
