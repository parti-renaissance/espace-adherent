<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407104257 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  push_token
                ADD
                  unsubscribed_reason VARCHAR(255) DEFAULT NULL,
                ADD
                  last_notification_at DATETIME DEFAULT NULL,
                ADD
                  last_notification_success TINYINT(1) DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  push_token
                DROP
                  unsubscribed_reason,
                DROP
                  last_notification_at,
                DROP
                  last_notification_success
            SQL);
    }
}
