<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250627081559 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                ADD
                  duplicate_inscription_for_status_id INT UNSIGNED DEFAULT NULL,
                ADD
                  confirmation_sent_at DATETIME DEFAULT NULL,
                CHANGE
                  payment_status payment_status VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                ADD
                  CONSTRAINT FK_C3325557CC613791 FOREIGN KEY (
                    duplicate_inscription_for_status_id
                  ) REFERENCES national_event_inscription (id)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_C3325557CC613791 ON national_event_inscription (
                  duplicate_inscription_for_status_id
                )
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment ADD replacement_id INT UNSIGNED DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                ADD
                  CONSTRAINT FK_D0696D129D25CF90 FOREIGN KEY (replacement_id) REFERENCES national_event_inscription_payment (id)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_D0696D129D25CF90 ON national_event_inscription_payment (replacement_id)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription DROP FOREIGN KEY FK_C3325557CC613791
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_C3325557CC613791 ON national_event_inscription
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                DROP
                  duplicate_inscription_for_status_id,
                DROP
                  confirmation_sent_at,
                CHANGE
                  payment_status payment_status VARCHAR(255) DEFAULT 'pending' NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment DROP FOREIGN KEY FK_D0696D129D25CF90
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_D0696D129D25CF90 ON national_event_inscription_payment
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment DROP replacement_id
            SQL);
    }
}
