<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230724100823 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP print_privilege');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD print_privilege TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
