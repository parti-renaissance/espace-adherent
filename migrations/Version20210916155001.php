<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210916155001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_riposte
        ADD
          open_graph JSON NOT NULL COMMENT \'(DC2Type:json_array)\',
        CHANGE
          source_url source_url VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_riposte
        DROP
          open_graph,
        CHANGE
          source_url source_url VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
