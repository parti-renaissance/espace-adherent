<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200605171830 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE my_team_delegated_access (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          delegator_id INT UNSIGNED DEFAULT NULL, 
          delegated_id INT UNSIGNED DEFAULT NULL, 
          role VARCHAR(255) NOT NULL, 
          accesses LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', 
          restricted_cities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          type VARCHAR(255) NOT NULL, 
          INDEX IDX_421C13B98825BEFA (delegator_id), 
          UNIQUE INDEX UNIQ_421C13B9B7E7AE18 (delegated_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE my_team_delegate_access_committee (
          delegated_access_id INT UNSIGNED NOT NULL, 
          committee_id INT UNSIGNED NOT NULL, 
          INDEX IDX_C52A163FFD98FA7A (delegated_access_id), 
          INDEX IDX_C52A163FED1A100B (committee_id), 
          PRIMARY KEY(
            delegated_access_id, committee_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          my_team_delegated_access 
        ADD 
          CONSTRAINT FK_421C13B98825BEFA FOREIGN KEY (delegator_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          my_team_delegated_access 
        ADD 
          CONSTRAINT FK_421C13B9B7E7AE18 FOREIGN KEY (delegated_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          my_team_delegate_access_committee 
        ADD 
          CONSTRAINT FK_C52A163FFD98FA7A FOREIGN KEY (delegated_access_id) REFERENCES my_team_delegated_access (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          my_team_delegate_access_committee 
        ADD 
          CONSTRAINT FK_C52A163FED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE my_team_delegate_access_committee DROP FOREIGN KEY FK_C52A163FFD98FA7A');
        $this->addSql('DROP TABLE my_team_delegated_access');
        $this->addSql('DROP TABLE my_team_delegate_access_committee');
    }
}
