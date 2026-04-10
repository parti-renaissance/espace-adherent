<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260410091313 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $batchSize = 100;

        do {
            $notifications = $this->connection->fetchAllAssociative(
                'SELECT n.id, n.tokens FROM notification n
                 WHERE n.tokens IS NOT NULL AND n.tokens != \'\'
                 AND NOT EXISTS (
                     SELECT 1 FROM notification_push_token npt
                     WHERE npt.notification_id = n.id
                 )
                 LIMIT ?',
                [$batchSize],
                ['integer']
            );

            if (empty($notifications)) {
                break;
            }

            $allIdentifiers = [];
            foreach ($notifications as $notification) {
                foreach (explode(',', $notification['tokens']) as $identifier) {
                    $identifier = trim($identifier);
                    if ('' !== $identifier) {
                        $allIdentifiers[$identifier] = true;
                    }
                }
            }

            $identifierList = array_keys($allIdentifiers);
            if (empty($identifierList)) {
                continue;
            }

            $placeholders = implode(',', array_fill(0, \count($identifierList), '?'));
            $idMap = [];

            $rows = $this->connection->fetchAllAssociative(
                "SELECT id, identifier FROM push_token WHERE identifier IN ($placeholders)",
                $identifierList
            );
            foreach ($rows as $row) {
                $idMap[$row['identifier']] = (int) $row['id'];
            }

            $values = [];
            foreach ($notifications as $notification) {
                foreach (explode(',', $notification['tokens']) as $identifier) {
                    $identifier = trim($identifier);
                    if ('' !== $identifier && isset($idMap[$identifier])) {
                        $values[] = \sprintf('(%d, %d)', $notification['id'], $idMap[$identifier]);
                    }
                }
            }

            if (!empty($values)) {
                $this->connection->executeStatement(
                    'INSERT IGNORE INTO notification_push_token (notification_id, push_token_id) VALUES '
                    .implode(',', $values)
                );
            }
        } while (\count($notifications) === $batchSize);
    }

    public function down(Schema $schema): void
    {
    }
}
