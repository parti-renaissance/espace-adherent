<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200917163301 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_thematic_community (
          adherent_id INT UNSIGNED NOT NULL, 
          thematic_community_id INT UNSIGNED NOT NULL, 
          INDEX IDX_DAB0B4EC25F06C53 (adherent_id), 
          INDEX IDX_DAB0B4EC1BE5825E (thematic_community_id), 
          PRIMARY KEY(
            adherent_id, thematic_community_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          adherent_thematic_community 
        ADD 
          CONSTRAINT FK_DAB0B4EC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          adherent_thematic_community 
        ADD 
          CONSTRAINT FK_DAB0B4EC1BE5825E FOREIGN KEY (thematic_community_id) REFERENCES thematic_community (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_thematic_community');
    }
}
