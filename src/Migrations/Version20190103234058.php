<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190103234058 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE adherent_messages (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                author_id INT UNSIGNED DEFAULT NULL, 
                label VARCHAR(255) NOT NULL, 
                subject VARCHAR(255) NOT NULL, 
                content LONGTEXT NOT NULL, 
                external_id VARCHAR(255) DEFAULT NULL, 
                status VARCHAR(255) NOT NULL, 
                synchronized TINYINT(1) DEFAULT \'0\' NOT NULL, 
                filter LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', 
                uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                created_at DATETIME NOT NULL, 
                updated_at DATETIME NOT NULL, 
                type VARCHAR(255) NOT NULL, 
                INDEX IDX_D187C183F675F31B (author_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE adherent_messages ADD CONSTRAINT FK_D187C183F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE referent_managed_users_message CHANGE include_supevisors include_supervisors TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_messages');
        $this->addSql('ALTER TABLE referent_managed_users_message CHANGE include_supervisors include_supevisors TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
