<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200114210855 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations ADD donated_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE donations SET donated_at = created_at');
        $this->addSql('ALTER TABLE donations CHANGE donated_at donated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP donated_at');
    }
}
