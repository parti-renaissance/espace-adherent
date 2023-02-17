<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230217112001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_formation CHANGE position position SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE formation_axes CHANGE position position SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE formation_modules CHANGE position position SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE formation_paths CHANGE position position SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE social_share_categories CHANGE position position SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE social_shares CHANGE position position SMALLINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_formation CHANGE position position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE formation_axes CHANGE position position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE formation_modules CHANGE position position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE formation_paths CHANGE position position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE
          social_share_categories
        CHANGE
          position position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE social_shares CHANGE position position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
    }
}
