<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201126133559 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_region CHANGE logo logo VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_region CHANGE logo logo VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
