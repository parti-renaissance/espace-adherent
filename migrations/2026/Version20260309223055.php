<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309223055 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE app_session ADD device_info VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                CHANGE
                  subscription_types subscription_types LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE app_session DROP device_info
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                CHANGE
                  subscription_types subscription_types JSON DEFAULT NULL
            SQL);
    }
}
