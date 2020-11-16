<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201022105759 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE thematic_community_membership ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE thematic_community_contact ADD position VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          thematic_community_membership CHANGE motivations motivations LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE thematic_community_membership DROP status');
        $this->addSql('ALTER TABLE thematic_community_contact DROP position');
        $this->addSql('ALTER TABLE
          thematic_community_membership CHANGE motivations motivations LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\'');
    }
}
