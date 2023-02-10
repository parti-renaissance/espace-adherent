<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230209153258 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_political_function DROP FOREIGN KEY FK_303BAF416C1129CD');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        ADD
          uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        CHANGE
          id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_38609146D17F50A6 ON elected_representative_mandate (uuid)');
        $this->addSql('ALTER TABLE
          elected_representative_political_function
        CHANGE
          mandate_id mandate_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_political_function
        ADD
          CONSTRAINT FK_303BAF416C1129CD FOREIGN KEY (mandate_id) REFERENCES elected_representative_mandate (id)');

        $this->addSql('UPDATE elected_representative_mandate SET uuid = UUID() WHERE uuid IS NULL');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        CHANGE
          uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_38609146D17F50A6 ON elected_representative_mandate');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP uuid, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_political_function
        CHANGE
          mandate_id mandate_id INT NOT NULL');
    }
}
