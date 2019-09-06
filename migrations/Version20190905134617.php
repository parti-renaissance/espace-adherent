<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190905134617 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE municipal_chief_areas ADD insee_code VARCHAR(255) NOT NULL');

        $this->addSql("UPDATE municipal_chief_areas SET insee_code = SUBSTRING_INDEX(codes, ',', 1)");

        $this->addSql('ALTER TABLE municipal_chief_areas DROP codes');
        $this->addSql('ALTER TABLE adherent_message_filters ADD insee_code VARCHAR(255) DEFAULT NULL, DROP cities');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          cities LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\', 
        DROP 
          insee_code');

        $this->addSql('ALTER TABLE 
          municipal_chief_areas 
        ADD 
          codes LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\'');

        $this->addSql('UPDATE municipal_chief_areas SET codes = insee_code');
        $this->addSql('ALTER TABLE municipal_chief_areas DROP insee_code');
    }
}
