<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260109132632 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  text_help_birthdate_field LONGTEXT DEFAULT NULL,
                ADD
                  text_help_phone_field LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                ADD
                  emergency_contact_name VARCHAR(255) DEFAULT NULL,
                ADD
                  emergency_contact_phone VARCHAR(35) DEFAULT NULL COMMENT '(DC2Type:phone_number)'
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event DROP text_help_birthdate_field, DROP text_help_phone_field');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                DROP
                  emergency_contact_name,
                DROP
                  emergency_contact_phone
            SQL);
    }
}
