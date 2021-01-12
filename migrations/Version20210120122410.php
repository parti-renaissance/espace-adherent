<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210120122410 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_mandate CHANGE provisional provisional TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE adherent_message_filters ADD include_committee_provisional_supervisors TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_managed_users ADD is_committee_provisional_supervisor TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_mandate CHANGE provisional provisional TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE adherent_message_filters DROP include_committee_provisional_supervisors');
        $this->addSql('ALTER TABLE projection_managed_users DROP is_committee_provisional_supervisor');
    }
}
