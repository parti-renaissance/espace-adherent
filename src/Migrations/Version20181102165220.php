<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181102165220 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE jecoute_suggested_question (
                id INT NOT NULL, 
                published TINYINT(1) DEFAULT \'0\' NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );

        $this->addSql('ALTER TABLE jecoute_suggested_question ADD CONSTRAINT FK_8280E9DABF396750 FOREIGN KEY (id) REFERENCES jecoute_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jecoute_survey CHANGE published published TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE jecoute_question ADD discr VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jecoute_suggested_question');
        $this->addSql('ALTER TABLE jecoute_question DROP discr');
        $this->addSql('ALTER TABLE jecoute_survey CHANGE published published TINYINT(1) NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeUpdate("UPDATE jecoute_question SET discr = 'question'");
    }
}
