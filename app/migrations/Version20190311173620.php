<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190311173620 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          jecoute_survey 
        ADD 
          administrator_id INT DEFAULT NULL, 
        ADD 
          type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE 
          jecoute_survey 
        ADD 
          CONSTRAINT FK_EC4948E54B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_EC4948E54B09E92C ON jecoute_survey (administrator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_survey DROP FOREIGN KEY FK_EC4948E54B09E92C');
        $this->addSql('DROP INDEX IDX_EC4948E54B09E92C ON jecoute_survey');
        $this->addSql('ALTER TABLE jecoute_survey DROP administrator_id, DROP type');
    }
}
