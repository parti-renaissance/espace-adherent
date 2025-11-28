<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250411105857 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          referral
        ADD
          email_hash VARCHAR(255) DEFAULT NULL,
        CHANGE
          email_address email_address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referral DROP email_hash, CHANGE email_address email_address VARCHAR(255) NOT NULL');
    }
}
