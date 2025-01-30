<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231003095946 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters CHANGE interests interests JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE administrator_export_history CHANGE parameters parameters JSON NOT NULL');
        $this->addSql('ALTER TABLE chez_vous_cities CHANGE postal_codes postal_codes JSON NOT NULL');
        $this->addSql('ALTER TABLE chez_vous_measures CHANGE payload payload JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE donation_transactions CHANGE paybox_payload paybox_payload JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE facebook_profiles CHANGE age_range age_range JSON NOT NULL');
        $this->addSql('ALTER TABLE jecoute_riposte CHANGE open_graph open_graph JSON NOT NULL');
        $this->addSql('ALTER TABLE reports CHANGE reasons reasons JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters CHANGE interests interests JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE administrator_export_history CHANGE parameters parameters JSON NOT NULL');
        $this->addSql('ALTER TABLE chez_vous_cities CHANGE postal_codes postal_codes JSON NOT NULL');
        $this->addSql('ALTER TABLE chez_vous_measures CHANGE payload payload JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE donation_transactions CHANGE paybox_payload paybox_payload JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE facebook_profiles CHANGE age_range age_range JSON NOT NULL');
        $this->addSql('ALTER TABLE jecoute_riposte CHANGE open_graph open_graph JSON NOT NULL');
        $this->addSql('ALTER TABLE reports CHANGE reasons reasons JSON NOT NULL');
    }
}
