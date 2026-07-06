<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705152340 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_statistics
                ADD
                  unique_opens_email_reliable INT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  unique_opens_email_effective INT UNSIGNED DEFAULT 0 NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_statistics
                DROP
                  unique_opens_email_reliable,
                DROP
                  unique_opens_email_effective
            SQL);
    }
}
