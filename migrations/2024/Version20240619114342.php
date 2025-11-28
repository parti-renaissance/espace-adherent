<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240619114342 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot ADD manual TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE procuration_v2_request_slot ADD manual TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot DROP manual');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP manual');
    }
}
