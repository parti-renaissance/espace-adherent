<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210820153501 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE phoning_campaign (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          team_id INT UNSIGNED NOT NULL,
          audience_id INT UNSIGNED NOT NULL,
          administrator_id INT DEFAULT NULL,
          title VARCHAR(255) NOT NULL,
          goal INT NOT NULL,
          finish_at DATETIME NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_C3882BA4296CD8AE (team_id),
          UNIQUE INDEX UNIQ_C3882BA4848CC616 (audience_id),
          INDEX IDX_C3882BA44B09E92C (administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA4296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA4848CC616 FOREIGN KEY (audience_id) REFERENCES audience_backup (id)');
        $this->addSql('ALTER TABLE
          phoning_campaign
        ADD
          CONSTRAINT FK_C3882BA44B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE phoning_campaign');
    }
}
