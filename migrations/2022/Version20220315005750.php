<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220315005750 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_news ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_riposte ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_survey ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pap_campaign ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE phoning_campaign ADD dynamic_link VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP dynamic_link');
        $this->addSql('ALTER TABLE jecoute_news DROP dynamic_link');
        $this->addSql('ALTER TABLE jecoute_riposte DROP dynamic_link');
        $this->addSql('ALTER TABLE jecoute_survey DROP dynamic_link');
        $this->addSql('ALTER TABLE pap_campaign DROP dynamic_link');
        $this->addSql('ALTER TABLE phoning_campaign DROP dynamic_link');
    }
}
