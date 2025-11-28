<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230124144750 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_label DROP FOREIGN KEY FK_D8143704D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP FOREIGN KEY FK_38609146D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_political_function DROP FOREIGN KEY FK_303BAF41D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_social_network_link DROP FOREIGN KEY FK_231377B5D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_sponsorship DROP FOREIGN KEY FK_CA6D486D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_user_list_definition DROP FOREIGN KEY FK_A9C53A24D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_user_list_definition_history DROP FOREIGN KEY FK_1ECF7566D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative
        ADD
          created_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        ADD
          created_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          created_at DATETIME DEFAULT NOW(),
        ADD
          updated_at DATETIME DEFAULT NOW(),
        CHANGE
          id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative
        CHANGE created_at created_at DATETIME NOT NULL,
        CHANGE updated_at updated_at DATETIME NOT NULL,
        ADD
          CONSTRAINT FK_BF51F0FD9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          elected_representative
        ADD
          CONSTRAINT FK_BF51F0FDCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          elected_representative
        ADD
          CONSTRAINT FK_BF51F0FD85C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          elected_representative
        ADD
          CONSTRAINT FK_BF51F0FDDF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF51F0FDD17F50A6 ON elected_representative (uuid)');
        $this->addSql('CREATE INDEX IDX_BF51F0FD9DF5350C ON elected_representative (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_BF51F0FDCF1918FF ON elected_representative (updated_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_BF51F0FD85C9D733 ON elected_representative (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_BF51F0FDDF6CFDC9 ON elected_representative (updated_by_adherent_id)');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition
        CHANGE
          elected_representative_id elected_representative_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_label
        CHANGE
          elected_representative_id elected_representative_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        CHANGE
          elected_representative_id elected_representative_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_political_function
        CHANGE
          elected_representative_id elected_representative_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_social_network_link
        CHANGE
          elected_representative_id elected_representative_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_sponsorship
        CHANGE
          elected_representative_id elected_representative_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        CHANGE
          elected_representative_id elected_representative_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition
        ADD
          CONSTRAINT FK_A9C53A24D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_label
        ADD
          CONSTRAINT FK_D8143704D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        ADD
          CONSTRAINT FK_38609146D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_political_function
        ADD
          CONSTRAINT FK_303BAF41D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_social_network_link
        ADD
          CONSTRAINT FK_231377B5D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_sponsorship
        ADD
          CONSTRAINT FK_CA6D486D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        ADD
          CONSTRAINT FK_1ECF7566D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative DROP FOREIGN KEY FK_BF51F0FD9DF5350C');
        $this->addSql('ALTER TABLE elected_representative DROP FOREIGN KEY FK_BF51F0FDCF1918FF');
        $this->addSql('ALTER TABLE elected_representative DROP FOREIGN KEY FK_BF51F0FD85C9D733');
        $this->addSql('ALTER TABLE elected_representative DROP FOREIGN KEY FK_BF51F0FDDF6CFDC9');
        $this->addSql('DROP INDEX UNIQ_BF51F0FDD17F50A6 ON elected_representative');
        $this->addSql('DROP INDEX IDX_BF51F0FD9DF5350C ON elected_representative');
        $this->addSql('DROP INDEX IDX_BF51F0FDCF1918FF ON elected_representative');
        $this->addSql('DROP INDEX IDX_BF51F0FD85C9D733 ON elected_representative');
        $this->addSql('DROP INDEX IDX_BF51F0FDDF6CFDC9 ON elected_representative');
        $this->addSql('ALTER TABLE
          elected_representative
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id,
        DROP
          created_by_adherent_id,
        DROP
          updated_by_adherent_id,
        DROP
          created_at,
        DROP
          updated_at,
        CHANGE
          id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_label
        CHANGE
          elected_representative_id elected_representative_id INT NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        CHANGE
          elected_representative_id elected_representative_id INT NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_political_function
        CHANGE
          elected_representative_id elected_representative_id INT NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_social_network_link
        CHANGE
          elected_representative_id elected_representative_id INT NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_sponsorship
        CHANGE
          elected_representative_id elected_representative_id INT NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition
        CHANGE
          elected_representative_id elected_representative_id INT NOT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_user_list_definition_history
        CHANGE
          elected_representative_id elected_representative_id INT NOT NULL');
    }
}
