<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230616145405 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE institutional_events_categories');
        $this->addSql('ALTER TABLE events DROP invitations');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574A12469DE2 FOREIGN KEY (category_id) REFERENCES events_categories (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE institutional_events_categories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT \'ENABLED\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_18A3A4175E237E06 (name),
          UNIQUE INDEX UNIQ_18A3A417989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A12469DE2');
        $this->addSql('ALTER TABLE events ADD invitations LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }
}
