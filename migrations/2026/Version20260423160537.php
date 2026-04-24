<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423160537 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `events` SET visibility = 'invitation' WHERE visibility = 'invitation_agora'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE `events` SET visibility = 'invitation_agora' WHERE visibility = 'invitation'");
    }
}
