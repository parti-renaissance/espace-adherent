<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107100714 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription ADD package_values JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE national_event_inscription_payment ADD package_values JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription DROP package_values');
        $this->addSql('ALTER TABLE national_event_inscription_payment DROP package_values');
    }
}
