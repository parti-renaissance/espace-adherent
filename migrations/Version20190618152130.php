<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190618152130 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation_articles RENAME TO formation_modules');

        $this->addSql('ALTER TABLE formation_modules RENAME INDEX uniq_784f66992b36786b TO UNIQ_6B4806AC2B36786B');
        $this->addSql('ALTER TABLE formation_modules RENAME INDEX uniq_784f6699989d9b62 TO UNIQ_6B4806AC989D9B62');
        $this->addSql('ALTER TABLE formation_modules RENAME INDEX idx_784f66992e30cd41 TO IDX_6B4806AC2E30CD41');
        $this->addSql('ALTER TABLE formation_modules RENAME INDEX idx_784f6699ea9fdd75 TO IDX_6B4806ACEA9FDD75');

        $this->addSql('ALTER TABLE formation_files DROP FOREIGN KEY FK_70BEDE2C7294869C');
        $this->addSql('CREATE TABLE formation_paths (
          id INT AUTO_INCREMENT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          description LONGTEXT NOT NULL, 
          UNIQUE INDEX UNIQ_FD311864989D9B62 (slug), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');

        $this->addSql('DROP INDEX IDX_70BEDE2C7294869C ON formation_files');
        $this->addSql('ALTER TABLE formation_files CHANGE article_id module_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          formation_files 
        ADD 
          CONSTRAINT FK_70BEDE2CAFC2B591 FOREIGN KEY (module_id) REFERENCES formation_modules (id)');
        $this->addSql('CREATE INDEX IDX_70BEDE2CAFC2B591 ON formation_files (module_id)');
        $this->addSql('ALTER TABLE formation_axes ADD path_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          formation_axes 
        ADD 
          CONSTRAINT FK_7E652CB6D96C566B FOREIGN KEY (path_id) REFERENCES formation_paths (id)');
        $this->addSql('CREATE INDEX IDX_7E652CB6D96C566B ON formation_axes (path_id)');

        $this->addSql('INSERT INTO formation_paths (title, slug, description) 
            VALUES (\'Parcours 1\', \'parcours-1\', \'Découvrez maintenant votre parcours personnalisé. Les modules sont numérotés pour vous permettre de compléter / renforcer vos compétences par ordre de priorité.\')'
        );

        $this->addSql('UPDATE formation_axes SET path_id = 1 WHERE path_id IS NULL');
        $this->addSql('ALTER TABLE formation_axes CHANGE path_id path_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE formation_modules RENAME INDEX uniq_6b4806ac2b36786b TO UNIQ_784F66992B36786B');
        $this->addSql('ALTER TABLE formation_modules RENAME INDEX uniq_6b4806ac989d9b62 TO UNIQ_784F6699989D9B62');
        $this->addSql('ALTER TABLE formation_modules RENAME INDEX idx_6b4806ac2e30cd41 TO IDX_784F66992E30CD41');
        $this->addSql('ALTER TABLE formation_modules RENAME INDEX idx_6b4806acea9fdd75 TO IDX_784F6699EA9FDD75');

        $this->addSql('ALTER TABLE formation_modules RENAME TO formation_articles');

        $this->addSql('ALTER TABLE formation_axes DROP FOREIGN KEY FK_7E652CB6D96C566B');
        $this->addSql('ALTER TABLE formation_files DROP FOREIGN KEY FK_70BEDE2CAFC2B591');

        $this->addSql('DROP TABLE formation_paths');
        $this->addSql('DROP INDEX IDX_7E652CB6D96C566B ON formation_axes');
        $this->addSql('ALTER TABLE formation_axes DROP path_id');
        $this->addSql('DROP INDEX IDX_70BEDE2CAFC2B591 ON formation_files');
        $this->addSql('ALTER TABLE formation_files CHANGE module_id article_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          formation_files 
        ADD 
          CONSTRAINT FK_70BEDE2C7294869C FOREIGN KEY (article_id) REFERENCES formation_articles (id)');
        $this->addSql('CREATE INDEX IDX_70BEDE2C7294869C ON formation_files (article_id)');
    }
}
