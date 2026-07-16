<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716163533 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription_payment ADD donation_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription_payment
                ADD
                  CONSTRAINT FK_D0696D124DC1279C FOREIGN KEY (donation_id) REFERENCES donations (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D0696D124DC1279C ON national_event_inscription_payment (donation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription_payment DROP FOREIGN KEY FK_D0696D124DC1279C');
        $this->addSql('DROP INDEX UNIQ_D0696D124DC1279C ON national_event_inscription_payment');
        $this->addSql('ALTER TABLE national_event_inscription_payment DROP donation_id');
    }
}
