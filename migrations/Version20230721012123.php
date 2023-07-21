<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230721012123 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          mandate_types LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
        ADD
          declared_mandates LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
        DROP
          mandate_type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          mandate_type VARCHAR(255) DEFAULT NULL,
        DROP
          mandate_types,
        DROP
          declared_mandates');
    }
}
