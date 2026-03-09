<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use UAParser\Parser;

final class Version20260309223500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $parser = Parser::create();

        $batchSize = 1000;
        $offset = 0;

        do {
            $sessions = $this->connection->fetchAllAssociative(
                'SELECT id, user_agent FROM app_session WHERE user_agent IS NOT NULL AND device_info IS NULL LIMIT ? OFFSET ?',
                [$batchSize, $offset],
                ['integer', 'integer']
            );

            foreach ($sessions as $session) {
                $result = $parser->parse($session['user_agent']);
                $family = $result->device->family;
                $model = $result->device->model;

                if ($model && $model !== $family && 'Other' !== $model) {
                    $deviceInfo = $model;
                } else {
                    $deviceInfo = 'Other' !== $family ? $family : $result->ua->family;
                }

                $this->connection->executeStatement(
                    'UPDATE app_session SET device_info = ? WHERE id = ?',
                    [$deviceInfo, $session['id']]
                );
            }

            $offset += $batchSize;
        } while (\count($sessions) === $batchSize);
    }

    public function down(Schema $schema): void
    {
    }
}
