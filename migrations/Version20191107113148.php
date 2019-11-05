<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191107113148 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          programmatic_foundation_sub_approach 
        ADD 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_project 
        ADD 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE programmatic_foundation_measure DROP FOREIGN KEY FK_213A5F1E15140614');
        $this->addSql('DROP INDEX IDX_213A5F1E15140614 ON programmatic_foundation_measure');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure 
        ADD 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          CHANGE approach_id sub_approach_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure 
        ADD 
          CONSTRAINT FK_213A5F1EF0ED738A FOREIGN KEY (sub_approach_id) REFERENCES programmatic_foundation_sub_approach (id)');
        $this->addSql('CREATE INDEX IDX_213A5F1EF0ED738A ON programmatic_foundation_measure (sub_approach_id)');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_approach 
        ADD 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE programmatic_foundation_approach DROP uuid');
        $this->addSql('ALTER TABLE programmatic_foundation_measure DROP FOREIGN KEY FK_213A5F1EF0ED738A');
        $this->addSql('DROP INDEX IDX_213A5F1EF0ED738A ON programmatic_foundation_measure');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure 
        DROP 
          uuid, 
          CHANGE sub_approach_id approach_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure 
        ADD 
          CONSTRAINT FK_213A5F1E15140614 FOREIGN KEY (approach_id) REFERENCES programmatic_foundation_sub_approach (id)');
        $this->addSql('CREATE INDEX IDX_213A5F1E15140614 ON programmatic_foundation_measure (approach_id)');
        $this->addSql('ALTER TABLE programmatic_foundation_project DROP uuid');
        $this->addSql('ALTER TABLE programmatic_foundation_sub_approach DROP uuid');
    }
}
