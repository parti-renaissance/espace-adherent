<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230322144816 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          committees
        ADD
          sympathizers_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
        CHANGE
          members_count members_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          committees
        DROP
          sympathizers_count,
        CHANGE
          members_count members_count SMALLINT UNSIGNED NOT NULL');
    }
}
