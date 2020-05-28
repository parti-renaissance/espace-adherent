<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200528124038 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          certification_request 
        ADD 
          block_reason VARCHAR(30) DEFAULT NULL, 
        ADD 
          custom_block_reason LONGTEXT DEFAULT NULL, 
        ADD 
          block_comment LONGTEXT DEFAULT NULL, 
        ADD 
          refusal_reason VARCHAR(30) DEFAULT NULL, 
        ADD 
          custom_refusal_reason LONGTEXT DEFAULT NULL, 
        ADD 
          refusal_comment LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          certification_request 
        DROP 
          block_reason, 
        DROP 
          custom_block_reason, 
        DROP 
          block_comment, 
        DROP 
          refusal_reason, 
        DROP 
          custom_refusal_reason, 
        DROP 
          refusal_comment');
    }
}
