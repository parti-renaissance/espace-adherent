<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211119194504 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE timeline_manifesto_translations CHANGE locale locale VARCHAR(5) NOT NULL');
        $this->addSql('ALTER TABLE timeline_measure_translations CHANGE locale locale VARCHAR(5) NOT NULL');
        $this->addSql('ALTER TABLE timeline_profile_translations CHANGE locale locale VARCHAR(5) NOT NULL');
        $this->addSql('ALTER TABLE timeline_theme_translations CHANGE locale locale VARCHAR(5) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          timeline_manifesto_translations
        CHANGE
          locale locale VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          timeline_measure_translations
        CHANGE
          locale locale VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          timeline_profile_translations
        CHANGE
          locale locale VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          timeline_theme_translations
        CHANGE
          locale locale VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
