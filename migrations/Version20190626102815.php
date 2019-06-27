<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190626102815 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrators ADD activated TINYINT(1) DEFAULT NULL');
        $this->addSql('UPDATE administrators SET activated = 1');

        $this->addSql('ALTER TABLE administrators CHANGE activated activated TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrators DROP activated');
    }
}
