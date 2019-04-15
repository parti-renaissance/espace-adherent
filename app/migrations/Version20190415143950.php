<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190415143950 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD referent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA335E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_562C7DA335E47E35 ON adherents (referent_id)');
        $this->addSql('ALTER TABLE referent_person_link ADD adherent_id INT UNSIGNED DEFAULT NULL');
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
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA335E47E35');
        $this->addSql('DROP INDEX IDX_562C7DA335E47E35 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP referent_id');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A25F06C53');
        $this->addSql('DROP INDEX IDX_BC75A60A25F06C53 ON referent_person_link');
        $this->addSql('ALTER TABLE referent_person_link DROP adherent_id');
    }
}
