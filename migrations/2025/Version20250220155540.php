<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250220155540 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_73079D00772E836A ON referral (identifier)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_73079D00772E836A ON referral');
    }
}
