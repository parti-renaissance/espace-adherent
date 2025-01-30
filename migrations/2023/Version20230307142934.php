<?php

namespace Migrations;

use App\Entity\Geo\ZoneTagEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230307142934 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_A4CCEF076FBC9426 ON geo_zone');
        $this->addSql('ALTER TABLE geo_zone CHANGE tags tags LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');

        $this->addSql(\sprintf(
            "UPDATE geo_zone SET tags = '%s' WHERE code IN ('64PB', '64B', '20', '69D', '69M')",
            ZoneTagEnum::SUB_ZONE
        ));
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          geo_zone
        CHANGE
          tags tags VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`');
        $this->addSql('CREATE INDEX IDX_A4CCEF076FBC9426 ON geo_zone (tags)');
    }
}
