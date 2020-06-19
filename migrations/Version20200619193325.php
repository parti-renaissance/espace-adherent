<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200619193325 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative DROP comment');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          elected_representative 
        ADD 
          comment VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
