<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220314141817 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE algolia_je_mengage_timeline_feed DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE
          algolia_je_mengage_timeline_feed
        CHANGE
          id object_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE algolia_je_mengage_timeline_feed ADD PRIMARY KEY (object_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE algolia_je_mengage_timeline_feed DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE
          algolia_je_mengage_timeline_feed
        CHANGE
          object_id id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE algolia_je_mengage_timeline_feed ADD PRIMARY KEY (id)');
    }
}
