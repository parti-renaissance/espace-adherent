<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180926101908 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE biography_executive_office_member CHANGE content content LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE biography_executive_office_member CHANGE content content VARCHAR(800) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
