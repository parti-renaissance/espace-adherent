<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201006102454 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE geo_city_community_department (
          city_community_id INT UNSIGNED NOT NULL,
          department_id INT UNSIGNED NOT NULL,
          INDEX IDX_1E2D6D066D3B1930 (city_community_id),
          INDEX IDX_1E2D6D06AE80F5DF (department_id),
          PRIMARY KEY(
            city_community_id, department_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          geo_city_community_department
        ADD
          CONSTRAINT FK_1E2D6D066D3B1930 FOREIGN KEY (city_community_id) REFERENCES geo_city_community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          geo_city_community_department
        ADD
          CONSTRAINT FK_1E2D6D06AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE geo_city_community DROP FOREIGN KEY FK_E5805E08AE80F5DF');
        $this->addSql('DROP INDEX IDX_E5805E08AE80F5DF ON geo_city_community');
        $this->addSql('ALTER TABLE geo_city_community DROP department_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE geo_city_community_department');
        $this->addSql('ALTER TABLE geo_city_community ADD department_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          geo_city_community
        ADD
          CONSTRAINT FK_E5805E08AE80F5DF FOREIGN KEY (department_id) REFERENCES geo_department (id)');
        $this->addSql('CREATE INDEX IDX_E5805E08AE80F5DF ON geo_city_community (department_id)');
    }
}
