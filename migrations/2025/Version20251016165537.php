<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251016165537 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_hit
                ADD
                  fingerprint VARCHAR(255) DEFAULT NULL,
                CHANGE
                  app_version app_version VARCHAR(255) DEFAULT NULL,
                CHANGE
                  raw raw JSON DEFAULT NULL
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_74A09586FC0B754A ON app_hit (fingerprint)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_74A09586FC0B754A ON app_hit');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  app_hit
                DROP
                  fingerprint,
                CHANGE
                  app_version app_version VARCHAR(255) NOT NULL,
                CHANGE
                  raw raw JSON NOT NULL
            SQL);
    }
}
