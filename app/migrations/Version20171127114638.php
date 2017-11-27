<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171127114638 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE interactive (id INT UNSIGNED AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, meta VARCHAR(255) DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, subtitle VARCHAR(255) DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX interactive_choices_uuid_unique (uuid), UNIQUE INDEX interactive_slug_key_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE interactive_choices ADD interactive_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE interactive_choices ADD CONSTRAINT FK_3C6695A7329C9C7D FOREIGN KEY (interactive_id) REFERENCES interactive (id)');
        $this->addSql('CREATE INDEX IDX_3C6695A7329C9C7D ON interactive_choices (interactive_id)');
        $this->addSql('ALTER TABLE interactive RENAME INDEX interactive_choices_uuid_unique TO interactive_uuid_unique');
    }

    public function postUp(Schema $schema)
    {
        $this->connection->insert('interactive', [
            'slug' => 'ton-pouvoir-achat',
            'uuid' => '8893f3b2-a992-4766-9124-e78df08d28ef',
            'id' => 1,
        ]);

        $this->connection->executeUpdate('UPDATE interactive_choices SET interactive_id = 1');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE interactive_choices DROP FOREIGN KEY FK_3C6695A7329C9C7D');
        $this->addSql('DROP TABLE interactive');
        $this->addSql('DROP INDEX IDX_3C6695A7329C9C7D ON interactive_choices');
        $this->addSql('ALTER TABLE interactive_choices DROP interactive_id');
        $this->addSql('ALTER TABLE interactive RENAME INDEX interactive_uuid_unique TO interactive_choices_uuid_unique');
    }
}
