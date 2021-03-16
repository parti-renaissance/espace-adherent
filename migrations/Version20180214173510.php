<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180214173510 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE referent_tags (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(100) NOT NULL, UNIQUE INDEX referent_tag_name_unique (name), UNIQUE INDEX referent_tag_code_unique (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_managed_areas (id INT AUTO_INCREMENT NOT NULL, marker_latitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', marker_longitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_managed_areas_tags (referent_managed_area_id INT NOT NULL, referent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_8BE84DD56B99CC25 (referent_managed_area_id), INDEX IDX_8BE84DD59C262DB3 (referent_tag_id), PRIMARY KEY(referent_managed_area_id, referent_tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE adherent_referent_tag (adherent_id INT UNSIGNED NOT NULL, referent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_79E8AFFD25F06C53 (adherent_id), INDEX IDX_79E8AFFD9C262DB3 (referent_tag_id), PRIMARY KEY(adherent_id, referent_tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE referent_managed_areas_tags ADD CONSTRAINT FK_8BE84DD56B99CC25 FOREIGN KEY (referent_managed_area_id) REFERENCES referent_managed_areas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE referent_managed_areas_tags ADD CONSTRAINT FK_8BE84DD59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');

        $this->addSql('ALTER TABLE adherent_referent_tag ADD CONSTRAINT FK_79E8AFFD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_referent_tag ADD CONSTRAINT FK_79E8AFFD9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE projection_referent_managed_users ADD subscribed_tags LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD managed_area_id INT DEFAULT NULL, DROP managed_area_codes, DROP managed_area_marker_latitude, DROP managed_area_marker_longitude');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA3DC184E71 FOREIGN KEY (managed_area_id) REFERENCES referent_managed_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3DC184E71 ON adherents (managed_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_areas_tags DROP FOREIGN KEY FK_8BE84DD59C262DB3');
        $this->addSql('ALTER TABLE adherent_referent_tag DROP FOREIGN KEY FK_79E8AFFD9C262DB3');
        $this->addSql('ALTER TABLE referent_managed_areas_tags DROP FOREIGN KEY FK_8BE84DD56B99CC25');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3DC184E71');
        $this->addSql('DROP TABLE referent_tags');
        $this->addSql('DROP TABLE referent_managed_areas');
        $this->addSql('DROP TABLE referent_managed_areas_tags');
        $this->addSql('DROP TABLE adherent_referent_tag');
        $this->addSql('DROP INDEX UNIQ_562C7DA3DC184E71 ON adherents');
        $this->addSql('ALTER TABLE adherents ADD managed_area_codes LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\', ADD managed_area_marker_latitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', ADD managed_area_marker_longitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', DROP managed_area_id');
        $this->addSql('ALTER TABLE projection_referent_managed_users DROP subscribed_tags');
    }
}
