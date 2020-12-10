<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201202105306 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_zone ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_zone
        ADD
          CONSTRAINT FK_A4CCEF0780E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4CCEF0780E32C3E ON geo_zone (geo_data_id)');
        $this->addSql('ALTER TABLE geo_district ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_district
        ADD
          CONSTRAINT FK_DF78232680E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DF78232680E32C3E ON geo_district (geo_data_id)');
        $this->addSql('ALTER TABLE geo_country ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_country
        ADD
          CONSTRAINT FK_E465446480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E465446480E32C3E ON geo_country (geo_data_id)');
        $this->addSql('ALTER TABLE geo_foreign_district ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_foreign_district
        ADD
          CONSTRAINT FK_973BE1F180E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_973BE1F180E32C3E ON geo_foreign_district (geo_data_id)');
        $this->addSql('ALTER TABLE geo_canton ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_canton
        ADD
          CONSTRAINT FK_F04FC05F80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F04FC05F80E32C3E ON geo_canton (geo_data_id)');
        $this->addSql('ALTER TABLE geo_city ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_city
        ADD
          CONSTRAINT FK_297C2D3480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_297C2D3480E32C3E ON geo_city (geo_data_id)');
        $this->addSql('ALTER TABLE geo_borough ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_borough
        ADD
          CONSTRAINT FK_1449587480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1449587480E32C3E ON geo_borough (geo_data_id)');
        $this->addSql('ALTER TABLE geo_region ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_region
        ADD
          CONSTRAINT FK_A4B3C80880E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4B3C80880E32C3E ON geo_region (geo_data_id)');
        $this->addSql('ALTER TABLE geo_consular_district ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_consular_district
        ADD
          CONSTRAINT FK_BBFC552F80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BBFC552F80E32C3E ON geo_consular_district (geo_data_id)');
        $this->addSql('ALTER TABLE geo_city_community ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_city_community
        ADD
          CONSTRAINT FK_E5805E0880E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E5805E0880E32C3E ON geo_city_community (geo_data_id)');
        $this->addSql('ALTER TABLE geo_department ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_department
        ADD
          CONSTRAINT FK_B460660480E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B460660480E32C3E ON geo_department (geo_data_id)');
        $this->addSql('ALTER TABLE geo_custom_zone ADD geo_data_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_custom_zone
        ADD
          CONSTRAINT FK_ABE4DB5A80E32C3E FOREIGN KEY (geo_data_id) REFERENCES geo_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ABE4DB5A80E32C3E ON geo_custom_zone (geo_data_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_borough DROP FOREIGN KEY FK_1449587480E32C3E');
        $this->addSql('DROP INDEX UNIQ_1449587480E32C3E ON geo_borough');
        $this->addSql('ALTER TABLE geo_borough DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_canton DROP FOREIGN KEY FK_F04FC05F80E32C3E');
        $this->addSql('DROP INDEX UNIQ_F04FC05F80E32C3E ON geo_canton');
        $this->addSql('ALTER TABLE geo_canton DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_city DROP FOREIGN KEY FK_297C2D3480E32C3E');
        $this->addSql('DROP INDEX UNIQ_297C2D3480E32C3E ON geo_city');
        $this->addSql('ALTER TABLE geo_city DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_city_community DROP FOREIGN KEY FK_E5805E0880E32C3E');
        $this->addSql('DROP INDEX UNIQ_E5805E0880E32C3E ON geo_city_community');
        $this->addSql('ALTER TABLE geo_city_community DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_consular_district DROP FOREIGN KEY FK_BBFC552F80E32C3E');
        $this->addSql('DROP INDEX UNIQ_BBFC552F80E32C3E ON geo_consular_district');
        $this->addSql('ALTER TABLE geo_consular_district DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_country DROP FOREIGN KEY FK_E465446480E32C3E');
        $this->addSql('DROP INDEX UNIQ_E465446480E32C3E ON geo_country');
        $this->addSql('ALTER TABLE geo_country DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_custom_zone DROP FOREIGN KEY FK_ABE4DB5A80E32C3E');
        $this->addSql('DROP INDEX UNIQ_ABE4DB5A80E32C3E ON geo_custom_zone');
        $this->addSql('ALTER TABLE geo_custom_zone DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_department DROP FOREIGN KEY FK_B460660480E32C3E');
        $this->addSql('DROP INDEX UNIQ_B460660480E32C3E ON geo_department');
        $this->addSql('ALTER TABLE geo_department DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_district DROP FOREIGN KEY FK_DF78232680E32C3E');
        $this->addSql('DROP INDEX UNIQ_DF78232680E32C3E ON geo_district');
        $this->addSql('ALTER TABLE geo_district DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_foreign_district DROP FOREIGN KEY FK_973BE1F180E32C3E');
        $this->addSql('DROP INDEX UNIQ_973BE1F180E32C3E ON geo_foreign_district');
        $this->addSql('ALTER TABLE geo_foreign_district DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_region DROP FOREIGN KEY FK_A4B3C80880E32C3E');
        $this->addSql('DROP INDEX UNIQ_A4B3C80880E32C3E ON geo_region');
        $this->addSql('ALTER TABLE geo_region DROP geo_data_id');
        $this->addSql('ALTER TABLE geo_zone DROP FOREIGN KEY FK_A4CCEF0780E32C3E');
        $this->addSql('DROP INDEX UNIQ_A4CCEF0780E32C3E ON geo_zone');
        $this->addSql('ALTER TABLE geo_zone DROP geo_data_id');
    }
}
