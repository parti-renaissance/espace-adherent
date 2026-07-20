<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260717093000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll ADD launch_notified TINYINT(1) DEFAULT 0 NOT NULL, ADD reminder_h8_notified TINYINT(1) DEFAULT 0 NOT NULL, ADD closing_h1_notified TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE poll SET launch_notified = 1, reminder_h8_notified = 1, closing_h1_notified = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll DROP launch_notified, DROP reminder_h8_notified, DROP closing_h1_notified');
    }
}
