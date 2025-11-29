<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240716171532 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home_blocks DROP FOREIGN KEY FK_3EE9FCC5EA9FDD75');
        $this->addSql('DROP TABLE home_blocks');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE home_blocks (
          id BIGINT AUTO_INCREMENT NOT NULL,
          media_id BIGINT DEFAULT NULL,
          position SMALLINT NOT NULL,
          position_name VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          title VARCHAR(70) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          subtitle VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          TYPE VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          link VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          updated_at DATETIME NOT NULL,
          display_filter TINYINT(1) DEFAULT 1 NOT NULL,
          display_titles TINYINT(1) DEFAULT 0 NOT NULL,
          display_block TINYINT(1) DEFAULT 1 NOT NULL,
          title_cta VARCHAR(70) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          color_cta VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          bg_color VARCHAR(6) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          video_controls TINYINT(1) DEFAULT 0 NOT NULL,
          video_autoplay_loop TINYINT(1) DEFAULT 1 NOT NULL,
          for_renaissance TINYINT(1) DEFAULT 0 NOT NULL,
          INDEX IDX_3EE9FCC5EA9FDD75 (media_id),
          UNIQUE INDEX UNIQ_3EE9FCC5462CE4F5 (position),
          UNIQUE INDEX UNIQ_3EE9FCC54DBB5058 (position_name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          home_blocks
        ADD
          CONSTRAINT FK_3EE9FCC5EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
