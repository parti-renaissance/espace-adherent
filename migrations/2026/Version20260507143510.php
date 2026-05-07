<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260507143510 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign
                CHANGE
                  cancellation_requested pending_send TINYINT(1) DEFAULT 0 NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_static_segment
                ADD
                  build_started_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign
                CHANGE
                  pending_send cancellation_requested TINYINT(1) DEFAULT 0 NOT NULL
            SQL);
        $this->addSql('ALTER TABLE mailchimp_static_segment DROP build_started_at');
    }
}
