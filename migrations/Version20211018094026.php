<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211018094026 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_riposte
        CHANGE
          nd_detail_views nb_detail_views INT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_riposte
        CHANGE
          nb_detail_views nd_detail_views INT UNSIGNED DEFAULT 0 NOT NULL');
    }
}
