<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171229105402 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE timeline_measures (id BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, link VARCHAR(255) DEFAULT NULL, status VARCHAR(50) NOT NULL, updated_at DATETIME NOT NULL, global TINYINT(1) DEFAULT \'0\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE timeline_measures_profiles (measure_id BIGINT NOT NULL, profile_id BIGINT NOT NULL, INDEX IDX_B83D81AE5DA37D00 (measure_id), INDEX IDX_B83D81AECCFA12B8 (profile_id), PRIMARY KEY(measure_id, profile_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE timeline_themes (id BIGINT AUTO_INCREMENT NOT NULL, media_id BIGINT DEFAULT NULL, title VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, featured TINYINT(1) DEFAULT \'0\' NOT NULL, display_media TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8ADDB8F6989D9B62 (slug), INDEX IDX_8ADDB8F6EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE timeline_themes_measures (id BIGINT AUTO_INCREMENT NOT NULL, theme_id BIGINT DEFAULT NULL, measure_id BIGINT DEFAULT NULL, featured TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_EB8A7B0C59027487 (theme_id), INDEX IDX_EB8A7B0C5DA37D00 (measure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE timeline_profiles (id BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_DB00DE3B989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE timeline_measures_profiles ADD CONSTRAINT FK_B83D81AE5DA37D00 FOREIGN KEY (measure_id) REFERENCES timeline_measures (id)');
        $this->addSql('ALTER TABLE timeline_measures_profiles ADD CONSTRAINT FK_B83D81AECCFA12B8 FOREIGN KEY (profile_id) REFERENCES timeline_profiles (id)');
        $this->addSql('ALTER TABLE timeline_themes ADD CONSTRAINT FK_8ADDB8F6EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE timeline_themes_measures ADD CONSTRAINT FK_EB8A7B0C59027487 FOREIGN KEY (theme_id) REFERENCES timeline_themes (id)');
        $this->addSql('ALTER TABLE timeline_themes_measures ADD CONSTRAINT FK_EB8A7B0C5DA37D00 FOREIGN KEY (measure_id) REFERENCES timeline_measures (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE timeline_measures_profiles DROP FOREIGN KEY FK_B83D81AE5DA37D00');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP FOREIGN KEY FK_EB8A7B0C5DA37D00');
        $this->addSql('ALTER TABLE timeline_themes_measures DROP FOREIGN KEY FK_EB8A7B0C59027487');
        $this->addSql('ALTER TABLE timeline_measures_profiles DROP FOREIGN KEY FK_B83D81AECCFA12B8');
        $this->addSql('DROP TABLE timeline_measures');
        $this->addSql('DROP TABLE timeline_measures_profiles');
        $this->addSql('DROP TABLE timeline_themes');
        $this->addSql('DROP TABLE timeline_themes_measures');
        $this->addSql('DROP TABLE timeline_profiles');
    }
}
