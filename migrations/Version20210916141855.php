<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210916141855 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_riposte
        ADD
          nb_views INT UNSIGNED DEFAULT 0 NOT NULL,
        ADD
          nd_detail_views INT UNSIGNED DEFAULT 0 NOT NULL,
        ADD
          nb_source_views INT UNSIGNED DEFAULT 0 NOT NULL,
        ADD
          nb_ripostes INT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_riposte
        DROP
          nb_views,
        DROP
          nd_detail_views,
        DROP
          nb_source_views,
        DROP
          nb_ripostes');
    }
}
