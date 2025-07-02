<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250702081141 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event ADD mailchimp_sync TINYINT(1) DEFAULT 0 NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event DROP mailchimp_sync
            SQL);
    }
}
