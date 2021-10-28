<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180518100736 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE unregistration_referent_tag (
      unregistration_id INT NOT NULL,
      referent_tag_id INT UNSIGNED NOT NULL,
      INDEX IDX_59B7AC414D824CA (unregistration_id),
      INDEX IDX_59B7AC49C262DB3 (referent_tag_id),
      PRIMARY KEY(unregistration_id, referent_tag_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
SQL
);
        $this->addSql('ALTER TABLE unregistration_referent_tag ADD CONSTRAINT FK_59B7AC414D824CA FOREIGN KEY (unregistration_id) REFERENCES unregistrations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unregistration_referent_tag ADD CONSTRAINT FK_59B7AC49C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE unregistration_referent_tag');
    }
}
