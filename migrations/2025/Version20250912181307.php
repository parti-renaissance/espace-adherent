<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250912181307 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          ticket_bracelet VARCHAR(255) DEFAULT NULL,
        CHANGE
          ticket_custom_detail_color ticket_bracelet_color VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          ticket_custom_detail_color VARCHAR(255) DEFAULT NULL,
        DROP
          ticket_bracelet,
        DROP
          ticket_bracelet_color');
    }
}
