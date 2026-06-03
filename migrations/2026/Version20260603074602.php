<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260603074602 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  video
                ADD
                  transcode_without_audio TINYINT DEFAULT 0 NOT NULL,
                ADD
                  transcoding_started_at DATETIME DEFAULT NULL,
                ADD
                  transcoding_finished_at DATETIME DEFAULT NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_7CC7DA2C7B00651C ON video (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_7CC7DA2C7B00651C ON video');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  video
                DROP
                  transcode_without_audio,
                DROP
                  transcoding_started_at,
                DROP
                  transcoding_finished_at
            SQL);
    }
}
