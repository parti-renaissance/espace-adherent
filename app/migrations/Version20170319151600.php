<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170319151600 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE procuration_proxies ADD referent_id INT UNSIGNED DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE procuration_proxies ADD CONSTRAINT FK_9B5E777A35E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9B5E777A35E47E35 ON procuration_proxies (referent_id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE procuration_proxies DROP FOREIGN KEY FK_9B5E777A35E47E35');
        $this->addSql('DROP INDEX IDX_9B5E777A35E47E35 ON procuration_proxies');
        $this->addSql('ALTER TABLE procuration_proxies DROP referent_id, DROP created_at, DROP updated_at');
    }
}
