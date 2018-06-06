<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180612102353 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE districts (
                            id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                            adherent_id INT UNSIGNED DEFAULT NULL, 
                            countries LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', 
                            code VARCHAR(6) NOT NULL, 
                            `number` SMALLINT UNSIGNED NOT NULL, 
                            name VARCHAR(50) NOT NULL, 
                            department_code VARCHAR(5) NOT NULL, 
                            geo_shape GEOMETRY NOT NULL COMMENT \'(DC2Type:geometry)\', 
                            UNIQUE INDEX UNIQ_68E318DC25F06C53 (adherent_id), 
                            UNIQUE INDEX district_code_unique (code), 
                            UNIQUE INDEX district_department_code_number (department_code, `number`), 
                            PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE districts ADD CONSTRAINT FK_68E318DC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE districts');
    }
}
