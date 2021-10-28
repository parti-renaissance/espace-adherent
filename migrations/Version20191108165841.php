<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191108165841 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE programmatic_foundation_measure DROP city');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          programmatic_foundation_measure 
        ADD 
          city VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
