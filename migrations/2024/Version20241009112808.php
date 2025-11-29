<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241009112808 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_static_label (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          category_id INT UNSIGNED NOT NULL,
          code VARCHAR(255) NOT NULL,
          label VARCHAR(255) NOT NULL,
          UNIQUE INDEX UNIQ_F204E90277153098 (code),
          UNIQUE INDEX UNIQ_F204E902EA750E8 (label),
          INDEX IDX_F204E90212469DE2 (category_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE adherent_static_label_category (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(255) NOT NULL,
          label VARCHAR(255) NOT NULL,
          sync TINYINT(1) DEFAULT 0 NOT NULL,
          UNIQUE INDEX UNIQ_36F0D66C77153098 (code),
          UNIQUE INDEX UNIQ_36F0D66CEA750E8 (label),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE adherent_adherent_static_label (
          adherent_id INT UNSIGNED NOT NULL,
          adherent_static_label_id INT UNSIGNED NOT NULL,
          INDEX IDX_64905F4225F06C53 (adherent_id),
          INDEX IDX_64905F42ED149D10 (adherent_static_label_id),
          PRIMARY KEY(
            adherent_id, adherent_static_label_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_static_label
        ADD
          CONSTRAINT FK_F204E90212469DE2 FOREIGN KEY (category_id) REFERENCES adherent_static_label_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_adherent_static_label
        ADD
          CONSTRAINT FK_64905F4225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_adherent_static_label
        ADD
          CONSTRAINT FK_64905F42ED149D10 FOREIGN KEY (adherent_static_label_id) REFERENCES adherent_static_label (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_static_label DROP FOREIGN KEY FK_F204E90212469DE2');
        $this->addSql('ALTER TABLE adherent_adherent_static_label DROP FOREIGN KEY FK_64905F4225F06C53');
        $this->addSql('ALTER TABLE adherent_adherent_static_label DROP FOREIGN KEY FK_64905F42ED149D10');
        $this->addSql('DROP TABLE adherent_static_label');
        $this->addSql('DROP TABLE adherent_static_label_category');
        $this->addSql('DROP TABLE adherent_adherent_static_label');
    }
}
