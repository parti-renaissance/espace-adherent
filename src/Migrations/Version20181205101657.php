<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181205101657 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ideas_workshop_ideas_themes (idea_id INT UNSIGNED NOT NULL, theme_id INT NOT NULL, INDEX IDX_DB4ED3145B6FEF7D (idea_id), INDEX IDX_DB4ED31459027487 (theme_id), PRIMARY KEY(idea_id, theme_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_themes ADD CONSTRAINT FK_DB4ED3145B6FEF7D FOREIGN KEY (idea_id) REFERENCES ideas_workshop_idea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ideas_workshop_ideas_themes ADD CONSTRAINT FK_DB4ED31459027487 FOREIGN KEY (theme_id) REFERENCES ideas_workshop_theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP FOREIGN KEY FK_CA001C7259027487');
        $this->addSql('DROP INDEX IDX_CA001C7259027487 ON ideas_workshop_idea');
        $this->addSql('ALTER TABLE ideas_workshop_idea DROP theme_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ideas_workshop_ideas_themes');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD theme_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_idea ADD CONSTRAINT FK_CA001C7259027487 FOREIGN KEY (theme_id) REFERENCES ideas_workshop_theme (id)');
        $this->addSql('CREATE INDEX IDX_CA001C7259027487 ON ideas_workshop_idea (theme_id)');
    }
}
