<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180119110431 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD adherent TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('UPDATE adherents SET adherent = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP adherent');
    }
}
