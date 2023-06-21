<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230621132631 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          campus_registration
        ADD
          event_maker_order_uid VARCHAR(50) NOT NULL,
        ADD
          registered_at DATETIME NOT NULL,
        DROP
          price,
        CHANGE
          event_maker_id event_maker_uid VARCHAR(50) NOT NULL,
        CHANGE
          secret status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          campus_registration
        ADD
          event_maker_id VARCHAR(50) NOT NULL,
        ADD
          price DOUBLE PRECISION NOT NULL,
        DROP
          event_maker_uid,
        DROP
          event_maker_order_uid,
        DROP
          registered_at,
        CHANGE
          status secret VARCHAR(255) NOT NULL');
    }
}
