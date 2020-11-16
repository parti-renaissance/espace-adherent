<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200731165543 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_commitment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED NOT NULL, 
          commitment_actions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          debate_and_propose_ideas_actions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          act_for_territory_actions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          progressivism_actions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          skills LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          availability VARCHAR(255) DEFAULT NULL, 
          UNIQUE INDEX UNIQ_D239EF6F25F06C53 (adherent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE 
          adherent_commitment 
        ADD 
          CONSTRAINT FK_D239EF6F25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_commitment');
    }
}
