<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250102173536 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP adherent');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD adherent TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
