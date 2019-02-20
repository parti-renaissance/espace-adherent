<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170301233005 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE je_marche_reports CHANGE convinced convinced LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE almost_convinced almost_convinced LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE je_marche_reports CHANGE convinced convinced LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\', CHANGE almost_convinced almost_convinced LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\'');
    }
}
