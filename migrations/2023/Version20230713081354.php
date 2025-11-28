<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230713081354 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contribution (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          start_date DATETIME DEFAULT NULL,
          end_date DATETIME DEFAULT NULL,
          gocardless_customer_id VARCHAR(50) NOT NULL,
          gocardless_bank_account_id VARCHAR(50) NOT NULL,
          gocardless_bank_account_enabled TINYINT(1) DEFAULT 1 NOT NULL,
          gocardless_mandate_id VARCHAR(50) NOT NULL,
          gocardless_mandate_status VARCHAR(20) NOT NULL,
          gocardless_subscription_id VARCHAR(50) NOT NULL,
          gocardless_subscription_status VARCHAR(20) NOT NULL,
          type VARCHAR(20) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_EA351E15D17F50A6 (uuid),
          INDEX IDX_EA351E1525F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contribution_payment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          ohme_id VARCHAR(50) NOT NULL,
          date DATETIME DEFAULT NULL,
          method VARCHAR(50) NOT NULL,
          status VARCHAR(50) DEFAULT NULL,
          amount INT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_2C09F4CCD17F50A6 (uuid),
          INDEX IDX_2C09F4CC25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contribution_revenue_declaration (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          amount INT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_84181073D17F50A6 (uuid),
          INDEX IDX_8418107325F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          contribution
        ADD
          CONSTRAINT FK_EA351E1525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          contribution_payment
        ADD
          CONSTRAINT FK_2C09F4CC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          contribution_revenue_declaration
        ADD
          CONSTRAINT FK_8418107325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          last_contribution_id INT UNSIGNED DEFAULT NULL,
        ADD
          contribution_status VARCHAR(255) DEFAULT NULL,
        ADD
          contributed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA314E51F8D FOREIGN KEY (last_contribution_id) REFERENCES contribution (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA314E51F8D ON adherents (last_contribution_id)');
        $this->addSql('ALTER TABLE elected_representative_contribution DROP FOREIGN KEY FK_6F9C7915D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        ADD
          CONSTRAINT FK_6F9C7915D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE elected_representative_payment DROP FOREIGN KEY FK_4C351AA5D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative_payment
        ADD
          CONSTRAINT FK_4C351AA5D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA314E51F8D');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E1525F06C53');
        $this->addSql('ALTER TABLE contribution_payment DROP FOREIGN KEY FK_2C09F4CC25F06C53');
        $this->addSql('ALTER TABLE contribution_revenue_declaration DROP FOREIGN KEY FK_8418107325F06C53');
        $this->addSql('DROP TABLE contribution');
        $this->addSql('DROP TABLE contribution_payment');
        $this->addSql('DROP TABLE contribution_revenue_declaration');
        $this->addSql('DROP INDEX UNIQ_562C7DA314E51F8D ON adherents');
        $this->addSql('ALTER TABLE adherents DROP last_contribution_id, DROP contribution_status, DROP contributed_at');
        $this->addSql('ALTER TABLE elected_representative_contribution DROP FOREIGN KEY FK_6F9C7915D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        ADD
          CONSTRAINT FK_6F9C7915D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE elected_representative_payment DROP FOREIGN KEY FK_4C351AA5D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative_payment
        ADD
          CONSTRAINT FK_4C351AA5D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
