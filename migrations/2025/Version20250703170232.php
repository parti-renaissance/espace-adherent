<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250703170232 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription
                    ADD accommodation VARCHAR(255) DEFAULT NULL,
                    CHANGE transport_costs amount SMALLINT UNSIGNED DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment ADD accommodation VARCHAR(255) DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription DROP accommodation
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription_payment DROP accommodation
            SQL);
    }
}
