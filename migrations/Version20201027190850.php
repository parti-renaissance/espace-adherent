<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201027190850 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents CHANGE position position VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE thematic_community_contact CHANGE position position VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherents CHANGE position position VARCHAR(20) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE 
          thematic_community_contact CHANGE position position VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
