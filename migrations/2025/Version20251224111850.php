<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251224111850 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  alert_logo_image_id INT UNSIGNED DEFAULT NULL,
                ADD
                  connection_enabled TINYINT(1) DEFAULT 1 NOT NULL,
                ADD
                  discount_label VARCHAR(255) DEFAULT NULL,
                ADD
                  discount_help LONGTEXT DEFAULT NULL,
                CHANGE
                  transport_configuration package_config JSON DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  CONSTRAINT FK_AD037664DD86B734 FOREIGN KEY (alert_logo_image_id) REFERENCES uploadable_file (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD037664DD86B734 ON national_event (alert_logo_image_id)');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                ADD
                  package_plan VARCHAR(255) DEFAULT NULL,
                ADD
                  package_city VARCHAR(255) DEFAULT NULL,
                ADD
                  package_departure_time VARCHAR(255) DEFAULT NULL,
                ADD
                  package_donation VARCHAR(255) DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event DROP FOREIGN KEY FK_AD037664DD86B734');
        $this->addSql('DROP INDEX UNIQ_AD037664DD86B734 ON national_event');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                DROP
                  alert_logo_image_id,
                DROP
                  connection_enabled,
                DROP
                  discount_label,
                DROP
                  discount_help,
                CHANGE
                  package_config transport_configuration JSON DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                DROP
                  package_plan,
                DROP
                  package_city,
                DROP
                  package_departure_time,
                DROP
                  package_donation
            SQL);
    }
}
