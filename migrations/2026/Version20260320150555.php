<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260320150555 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                CHANGE
                  mandate_type elect_mandate VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                CHANGE
                  mandates elect_mandates LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                CHANGE
                  elect_mandate mandate_type VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                CHANGE
                  elect_mandates mandates LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
    }
}
