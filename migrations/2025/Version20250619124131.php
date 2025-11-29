<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250619124131 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE national_event_inscription_reminder (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  inscription_id INT UNSIGNED DEFAULT NULL,
                  type VARCHAR(255) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  INDEX IDX_CD82035C5DAC5993 (inscription_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_reminder
                ADD
                  CONSTRAINT FK_CD82035C5DAC5993 FOREIGN KEY (inscription_id) REFERENCES national_event_inscription (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment DROP FOREIGN KEY FK_D0696D125DAC5993
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                ADD
                  CONSTRAINT FK_D0696D125DAC5993 FOREIGN KEY (inscription_id) REFERENCES national_event_inscription (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment_status DROP FOREIGN KEY FK_746EBF595DAC5993
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment_status DROP FOREIGN KEY FK_746EBF594C3A3BB
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX UNIQ_746EBF59D17F50A6 ON national_event_inscription_payment_status
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_746EBF595DAC5993 ON national_event_inscription_payment_status
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment_status DROP inscription_id, DROP uuid
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment_status
                ADD
                  CONSTRAINT FK_746EBF594C3A3BB FOREIGN KEY (payment_id) REFERENCES national_event_inscription_payment (id) ON DELETE CASCADE
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_reminder DROP FOREIGN KEY FK_CD82035C5DAC5993
            SQL);
        $this->addSql(<<<'SQL'
                DROP TABLE national_event_inscription_reminder
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment DROP FOREIGN KEY FK_D0696D125DAC5993
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                ADD
                  CONSTRAINT FK_D0696D125DAC5993 FOREIGN KEY (inscription_id) REFERENCES national_event_inscription (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment_status DROP FOREIGN KEY FK_746EBF594C3A3BB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment_status
                ADD
                  inscription_id INT UNSIGNED DEFAULT NULL,
                ADD
                  uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)'
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment_status
                ADD
                  CONSTRAINT FK_746EBF595DAC5993 FOREIGN KEY (inscription_id) REFERENCES national_event_inscription (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment_status
                ADD
                  CONSTRAINT FK_746EBF594C3A3BB FOREIGN KEY (payment_id) REFERENCES national_event_inscription_payment (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
        $this->addSql(<<<'SQL'
                CREATE UNIQUE INDEX UNIQ_746EBF59D17F50A6 ON national_event_inscription_payment_status (uuid)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_746EBF595DAC5993 ON national_event_inscription_payment_status (inscription_id)
            SQL);
    }
}
