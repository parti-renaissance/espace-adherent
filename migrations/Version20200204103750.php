<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200204103750 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE assessor_role_association (
          id INT AUTO_INCREMENT NOT NULL, 
          vote_place_id INT DEFAULT NULL, 
          UNIQUE INDEX UNIQ_B93395C2F3F90B30 (vote_place_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          assessor_role_association 
        ADD 
          CONSTRAINT FK_B93395C2F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id)');
        $this->addSql('ALTER TABLE adherents ADD assessor_role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3E4A5D7A5 FOREIGN KEY (assessor_role_id) REFERENCES assessor_role_association (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3E4A5D7A5 ON adherents (assessor_role_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3E4A5D7A5');
        $this->addSql('DROP TABLE assessor_role_association');
        $this->addSql('DROP INDEX UNIQ_562C7DA3E4A5D7A5 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP assessor_role_id');
    }
}
