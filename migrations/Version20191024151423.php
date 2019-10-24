<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191024151423 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donators ADD comment LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE donations ADD comment LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP comment');
        $this->addSql('ALTER TABLE donators DROP comment');
    }
}
