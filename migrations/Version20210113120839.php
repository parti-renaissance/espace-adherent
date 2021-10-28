<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210113120839 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE committee_provisional_supervisor (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, committee_id INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_E394C3D425F06C53 (adherent_id), INDEX IDX_E394C3D4ED1A100B (committee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE committee_provisional_supervisor ADD CONSTRAINT FK_E394C3D425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE committee_provisional_supervisor ADD CONSTRAINT FK_E394C3D4ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_mandate ADD provisional TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE committee_provisional_supervisor');
        $this->addSql('ALTER TABLE adherent_mandate DROP provisional');
    }
}
