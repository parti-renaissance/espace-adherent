<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200226153640 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE municipal_manager_supervisor_role (
          id INT AUTO_INCREMENT NOT NULL, 
          referent_id INT UNSIGNED NOT NULL, 
          INDEX IDX_F304FF35E47E35 (referent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          municipal_manager_supervisor_role 
        ADD 
          CONSTRAINT FK_F304FF35E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherents ADD municipal_manager_supervisor_role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA39801977F FOREIGN KEY (
            municipal_manager_supervisor_role_id
          ) REFERENCES municipal_manager_supervisor_role (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA39801977F ON adherents (municipal_manager_supervisor_role_id)');
        $this->addSql('ALTER TABLE 
          referent_person_link 
        ADD 
          is_municipal_manager_supervisor TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA39801977F');
        $this->addSql('DROP TABLE municipal_manager_supervisor_role');
        $this->addSql('DROP INDEX UNIQ_562C7DA39801977F ON adherents');
        $this->addSql('ALTER TABLE adherents DROP municipal_manager_supervisor_role_id');
        $this->addSql('ALTER TABLE referent_person_link DROP is_municipal_manager_supervisor');
    }
}
