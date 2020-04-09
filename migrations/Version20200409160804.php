<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200409160804 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        DROP 
          INDEX UNIQ_B9F11DAE8BAC62AF, 
        ADD 
          INDEX IDX_B9F11DAE8BAC62AF (city_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        DROP 
          INDEX IDX_B9F11DAE8BAC62AF, 
        ADD 
          UNIQUE INDEX UNIQ_B9F11DAE8BAC62AF (city_id)');
    }
}
