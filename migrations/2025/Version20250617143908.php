<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250617143908 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE national_event_inscription_payment_status (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  inscription_id INT UNSIGNED DEFAULT NULL,
                  payload JSON NOT NULL,
                  uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  UNIQUE INDEX UNIQ_746EBF59D17F50A6 (uuid),
                  INDEX IDX_746EBF595DAC5993 (inscription_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment_status
                ADD
                  CONSTRAINT FK_746EBF595DAC5993 FOREIGN KEY (inscription_id) REFERENCES national_event_inscription (id)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment_status DROP FOREIGN KEY FK_746EBF595DAC5993
            SQL);
        $this->addSql(<<<'SQL'
                DROP TABLE national_event_inscription_payment_status
            SQL);
    }
}
