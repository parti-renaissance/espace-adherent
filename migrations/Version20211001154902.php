<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211001154902 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE phoning_campaign_history CHANGE adherent_id adherent_id INT UNSIGNED DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE phoning_campaign_history CHANGE adherent_id adherent_id INT UNSIGNED NOT NULL');
    }
}
