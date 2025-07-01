<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250701102058 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  scope
                ADD
                  color_primary VARCHAR(255) DEFAULT NULL,
                ADD
                  color_soft VARCHAR(255) DEFAULT NULL,
                ADD
                  color_hover VARCHAR(255) DEFAULT NULL,
                ADD
                  color_active VARCHAR(255) DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE scope DROP color_primary, DROP color_soft, DROP color_hover, DROP color_active
            SQL);
    }
}
