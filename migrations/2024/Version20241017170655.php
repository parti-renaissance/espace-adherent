<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241017170655 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          designation
        CHANGE
          denomination denomination VARCHAR(255) DEFAULT \'élection\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          designation
        CHANGE
          denomination denomination VARCHAR(255) DEFAULT \'désignation\' NOT NULL');
    }
}
