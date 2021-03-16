<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210324150101 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_election DROP questions');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          territorial_council_election 
        ADD 
          questions LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
