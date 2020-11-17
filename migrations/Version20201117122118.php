<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201117122118 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_mandate ADD reason VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE adherent_mandate SET reason = "election" WHERE finish_at IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_mandate DROP reason');
    }
}
