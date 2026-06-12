<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612101818 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP pap_user_role');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD pap_user_role TINYINT DEFAULT 0 NOT NULL');
    }
}
