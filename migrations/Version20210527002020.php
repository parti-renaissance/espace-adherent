<?php

namespace Migrations;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210527002020 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages ADD source VARCHAR(255) DEFAULT \'platform\' NOT NULL');
        $this->addSql(sprintf("UPDATE adherent_messages SET source = '%s' WHERE label LIKE 'DataCorner:%%' OR label LIKE 'Pourunecause:%%'", AdherentMessageInterface::SOURCE_API));
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages DROP source');
    }
}
