<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190124141525 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations CHANGE nationality nationality VARCHAR(2) DEFAULT NULL');
        $this->addSql('UPDATE donations SET nationality = NULL WHERE created_at < \'2019-01-23 18:08:00\' ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE donations SET nationality = \'FR\' WHERE created_at < \'2019-01-23 18:08:00\' ');
        $this->addSql('ALTER TABLE donations CHANGE nationality nationality VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
    }
}
