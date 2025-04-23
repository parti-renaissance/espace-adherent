<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250423143331 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          unregistrations
        CHANGE
          adherent_uuid email_hash CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          unregistrations
        CHANGE
          email_hash adherent_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
