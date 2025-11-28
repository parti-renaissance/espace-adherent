<?php

declare(strict_types=1);

namespace Migrations;

use App\Procuration\V2\ProxyStatusEnum;
use App\Procuration\V2\RequestStatusEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240321162127 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies ADD status VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE procuration_v2_proxies SET status = :status', ['status' => ProxyStatusEnum::PENDING->value]);
        $this->addSql('ALTER TABLE procuration_v2_proxies CHANGE status status VARCHAR(255) NOT NULL');

        $this->addSql('ALTER TABLE procuration_v2_requests ADD status VARCHAR(255) DEFAULT NULL');
        $this->addSql(<<<'SQL'
            UPDATE procuration_v2_requests
            SET status = CASE
                WHEN proxy_id IS NOT NULL THEN :status_completed
                ELSE :status_pending
            END
            SQL,
            [
                'status_completed' => RequestStatusEnum::COMPLETED->value,
                'status_pending' => RequestStatusEnum::PENDING->value,
            ]
        );
        $this->addSql('ALTER TABLE procuration_v2_requests CHANGE status status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP status');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP status');
    }
}
