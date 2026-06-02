<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602103116 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE social_network_feed_video ADD video_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  social_network_feed_video
                ADD
                  CONSTRAINT FK_34F440C29C1004E FOREIGN KEY (video_id) REFERENCES video (id)
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34F440C29C1004E ON social_network_feed_video (video_id)');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  video
                ADD
                  source_uri VARCHAR(255) DEFAULT NULL,
                ADD
                  original_path VARCHAR(255) DEFAULT NULL,
                ADD
                  transcoding_job_name VARCHAR(255) DEFAULT NULL,
                ADD
                  failure_reason LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7CC7DA2CD5E8B460 ON video (source_uri)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE social_network_feed_video DROP FOREIGN KEY FK_34F440C29C1004E');
        $this->addSql('DROP INDEX UNIQ_34F440C29C1004E ON social_network_feed_video');
        $this->addSql('ALTER TABLE social_network_feed_video DROP video_id');
        $this->addSql('DROP INDEX UNIQ_7CC7DA2CD5E8B460 ON video');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  video
                DROP
                  source_uri,
                DROP
                  original_path,
                DROP
                  transcoding_job_name,
                DROP
                  failure_reason
            SQL);
    }
}
