<?php

namespace Migrations;

use App\Event\EventTypeEnum;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210202145617 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(sprintf("UPDATE events SET type = '%s' WHERE type = 'event'", EventTypeEnum::TYPE_COMMITTEE));
        $this->addSql(sprintf("UPDATE events SET type = '%s' WHERE type = 'institutional_event'", EventTypeEnum::TYPE_INSTITUTIONAL));
        $this->addSql(sprintf("UPDATE events SET type = '%s' WHERE type = 'municipal_event'", EventTypeEnum::TYPE_MUNICIPAL));
    }

    public function down(Schema $schema): void
    {
        $this->addSql(sprintf("UPDATE events SET type = 'event' WHERE type = '%s'", EventTypeEnum::TYPE_COMMITTEE));
        $this->addSql(sprintf("UPDATE events SET type = 'institutional_event' WHERE type = '%s'", EventTypeEnum::TYPE_INSTITUTIONAL));
        $this->addSql(sprintf("UPDATE events SET type = 'municipal_event' WHERE type = '%s'", EventTypeEnum::TYPE_MUNICIPAL));
    }
}
