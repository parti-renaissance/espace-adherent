<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170502113039 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_invitations (id INT UNSIGNED AUTO_INCREMENT NOT NULL, event_id INT UNSIGNED DEFAULT NULL, email VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, client_ip VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, INDEX IDX_88AEC32E71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_invitations ADD CONSTRAINT FK_88AEC32E71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE event_invitations');
    }
}
