<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240618143502 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies ADD status_detail VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_v2_requests ADD status_detail VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP status_detail');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP status_detail');
    }
}
