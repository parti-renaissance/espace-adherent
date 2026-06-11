<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611125809 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign
                DROP
                  recovery_original_external_id,
                DROP
                  recovery_attempted_at,
                DROP
                  recovery_status
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign
                ADD
                  recovery_original_external_id VARCHAR(255) DEFAULT NULL,
                ADD
                  recovery_attempted_at DATETIME DEFAULT NULL,
                ADD
                  recovery_status VARCHAR(255) DEFAULT NULL
            SQL);
    }
}
