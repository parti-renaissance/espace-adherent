<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210319175244 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE committee_candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE committee_candidacy ADD candidacies_group_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A04454FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES committee_candidacies_group (id)');
        $this->addSql('CREATE INDEX IDX_9A04454FC1537C1 ON committee_candidacy (candidacies_group_id)');
        $this->addSql('ALTER TABLE territorial_council_candidacy ADD candidacies_group_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B6FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES candidacies_group (id)');
        $this->addSql('CREATE INDEX IDX_39885B6FC1537C1 ON territorial_council_candidacy (candidacies_group_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A04454FC1537C1');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B6FC1537C1');
        $this->addSql('DROP TABLE committee_candidacies_group');
        $this->addSql('DROP TABLE candidacies_group');
        $this->addSql('DROP INDEX IDX_9A04454FC1537C1 ON committee_candidacy');
        $this->addSql('ALTER TABLE committee_candidacy DROP candidacies_group_id');
        $this->addSql('DROP INDEX IDX_39885B6FC1537C1 ON territorial_council_candidacy');
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP candidacies_group_id');
    }
}
