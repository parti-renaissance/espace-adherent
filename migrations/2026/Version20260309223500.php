<?php

declare(strict_types=1);

namespace Migrations;

use App\AppSession\DeviceInfoParser;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309223500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $parser = new DeviceInfoParser();
        $batchSize = 1000;

        do {
            $sessions = $this->connection->fetchAllAssociative(
                'SELECT id, user_agent FROM app_session WHERE user_agent IS NOT NULL AND device_info IS NULL LIMIT ?',
                [$batchSize],
                ['integer']
            );

            foreach ($sessions as $session) {
                $deviceInfo = $parser->parse($session['user_agent']);

                $this->connection->executeStatement(
                    'UPDATE app_session SET device_info = ? WHERE id = ?',
                    [$deviceInfo, $session['id']]
                );
            }
        } while (\count($sessions) === $batchSize);
    }

    public function down(Schema $schema): void
    {
    }
}
