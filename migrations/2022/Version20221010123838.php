<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221010123838 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          biography_executive_office_member
        ADD
          president TINYINT(1) DEFAULT \'0\' NOT NULL,
        ADD
          for_renaissance TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE biography_executive_office_member DROP president, DROP for_renaissance');
    }
}
