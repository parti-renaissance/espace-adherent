<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716131913 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                ADD
                  hosted_checkout_id VARCHAR(255) DEFAULT NULL,
                ADD
                  worldline_payment_id VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment_status
                ADD
                  worldline_payment_id VARCHAR(255) DEFAULT NULL,
                ADD
                  status_code VARCHAR(255) DEFAULT NULL,
                ADD
                  status VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                CREATE UNIQUE INDEX UNIQ_746EBF594C3A3BBA3DAD8F74F139D0C ON national_event_inscription_payment_status (
                  payment_id, worldline_payment_id,
                  status_code
                )
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                DROP
                  hosted_checkout_id,
                DROP
                  worldline_payment_id
            SQL);
        $this->addSql('DROP INDEX UNIQ_746EBF594C3A3BBA3DAD8F74F139D0C ON national_event_inscription_payment_status');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment_status
                DROP
                  worldline_payment_id,
                DROP
                  status_code,
                DROP
                  status
            SQL);
    }
}
