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
        $this->addSql('ALTER TABLE elected_representative_label CHANGE begin_year begin_year INT DEFAULT NULL');
        $this->addSql('ALTER TABLE elected_representative DROP comment');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          elected_representative 
        ADD 
          comment VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSQL('ALTER TABLE elected_representative_label SET begin_year = YEAR(CURDATE()) WHERE begin_year IS NULL');
        $this->addSql('ALTER TABLE elected_representative_label CHANGE begin_year begin_year INT NOT NULL');
        $this->addSql('ALTER TABLE elected_representative CHANGE is_adherent is_adherent TINYINT(1) DEFAULT \'0\'');
    }
}
