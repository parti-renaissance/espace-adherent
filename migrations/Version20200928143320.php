<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200928143320 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_mandate ADD is_additionally_elected TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE political_committee_membership ADD is_additional TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_mandate DROP is_additionally_elected');
        $this->addSql('ALTER TABLE political_committee_membership DROP is_additional');
    }
}
