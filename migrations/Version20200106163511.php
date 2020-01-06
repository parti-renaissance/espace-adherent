<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200106163511 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          senator_area 
        DROP 
          INDEX UNIQ_D229BBF7AEC89CE1, 
        ADD 
          INDEX IDX_D229BBF7AEC89CE1 (department_tag_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          senator_area 
        DROP 
          INDEX IDX_D229BBF7AEC89CE1, 
        ADD 
          UNIQUE INDEX UNIQ_D229BBF7AEC89CE1 (department_tag_id)');
    }
}
