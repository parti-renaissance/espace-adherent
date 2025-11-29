<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221209122913 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE designation_poll (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          label VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_3D0766CED17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE designation_poll_question (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          poll_id INT UNSIGNED NOT NULL,
          content VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          position INT NOT NULL,
          UNIQUE INDEX UNIQ_83F55735D17F50A6 (uuid),
          INDEX IDX_83F557353C947C0F (poll_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE designation_poll_question_choice (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          question_id INT UNSIGNED NOT NULL,
          label VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_AC70C953D17F50A6 (uuid),
          INDEX IDX_AC70C9531E27F6BF (question_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          designation_poll_question
        ADD
          CONSTRAINT FK_83F557353C947C0F FOREIGN KEY (poll_id) REFERENCES designation_poll (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          designation_poll_question_choice
        ADD
          CONSTRAINT FK_AC70C9531E27F6BF FOREIGN KEY (question_id) REFERENCES designation_poll_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          designation
        ADD
          poll_id INT UNSIGNED DEFAULT NULL,
        ADD
          created_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        ADD
          custom_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          designation
        ADD
          CONSTRAINT FK_8947610D3C947C0F FOREIGN KEY (poll_id) REFERENCES designation_poll (id)');
        $this->addSql('ALTER TABLE
          designation
        ADD
          CONSTRAINT FK_8947610D9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          designation
        ADD
          CONSTRAINT FK_8947610DCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_8947610D3C947C0F ON designation (poll_id)');
        $this->addSql('CREATE INDEX IDX_8947610D9DF5350C ON designation (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_8947610DCF1918FF ON designation (updated_by_administrator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation DROP FOREIGN KEY FK_8947610D3C947C0F');
        $this->addSql('ALTER TABLE designation_poll_question DROP FOREIGN KEY FK_83F557353C947C0F');
        $this->addSql('ALTER TABLE designation_poll_question_choice DROP FOREIGN KEY FK_AC70C9531E27F6BF');
        $this->addSql('DROP TABLE designation_poll');
        $this->addSql('DROP TABLE designation_poll_question');
        $this->addSql('DROP TABLE designation_poll_question_choice');
        $this->addSql('ALTER TABLE designation DROP FOREIGN KEY FK_8947610D9DF5350C');
        $this->addSql('ALTER TABLE designation DROP FOREIGN KEY FK_8947610DCF1918FF');
        $this->addSql('DROP INDEX IDX_8947610D3C947C0F ON designation');
        $this->addSql('DROP INDEX IDX_8947610D9DF5350C ON designation');
        $this->addSql('DROP INDEX IDX_8947610DCF1918FF ON designation');
        $this->addSql('ALTER TABLE
          designation
        DROP
          poll_id,
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id,
        DROP
          custom_title');
    }
}
