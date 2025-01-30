<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241231142707 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP old_password');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD old_password VARCHAR(255) DEFAULT NULL');
    }
}
