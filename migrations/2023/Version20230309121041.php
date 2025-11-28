<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230309121041 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        ADD
          gocardless_bank_account_id VARCHAR(50) DEFAULT NULL,
        ADD
          gocardless_mandate_id VARCHAR(50) DEFAULT NULL,
        ADD
          gocardless_subscription_id VARCHAR(50) DEFAULT NULL,
        ADD
          gocardless_bank_account_enabled TINYINT(1) DEFAULT \'1\' NOT NULL,
        ADD
          gocardless_mandate_status VARCHAR(20) DEFAULT NULL,
        ADD
          gocardless_subscription_status VARCHAR(20) DEFAULT NULL');

        $this->addSql('UPDATE elected_representative_contribution SET
          gocardless_bank_account_id = :unknown,
          gocardless_mandate_id = :unknown,
          gocardless_subscription_id = :unknown,
          gocardless_mandate_status = :unknown,
          gocardless_subscription_status = :unknown', [
            'unknown' => 'unknown',
        ]);

        $this->addSql('ALTER TABLE
          elected_representative_contribution
        CHANGE
          gocardless_bank_account_id gocardless_bank_account_id VARCHAR(50) NOT NULL,
        CHANGE
          gocardless_mandate_id gocardless_mandate_id VARCHAR(50) NOT NULL,
        CHANGE
          gocardless_subscription_id gocardless_subscription_id VARCHAR(50) NOT NULL,
        CHANGE
          gocardless_mandate_status gocardless_mandate_status VARCHAR(20) NOT NULL,
        CHANGE
          gocardless_subscription_status gocardless_subscription_status VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        DROP
          gocardless_bank_account_id,
        DROP
          gocardless_mandate_id,
        DROP
          gocardless_subscription_id,
        DROP
          gocardless_bank_account_enabled,
        DROP
          gocardless_mandate_status,
        DROP
          gocardless_subscription_status');
    }
}
