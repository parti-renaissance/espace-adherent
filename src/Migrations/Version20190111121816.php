<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190111121816 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX idea_workshop_status_idx ON ideas_workshop_idea');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD finalized_at DATETIME DEFAULT NULL, ADD enabled TINYINT(1) DEFAULT \'1\' NOT NULL, DROP status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD status VARCHAR(11) DEFAULT \'DRAFT\' NOT NULL COLLATE utf8_unicode_ci, DROP finalized_at, DROP enabled');
        $this->addSql('CREATE INDEX idea_workshop_status_idx ON ideas_workshop_idea (status)');
    }
}
