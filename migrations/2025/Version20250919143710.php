<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250919143710 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event
        ADD
          default_access VARCHAR(255) DEFAULT NULL,
        ADD
          default_bracelet VARCHAR(255) DEFAULT NULL,
        ADD
          default_bracelet_color VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event
        DROP
          default_access,
        DROP
          default_bracelet,
        DROP
          default_bracelet_color');
    }
}
