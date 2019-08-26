<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190823172134 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE donators (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          identifier VARCHAR(255) NOT NULL, 
          last_name VARCHAR(50) DEFAULT NULL, 
          first_name VARCHAR(100) DEFAULT NULL, 
          city VARCHAR(50) DEFAULT NULL, 
          country VARCHAR(2) NOT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          last_donation_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
          INDEX IDX_A902FDD725F06C53 (adherent_id), 
          UNIQUE INDEX donator_identifier_unique (identifier), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE donator_identifier (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          identifier VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          donators 
        ADD 
          CONSTRAINT FK_A902FDD725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE donations ADD donator_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          donations 
        ADD 
          CONSTRAINT FK_CDE98962831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id)');
        $this->addSql('CREATE INDEX IDX_CDE98962831BACAF ON donations (donator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP FOREIGN KEY FK_CDE98962831BACAF');
        $this->addSql('DROP TABLE donators');
        $this->addSql('DROP TABLE donator_identifier');
        $this->addSql('DROP INDEX IDX_CDE98962831BACAF ON donations');
        $this->addSql('ALTER TABLE donations DROP donator_id');
    }

    public function postUp(Schema $schema)
    {
        $this->connection->insert('donator_identifier', ['identifier' => '000050']);
    }
}
