<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230405125351 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          voting_platform_election
        CHANGE
          cancel_raison cancel_reason VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          voting_platform_election
        CHANGE
          cancel_reason cancel_raison VARCHAR(255) DEFAULT NULL');
    }
}
