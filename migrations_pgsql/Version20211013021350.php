<?php

namespace MigrationsPgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211013021350 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE address (
          id BIGINT NOT NULL,
          number VARCHAR(255) NOT NULL,
          street VARCHAR(255) NOT NULL,
          PRIMARY KEY(id)
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE address_id_seq CASCADE');
        $this->addSql('DROP TABLE address');
    }
}
