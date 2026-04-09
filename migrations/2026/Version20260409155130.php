<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409155130 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  show_birth_place TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  label_birth_place VARCHAR(255) DEFAULT NULL,
                ADD
                  show_transport_needs TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  label_transport_needs VARCHAR(255) DEFAULT NULL,
                ADD
                  show_with_children TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  label_with_children VARCHAR(255) DEFAULT NULL,
                ADD
                  show_volunteer TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  label_volunteer VARCHAR(255) DEFAULT NULL,
                ADD
                  show_is_jam TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  label_is_jam VARCHAR(255) DEFAULT NULL,
                ADD
                  show_allow_notifications TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  label_allow_notifications VARCHAR(255) DEFAULT NULL,
                ADD
                  show_emergency_contact TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  label_emergency_contact VARCHAR(255) DEFAULT NULL,
                ADD
                  show_roommate_identifier TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  label_roommate_identifier VARCHAR(255) DEFAULT NULL,
                ADD
                  show_accessibility TINYINT(1) DEFAULT 1 NOT NULL,
                ADD
                  label_accessibility VARCHAR(255) DEFAULT NULL,
                ADD
                  phone_required TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  required_birth_place TINYINT(1) DEFAULT 1 NOT NULL,
                ADD
                  required_emergency_contact TINYINT(1) DEFAULT 1 NOT NULL,
                ADD
                  required_accessibility TINYINT(1) DEFAULT 0 NOT NULL
            SQL);

        // Data migration: populate booleans based on existing event type
        // DEFAULT type
        $this->addSql("UPDATE national_event SET show_birth_place = 1, show_transport_needs = 1, show_with_children = 1, show_volunteer = 1, show_is_jam = 1, show_allow_notifications = 1 WHERE type = 'default'");
        // CAMPUS type
        $this->addSql("UPDATE national_event SET show_birth_place = 1, show_volunteer = 1, show_is_jam = 1, show_allow_notifications = 1, show_roommate_identifier = 1 WHERE type = 'campus'");
        // NRP type
        $this->addSql("UPDATE national_event SET show_birth_place = 1, show_allow_notifications = 1 WHERE type = 'nrp'");
        // JEM type
        $this->addSql("UPDATE national_event SET show_emergency_contact = 1, phone_required = 1 WHERE type = 'jem'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                DROP
                  show_birth_place,
                DROP
                  label_birth_place,
                DROP
                  show_transport_needs,
                DROP
                  label_transport_needs,
                DROP
                  show_with_children,
                DROP
                  label_with_children,
                DROP
                  show_volunteer,
                DROP
                  label_volunteer,
                DROP
                  show_is_jam,
                DROP
                  label_is_jam,
                DROP
                  show_allow_notifications,
                DROP
                  label_allow_notifications,
                DROP
                  show_emergency_contact,
                DROP
                  label_emergency_contact,
                DROP
                  show_roommate_identifier,
                DROP
                  label_roommate_identifier,
                DROP
                  show_accessibility,
                DROP
                  label_accessibility,
                DROP
                  phone_required,
                DROP
                  required_birth_place,
                DROP
                  required_emergency_contact,
                DROP
                  required_accessibility
            SQL);
    }
}
