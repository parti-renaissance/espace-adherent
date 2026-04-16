<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416101623 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative DROP FOREIGN KEY FK_BF51F0FD14E51F8D');
        $this->addSql('ALTER TABLE elected_representative_contribution DROP FOREIGN KEY FK_6F9C7915D38DA5D3');
        $this->addSql('DROP TABLE elected_representative_contribution');
        $this->addSql('DROP INDEX UNIQ_BF51F0FD14E51F8D ON elected_representative');
        $this->addSql('ALTER TABLE elected_representative DROP last_contribution_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE elected_representative_contribution (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  elected_representative_id INT UNSIGNED NOT NULL,
                  gocardless_customer_id VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT '(DC2Type:uuid)',
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  gocardless_bank_account_id VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  gocardless_mandate_id VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  gocardless_subscription_id VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  gocardless_bank_account_enabled TINYINT(1) DEFAULT 1 NOT NULL,
                  gocardless_mandate_status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  gocardless_subscription_status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  start_date DATETIME DEFAULT NULL,
                  end_date DATETIME DEFAULT NULL,
                  UNIQUE INDEX UNIQ_6F9C7915D17F50A6 (uuid),
                  INDEX IDX_6F9C7915D38DA5D3 (elected_representative_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_contribution
                ADD
                  CONSTRAINT FK_6F9C7915D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql('ALTER TABLE elected_representative ADD last_contribution_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative
                ADD
                  CONSTRAINT FK_BF51F0FD14E51F8D FOREIGN KEY (last_contribution_id) REFERENCES elected_representative_contribution (id) ON
                UPDATE
                  NO ACTION ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF51F0FD14E51F8D ON elected_representative (last_contribution_id)');
    }
}
