<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230228093017 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative ADD last_contribution_date DATETIME DEFAULT NULL');
        $this->addSql('CREATE TABLE elected_representative_contribution (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          elected_representative_id INT UNSIGNED DEFAULT NULL,
          gocardless_customer_id VARCHAR(50) NOT NULL,
          type VARCHAR(20) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_EA351E15D17F50A6 (uuid),
          INDEX IDX_EA351E15D38DA5D3 (elected_representative_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        ADD
          CONSTRAINT FK_EA351E15D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE elected_representative_contribution');
        $this->addSql('ALTER TABLE elected_representative DROP last_contribution_date');
    }
}
