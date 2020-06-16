<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200602175713 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          timeline_profile_translations CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          CHANGE locale locale VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE 
          timeline_profile_translations RENAME INDEX uniq_41b3a6da2c2ac5d34180c698 TO timeline_profile_translations_unique_translation');
        $this->addSql('ALTER TABLE 
          timeline_manifesto_translations CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          CHANGE locale locale VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE 
          timeline_manifesto_translations RENAME INDEX uniq_f7bd6c172c2ac5d34180c698 TO timeline_manifesto_translations_unique_translation');
        $this->addSql('ALTER TABLE 
          timeline_measure_translations CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          CHANGE locale locale VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE 
          timeline_measure_translations RENAME INDEX uniq_5c9eb6072c2ac5d34180c698 TO timeline_measure_translations_unique_translation');
        $this->addSql('ALTER TABLE 
          timeline_theme_translations CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          CHANGE locale locale VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE 
          timeline_theme_translations RENAME INDEX uniq_f81f72932c2ac5d34180c698 TO timeline_theme_translations_unique_translation');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          timeline_manifesto_translations CHANGE id id INT AUTO_INCREMENT NOT NULL, 
          CHANGE locale locale VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          timeline_manifesto_translations RENAME INDEX timeline_manifesto_translations_unique_translation TO UNIQ_F7BD6C172C2AC5D34180C698');
        $this->addSql('ALTER TABLE 
          timeline_measure_translations CHANGE id id INT AUTO_INCREMENT NOT NULL, 
          CHANGE locale locale VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          timeline_measure_translations RENAME INDEX timeline_measure_translations_unique_translation TO UNIQ_5C9EB6072C2AC5D34180C698');
        $this->addSql('ALTER TABLE 
          timeline_profile_translations CHANGE id id INT AUTO_INCREMENT NOT NULL, 
          CHANGE locale locale VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          timeline_profile_translations RENAME INDEX timeline_profile_translations_unique_translation TO UNIQ_41B3A6DA2C2AC5D34180C698');
        $this->addSql('ALTER TABLE 
          timeline_theme_translations CHANGE id id INT AUTO_INCREMENT NOT NULL, 
          CHANGE locale locale VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE 
          timeline_theme_translations RENAME INDEX timeline_theme_translations_unique_translation TO UNIQ_F81F72932C2AC5D34180C698');
    }
}
