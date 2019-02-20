<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190128175553 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD nickname VARCHAR(25) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3A188FE64 ON adherents (nickname)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_562C7DA3A188FE64 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP nickname');
    }
}
