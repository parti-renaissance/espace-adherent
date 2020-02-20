<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200220114308 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          vote_result 
        DROP 
          INDEX UNIQ_1F8DB349F3F90B30, 
        ADD 
          INDEX IDX_1F8DB349F3F90B30 (vote_place_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          vote_result 
        DROP 
          INDEX IDX_1F8DB349F3F90B30, 
        ADD 
          UNIQUE INDEX UNIQ_1F8DB349F3F90B30 (vote_place_id)');
    }
}
