<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180125175213 extends AbstractMigration
{
    public function preUp(Schema $schema): void
    {
        if ($schema->hasTable('web_hook_callbacks')) {
            $this->addSql('ALTER TABLE web_hook_callbacks DROP FOREIGN KEY FK_74BE63305A7D4251');
            $this->addSql('DROP TABLE web_hook_callbacks');
            $this->addSql('DROP INDEX web_hook_event_unique ON web_hooks');
            $this->addSql('DROP TABLE web_hooks');
        }
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE web_hooks (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
                client_id INT UNSIGNED NOT NULL, 
                event VARCHAR(64) NOT NULL, 
                callbacks JSON NOT NULL, 
                uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                INDEX IDX_CDB836AD19EB6921 (client_id), 
                UNIQUE INDEX web_hook_uuid_unique (uuid), 
                UNIQUE INDEX web_hook_event_client_id_unique (event, client_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE web_hooks ADD CONSTRAINT FK_CDB836AD19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE web_hooks');
    }
}
