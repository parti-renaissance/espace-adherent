<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230329012543 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A04454FC1537C1');
        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT FK_9A04454FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES committee_candidacies_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE designation ADD is_canceled TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE
          voting_platform_election
        ADD
          canceled_at DATETIME DEFAULT NULL,
        ADD
          cancel_raison VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A04454FC1537C1');
        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT FK_9A04454FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES committee_candidacies_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE designation DROP is_canceled');
        $this->addSql('ALTER TABLE voting_platform_election DROP canceled_at, DROP cancel_raison');
    }
}
