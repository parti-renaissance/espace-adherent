<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210729162231 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE scope (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) NOT NULL,
          name VARCHAR(100) NOT NULL,
          features LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          apps LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          UNIQUE INDEX scope_code_unique (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql(<<<'SQL'
            UPDATE oauth_clients
            SET requested_roles = REPLACE(requested_roles, 'ROLE_DATA_CORNER', 'DATA_CORNER')
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE scopes');

        $this->addSql(<<<'SQL'
            UPDATE oauth_clients
            SET requested_roles = REPLACE(requested_roles, 'DATA_CORNER', 'ROLE_DATA_CORNER')
SQL
        );
    }
}
