<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250625085743 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                ADD
                  payment_status VARCHAR(255) DEFAULT 'pending' NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                ADD
                  transport VARCHAR(255) DEFAULT NULL,
                ADD
                  with_discount TINYINT(1) DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription DROP payment_status
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment DROP transport, DROP with_discount
            SQL);
    }
}
