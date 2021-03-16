<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190116162854 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations ADD nationality VARCHAR(2) DEFAULT \'FR\' NOT NULL');
        $this->addSql('ALTER TABLE donations CHANGE nationality nationality VARCHAR(2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP nationality');
    }
}
