<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240603164507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          vox_action
        ADD
          notified_at_first_notification DATETIME DEFAULT NULL,
        ADD
          notified_at_second_notification DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          vox_action
        DROP
          notified_at_first_notification,
        DROP
          notified_at_second_notification');
    }
}
