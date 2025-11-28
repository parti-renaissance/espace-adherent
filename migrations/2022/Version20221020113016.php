<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221020113016 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP comments_cgu_accepted');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD comments_cgu_accepted TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
