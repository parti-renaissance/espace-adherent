<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250317152336 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          referral
        CHANGE
          identifier identifier VARCHAR(6) DEFAULT NULL,
        CHANGE
          mode mode VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          referral
        CHANGE
          identifier identifier VARCHAR(6) NOT NULL,
        CHANGE
          mode mode VARCHAR(255) NOT NULL');
    }
}
