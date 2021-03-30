<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201012163616 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          thematic_community 
        ADD 
          canonical_name VARCHAR(255) NOT NULL, 
        ADD 
          slug VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE thematic_community DROP canonical_name, DROP slug');
    }
}
