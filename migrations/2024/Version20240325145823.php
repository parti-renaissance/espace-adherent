<?php

declare(strict_types=1);

namespace Migrations;

use App\Procuration\V2\InitialRequestTypeEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240325145823 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_initial_requests ADD type VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE procuration_v2_initial_requests SET type = :type', ['type' => InitialRequestTypeEnum::REQUEST->value]);
        $this->addSql('ALTER TABLE procuration_v2_initial_requests CHANGE type type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_initial_requests DROP type');
    }
}
