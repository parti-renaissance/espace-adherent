<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210419144627 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE coalition ADD youtube_id VARCHAR(11) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX cause_follower_uuid_unique ON cause_follower (uuid)');
        $this->addSql('CREATE UNIQUE INDEX coalition_follower_uuid_unique ON coalition_follower (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE coalition DROP youtube_id');
        $this->addSql('DROP INDEX cause_follower_uuid_unique ON cause_follower');
        $this->addSql('DROP INDEX coalition_follower_uuid_unique ON coalition_follower');
    }
}
