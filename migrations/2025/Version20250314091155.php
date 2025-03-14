<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250314091155 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          unregistrations
        ADD
          type VARCHAR(255) DEFAULT NULL,
        ADD
          tags LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE unregistrations DROP type, DROP tags');
    }
}
