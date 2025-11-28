<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240322150932 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrators DROP roles');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrators ADD roles LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
    }
}
