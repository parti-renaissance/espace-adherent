<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250211155729 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          birth_place VARCHAR(255) DEFAULT NULL,
        ADD
          accessibility VARCHAR(255) DEFAULT NULL,
        ADD
          transport_needs TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          volunteer TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        DROP
          birth_place,
        DROP
          accessibility,
        DROP
          transport_needs,
        DROP
          volunteer');
    }
}
