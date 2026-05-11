<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511215540 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherents
                ADD
                  mailchimp_last_failed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                DROP
                  email_status_comment
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherents
                ADD
                  email_status_comment VARCHAR(255) DEFAULT NULL,
                DROP
                  mailchimp_last_failed_at
            SQL);
    }
}
