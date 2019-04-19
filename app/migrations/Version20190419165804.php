<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190419165804 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE referent_team (
          id INT AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED NOT NULL, 
          referent_id INT UNSIGNED NOT NULL, 
          UNIQUE INDEX UNIQ_B8011BE025F06C53 (adherent_id), 
          INDEX IDX_B8011BE035E47E35 (referent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          referent_team 
        ADD 
          CONSTRAINT FK_B8011BE025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          referent_team 
        ADD 
          CONSTRAINT FK_B8011BE035E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          referent_person_link 
        ADD 
          adherent_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          is_co_referent TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE 
          referent_person_link 
        ADD 
          CONSTRAINT FK_BC75A60A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_BC75A60A25F06C53 ON referent_person_link (adherent_id)');

        $this->addSql('UPDATE referent_person_link
        INNER JOIN adherents ON adherents.email_address = referent_person_link.email
        SET referent_person_link.adherent_id = adherents.id'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE referent_team');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A25F06C53');
        $this->addSql('DROP INDEX IDX_BC75A60A25F06C53 ON referent_person_link');
        $this->addSql('ALTER TABLE referent_person_link DROP adherent_id, DROP is_co_referent');
    }
}
