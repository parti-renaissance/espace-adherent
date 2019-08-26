<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190827001734 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          social_share_categories CHANGE position position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE formation_paths ADD position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE formation_modules ADD position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE formation_axes ADD position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE social_shares CHANGE position position SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation_axes DROP position');
        $this->addSql('ALTER TABLE formation_modules DROP position');
        $this->addSql('ALTER TABLE formation_paths DROP position');
        $this->addSql('ALTER TABLE social_share_categories CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE social_shares CHANGE position position INT NOT NULL');
    }
}
