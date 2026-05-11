<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511172041 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_5387574ABF972FA21FA05323 ON events (address_latitude, address_longitude)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_5387574ABF972FA21FA05323 ON `events`');
    }
}
