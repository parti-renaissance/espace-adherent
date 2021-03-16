<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170503015627 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE events_invitations (id INT UNSIGNED AUTO_INCREMENT NOT NULL, event_id INT UNSIGNED DEFAULT NULL, email VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, guests LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, INDEX IDX_B94D5AAD71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE events_invitations ADD CONSTRAINT FK_B94D5AAD71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('DROP TABLE event_invitations');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_invitations (id INT UNSIGNED AUTO_INCREMENT NOT NULL, event_id INT UNSIGNED DEFAULT NULL, email VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, message VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, client_ip VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', first_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, last_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_88AEC32E71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_invitations ADD CONSTRAINT FK_88AEC32E71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('DROP TABLE events_invitations');
    }
}
