<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220207135908 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign ADD associated TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('CREATE INDEX IDX_EF50C8E83826374DFE28FD87 ON pap_campaign (begin_at, finish_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_EF50C8E83826374DFE28FD87 ON pap_campaign');
        $this->addSql('ALTER TABLE pap_campaign DROP associated');
    }
}
