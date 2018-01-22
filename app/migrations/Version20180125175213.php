<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180125175213 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(
            'CREATE TABLE web_hooks (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                event VARCHAR(64) NOT NULL,
                uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
                UNIQUE INDEX web_hook_uuid_unique (uuid),
                UNIQUE INDEX web_hook_event_unique (event),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE web_hook_callbacks (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                web_hook_id INT UNSIGNED NOT NULL,
                client_id INT UNSIGNED NOT NULL,
                urls JSON NOT NULL,
                uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
                INDEX IDX_74BE63305A7D4251 (web_hook_id),
                INDEX IDX_74BE633019EB6921 (client_id),
                UNIQUE INDEX web_hook_callback_uuid_unique (uuid),
                UNIQUE INDEX web_hook_callback_vs_client_unique (web_hook_id, client_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE web_hook_callbacks ADD CONSTRAINT FK_74BE63305A7D4251 FOREIGN KEY (web_hook_id) REFERENCES web_hooks (id)');
        $this->addSql('ALTER TABLE web_hook_callbacks ADD CONSTRAINT FK_74BE633019EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE web_hook_callbacks DROP FOREIGN KEY FK_74BE63305A7D4251');
        $this->addSql('DROP TABLE web_hooks');
        $this->addSql('DROP TABLE web_hook_callbacks');
    }
}
