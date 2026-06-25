<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625084217 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  timeline_feed
                ADD
                  visibility VARCHAR(50) DEFAULT NULL,
                ADD
                  committee_uuid VARCHAR(36) DEFAULT NULL,
                ADD
                  agora_uuid VARCHAR(36) DEFAULT NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_2248B1DE8CDE5729A166B6B7 ON timeline_feed (type, publication_date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_2248B1DE8CDE5729A166B6B7 ON timeline_feed');
        $this->addSql('ALTER TABLE timeline_feed DROP visibility, DROP committee_uuid, DROP agora_uuid');
    }
}
