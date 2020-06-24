<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200624175233 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_user_list_definition_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          elected_representative_id INT NOT NULL, 
          user_list_definition_id INT UNSIGNED NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          administrator_id INT DEFAULT NULL, 
          action VARCHAR(20) NOT NULL, 
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
          INDEX IDX_1ECF7566D38DA5D3 (elected_representative_id), 
          INDEX IDX_1ECF7566F74563E3 (user_list_definition_id), 
          INDEX IDX_1ECF756625F06C53 (adherent_id), 
          INDEX IDX_1ECF75664B09E92C (administrator_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        ADD 
          CONSTRAINT FK_1ECF7566D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        ADD 
          CONSTRAINT FK_1ECF7566F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        ADD 
          CONSTRAINT FK_1ECF756625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          elected_representative_user_list_definition_history 
        ADD 
          CONSTRAINT FK_1ECF75664B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE elected_representative_user_list_definition_history');
    }
}
