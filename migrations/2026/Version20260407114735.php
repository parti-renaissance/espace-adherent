<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407114735 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // 1. Create push_notification parents from existing notification chunks
        $this->addSql(<<<'SQL'
            INSERT INTO push_notification (uuid, notification_class, title, body, scope, data, status, total_tokens, total_success, total_failed, chunks_total, chunks_delivered, created_at, updated_at)
            SELECT
                UUID(),
                n.notification_class,
                n.title,
                MIN(n.body),
                n.scope,
                MIN(n.data),
                CASE WHEN SUM(CASE WHEN n.delivered_at IS NOT NULL THEN 1 ELSE 0 END) = COUNT(*) THEN 'delivered' ELSE 'partial' END,
                SUM(LENGTH(n.tokens) - LENGTH(REPLACE(n.tokens, ',', '')) + 1),
                0,
                0,
                COUNT(*),
                SUM(CASE WHEN n.delivered_at IS NOT NULL THEN 1 ELSE 0 END),
                MIN(n.created_at),
                MIN(n.created_at)
            FROM notification n
            WHERE n.push_notification_id IS NULL
            GROUP BY n.notification_class, n.title, n.body, n.scope
            SQL
        );

        // 2. Link existing notification chunks to their new push_notification parent
        $this->addSql(<<<'SQL'
            UPDATE notification n
            INNER JOIN push_notification pn
                ON pn.notification_class = n.notification_class
                AND pn.title = n.title
                AND (pn.scope = n.scope OR (pn.scope IS NULL AND n.scope IS NULL))
            SET n.push_notification_id = pn.id,
                n.tokens_sent = LENGTH(n.tokens) - LENGTH(REPLACE(n.tokens, ',', '')) + 1
            WHERE n.push_notification_id IS NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
    }
}
