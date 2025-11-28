<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250911151519 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription_payment ADD expired_checked_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE national_event_inscription_payment_status DROP raw_body');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription_payment DROP expired_checked_at');
        $this->addSql('ALTER TABLE national_event_inscription_payment_status ADD raw_body LONGTEXT DEFAULT NULL');
    }
}
