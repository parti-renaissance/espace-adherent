<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200619185543 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_label CHANGE begin_year begin_year INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSQL('ALTER TABLE elected_representative_label SET begin_year = YEAR(CURDATE()) WHERE begin_year IS NULL');
        $this->addSql('ALTER TABLE elected_representative_label CHANGE begin_year begin_year INT NOT NULL');
    }
}
