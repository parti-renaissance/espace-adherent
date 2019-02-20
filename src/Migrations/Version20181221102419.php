<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181221102419 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP with_committee, CHANGE description description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD with_committee TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE description description LONGTEXT NOT NULL COLLATE utf8_unicode_ci');
    }
}
