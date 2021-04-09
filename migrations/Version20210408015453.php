<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210408015453 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE internal_api_application (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          application_name VARCHAR(200) NOT NULL,
          hostname VARCHAR(200) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX internal_application_uuid_unique (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE internal_api_application');
    }
}
