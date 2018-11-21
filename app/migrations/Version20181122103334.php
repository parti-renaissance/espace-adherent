<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181122103334 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents CHANGE mandate mandates LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function preDown(Schema $schema): void
    {
        $this->connection->executeUpdate('UPDATE adherents SET mandates = SUBSTRING_INDEX(mandates, \',\', 1) WHERE mandates IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents CHANGE mandates mandate VARCHAR(22) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
