<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190419145116 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territory_department (id BIGINT AUTO_INCREMENT NOT NULL, region_id BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, INDEX IDX_B30148E898260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territory_region (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territory_city (id BIGINT AUTO_INCREMENT NOT NULL, department_id BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, postal_code VARCHAR(255) NOT NULL, INDEX IDX_376BCFC0AE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE managed_area_senator (id INT UNSIGNED AUTO_INCREMENT NOT NULL, department_id BIGINT DEFAULT NULL, since DATE DEFAULT NULL, INDEX IDX_E41F872CAE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE managed_area_european_deputy (id INT UNSIGNED AUTO_INCREMENT NOT NULL, since DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE managed_area_referent (id INT UNSIGNED AUTO_INCREMENT NOT NULL, since DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_managed_area_referent_tag (referent_managed_area_id INT UNSIGNED NOT NULL, referent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_BD8B6D7A6B99CC25 (referent_managed_area_id), INDEX IDX_BD8B6D7A9C262DB3 (referent_tag_id), PRIMARY KEY(referent_managed_area_id, referent_tag_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territory_department ADD CONSTRAINT FK_B30148E898260155 FOREIGN KEY (region_id) REFERENCES territory_region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territory_city ADD CONSTRAINT FK_376BCFC0AE80F5DF FOREIGN KEY (department_id) REFERENCES territory_department (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE managed_area_senator ADD CONSTRAINT FK_E41F872CAE80F5DF FOREIGN KEY (department_id) REFERENCES territory_department (id)');
        $this->addSql('ALTER TABLE referent_managed_area_referent_tag ADD CONSTRAINT FK_BD8B6D7A6B99CC25 FOREIGN KEY (referent_managed_area_id) REFERENCES managed_area_referent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE referent_managed_area_referent_tag ADD CONSTRAINT FK_BD8B6D7A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3DC184E71');
        $this->addSql('DROP INDEX UNIQ_562C7DA3DC184E71 ON adherents');
        $this->addSql('ALTER TABLE adherents ADD senator_managed_area_id INT UNSIGNED DEFAULT NULL, ADD european_deputy_managed_area_id INT UNSIGNED DEFAULT NULL, ADD referent_managed_area_id INT UNSIGNED DEFAULT NULL, DROP managed_area_id');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA38C271B0B FOREIGN KEY (senator_managed_area_id) REFERENCES managed_area_senator (id)');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA3F1F77730 FOREIGN KEY (european_deputy_managed_area_id) REFERENCES managed_area_european_deputy (id)');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA36B99CC25 FOREIGN KEY (referent_managed_area_id) REFERENCES managed_area_referent (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA38C271B0B ON adherents (senator_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3F1F77730 ON adherents (european_deputy_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA36B99CC25 ON adherents (referent_managed_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territory_city DROP FOREIGN KEY FK_376BCFC0AE80F5DF');
        $this->addSql('ALTER TABLE managed_area_senator DROP FOREIGN KEY FK_E41F872CAE80F5DF');
        $this->addSql('ALTER TABLE territory_department DROP FOREIGN KEY FK_B30148E898260155');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA38C271B0B');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3F1F77730');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA36B99CC25');
        $this->addSql('ALTER TABLE referent_managed_area_referent_tag DROP FOREIGN KEY FK_BD8B6D7A6B99CC25');
        $this->addSql('DROP TABLE territory_department');
        $this->addSql('DROP TABLE territory_region');
        $this->addSql('DROP TABLE territory_city');
        $this->addSql('DROP TABLE managed_area_senator');
        $this->addSql('DROP TABLE managed_area_european_deputy');
        $this->addSql('DROP TABLE managed_area_referent');
        $this->addSql('DROP TABLE referent_managed_area_referent_tag');
        $this->addSql('DROP INDEX UNIQ_562C7DA38C271B0B ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA3F1F77730 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA36B99CC25 ON adherents');
        $this->addSql('ALTER TABLE adherents ADD managed_area_id INT DEFAULT NULL, DROP senator_managed_area_id, DROP european_deputy_managed_area_id, DROP referent_managed_area_id');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA3DC184E71 FOREIGN KEY (managed_area_id) REFERENCES referent_managed_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3DC184E71 ON adherents (managed_area_id)');
    }
}
