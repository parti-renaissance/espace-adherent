<?php

declare(strict_types=1);

namespace Migrations;

use App\Scope\ScopeVisibilityEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220119160854 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_34362099F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_news ADD visibility VARCHAR(30) DEFAULT NULL');
        $this->addSql(<<<'SQL'
            UPDATE jecoute_news
            SET visibility = CASE
                WHEN zone_id IS NOT NULL THEN :visibility_local
                ELSE :visibility_national
            END
            SQL,
            [
                'visibility_local' => ScopeVisibilityEnum::LOCAL,
                'visibility_national' => ScopeVisibilityEnum::NATIONAL,
            ]
        );
        $this->addSql('ALTER TABLE jecoute_news CHANGE visibility visibility VARCHAR(30) NOT NULL');

        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_34362099F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_34362099F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_news DROP visibility');
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          CONSTRAINT FK_34362099F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
    }
}
