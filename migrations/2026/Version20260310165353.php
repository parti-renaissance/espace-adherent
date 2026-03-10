<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310165353 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_managed_users CHANGE roles roles JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                CHANGE
                  roles roles LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
    }
}
