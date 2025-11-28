<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221003152108 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          donators
        ADD
          uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        ADD
          created_at DATETIME DEFAULT NULL,
        ADD
          updated_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A902FDD7D17F50A6 ON donators (uuid)');

        $this->addSql('UPDATE donators SET uuid = UUID() WHERE uuid IS NULL');
        $this->addSql('UPDATE donators AS d SET d.created_at = COALESCE((SELECT d2.created_at FROM donations AS d2 WHERE d2.donator_id = d.id ORDER BY d2.created_at LIMIT 1), NOW()) WHERE d.created_at IS NULL');
        $this->addSql('UPDATE donators AS d SET d.updated_at = COALESCE(d.created_at, NOW()) WHERE d.updated_at IS NULL');

        $this->addSql('ALTER TABLE
          donators
        CHANGE
          uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
        CHANGE
          created_at created_at DATETIME NOT NULL,
        CHANGE
          updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_A902FDD7D17F50A6 ON donators');
        $this->addSql('ALTER TABLE donators DROP uuid, DROP created_at, DROP updated_at');
    }
}
