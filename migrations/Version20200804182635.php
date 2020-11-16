<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200804182635 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council_quality (id INT UNSIGNED AUTO_INCREMENT NOT NULL, territorial_council_membership_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, zone VARCHAR(255) NOT NULL, joined_at DATE NOT NULL, INDEX IDX_C018E022E797FAB0 (territorial_council_membership_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territorial_council_quality ADD CONSTRAINT FK_C018E022E797FAB0 FOREIGN KEY (territorial_council_membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territorial_council_membership DROP qualities');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE territorial_council_quality');
        $this->addSql('ALTER TABLE territorial_council_membership ADD qualities LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\'');
    }
}
