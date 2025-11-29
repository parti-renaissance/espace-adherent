<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250220105051 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          referral
        ADD
          type VARCHAR(255) NOT NULL,
        ADD
          mode VARCHAR(255) NOT NULL,
        ADD
          status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referral DROP type, DROP mode, DROP status');
    }
}
