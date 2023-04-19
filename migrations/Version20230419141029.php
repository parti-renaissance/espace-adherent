<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230419141029 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_payment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          elected_representative_id INT UNSIGNED NOT NULL,
          ohme_id VARCHAR(50) NOT NULL,
          date DATETIME DEFAULT NULL,
          method VARCHAR(50) NOT NULL,
          status VARCHAR(50) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_4C351AA5D17F50A6 (uuid),
          INDEX IDX_4C351AA5D38DA5D3 (elected_representative_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          elected_representative_payment
        ADD
          CONSTRAINT FK_4C351AA5D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id)');
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        CHANGE
          elected_representative_id elected_representative_id INT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_payment DROP FOREIGN KEY FK_4C351AA5D38DA5D3');
        $this->addSql('DROP TABLE elected_representative_payment');
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        CHANGE
          elected_representative_id elected_representative_id INT UNSIGNED DEFAULT NULL');
    }
}
