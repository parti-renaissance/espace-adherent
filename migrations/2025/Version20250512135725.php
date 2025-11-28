<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250512135725 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                ADD
                  agora VARCHAR(255) DEFAULT NULL,
                ADD
                  agora_uuid CHAR(36) DEFAULT NULL COMMENT '(DC2Type:uuid)'
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE projection_managed_users DROP agora, DROP agora_uuid
            SQL);
    }
}
