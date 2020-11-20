<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201008193819 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          political_committee_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          qualities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94C7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94C7A72 ON adherent_message_filters (political_committee_id)');

        $this->addSql("UPDATE adherent_messages SET `type` = 'referent_instances' WHERE `type` = 'referent_territorial_council'");
        $this->addSql("UPDATE adherent_message_filters SET `dtype` = 'referentinstancesfilter' WHERE `dtype` = 'referentterritorialcouncilfilter'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94C7A72');
        $this->addSql('DROP INDEX IDX_28CA9F94C7A72 ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP political_committee_id, DROP qualities');

        $this->addSql("UPDATE adherent_messages SET `type` = 'referent_territorial_council' WHERE `type` = 'referent_instances'");
        $this->addSql("UPDATE adherent_message_filters SET `dtype` = 'referentterritorialcouncilfilter' WHERE `dtype` = 'referentinstancesfilter'");
    }
}
