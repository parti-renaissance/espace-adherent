<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230324113203 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          projection_managed_users
        ADD
          committee VARCHAR(255) DEFAULT NULL,
        ADD
          committee_uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_managed_users DROP committee, DROP committee_uuid');
    }
}
