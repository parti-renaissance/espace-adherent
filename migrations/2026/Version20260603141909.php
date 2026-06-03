<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260603141909 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_network_feed
                ADD
                  author_name VARCHAR(255) DEFAULT NULL,
                ADD
                  publication_failure VARCHAR(255) DEFAULT NULL,
                ADD
                  publication_failed_at DATETIME DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_network_feed
                DROP
                  author_name,
                DROP
                  publication_failure,
                DROP
                  publication_failed_at
            SQL);
    }
}
