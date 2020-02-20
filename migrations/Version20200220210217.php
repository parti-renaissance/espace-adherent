<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200220210217 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cities (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          municipal_manager_id INT UNSIGNED DEFAULT NULL, 
          name VARCHAR(100) NOT NULL, 
          insee_code VARCHAR(10) NOT NULL, 
          UNIQUE INDEX UNIQ_D95DB16B15A3C1BC (insee_code), 
          UNIQUE INDEX UNIQ_D95DB16B7C14C7A2 (municipal_manager_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          cities 
        ADD 
          CONSTRAINT FK_D95DB16B7C14C7A2 FOREIGN KEY (municipal_manager_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cities');
    }
}
