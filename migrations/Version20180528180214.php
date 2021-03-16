<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180528180214 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('RENAME TABLE transaction TO donation_transactions');
        $this->addSql('ALTER TABLE donation_transactions RENAME INDEX uniq_723705d15a4036c7 TO UNIQ_89D6D36B5A4036C7');
        $this->addSql('ALTER TABLE donation_transactions RENAME INDEX idx_723705d14dc1279c TO IDX_89D6D36B4DC1279C');
        $this->addSql('ALTER TABLE donation_transactions RENAME INDEX transaction_result_idx TO donation_transactions_result_idx');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donation_transactions RENAME INDEX donation_transactions_result_idx TO transaction_result_idx');
        $this->addSql('ALTER TABLE donation_transactions RENAME INDEX uniq_89d6d36b5a4036c7 TO UNIQ_723705D15A4036C7');
        $this->addSql('ALTER TABLE donation_transactions RENAME INDEX idx_89d6d36b4dc1279c TO IDX_723705D14DC1279C');
        $this->addSql('RENAME TABLE donation_transactions TO transaction');
    }
}
