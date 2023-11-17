<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231117170244 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE web_hooks DROP FOREIGN KEY FK_CDB836AD19EB6921');
        $this->addSql('DROP TABLE web_hooks');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE web_hooks (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          client_id INT UNSIGNED NOT NULL,
          event VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          callbacks JSON NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          service VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX web_hook_event_client_id_unique (event, client_id),
          UNIQUE INDEX UNIQ_CDB836ADD17F50A6 (uuid),
          INDEX IDX_CDB836AD19EB6921 (client_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          web_hooks
        ADD
          CONSTRAINT FK_CDB836AD19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
