<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619172723 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pronostic
                ADD
                  creation_notified TINYINT DEFAULT 0 NOT NULL,
                ADD
                  j_minus1_notified TINYINT DEFAULT 0 NOT NULL,
                ADD
                  h_minus1_notified TINYINT DEFAULT 0 NOT NULL,
                ADD
                  result_notified TINYINT DEFAULT 0 NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pronostic
                DROP
                  creation_notified,
                DROP
                  j_minus1_notified,
                DROP
                  h_minus1_notified,
                DROP
                  result_notified
            SQL);
    }
}
