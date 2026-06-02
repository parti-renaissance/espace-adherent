<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602224542 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_network_feed
                ADD
                  published TINYINT DEFAULT 0 NOT NULL,
                ADD
                  published_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('DROP INDEX UNIQ_7CC7DA2CD5E8B460 ON video');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE social_network_feed DROP published, DROP published_at');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7CC7DA2CD5E8B460 ON video (source_uri)');
    }
}
