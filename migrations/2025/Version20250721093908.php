<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250721093908 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                CHANGE
                  label label VARCHAR(255) DEFAULT NULL,
                CHANGE
                  subject subject VARCHAR(255) DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                CHANGE
                  label label VARCHAR(255) NOT NULL,
                CHANGE
                  subject subject VARCHAR(255) NOT NULL
            SQL);
    }
}
