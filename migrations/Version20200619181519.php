<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200619181519 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE elected_representative SET is_adherent = 1 WHERE is_adherent IS NULL');
        $this->addSql('ALTER TABLE 
          elected_representative CHANGE is_adherent is_adherent TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative CHANGE is_adherent is_adherent TINYINT(1) DEFAULT \'0\'');
    }
}
