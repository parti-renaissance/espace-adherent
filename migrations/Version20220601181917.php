<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220601181917 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assessor_requests DROP FOREIGN KEY FK_26BC800F3F90B30');
        $this->addSql('ALTER TABLE assessor_requests CHANGE vote_place_id vote_place_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          assessor_requests
        ADD
          CONSTRAINT FK_26BC800F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES election_vote_place (id)');
        $this->addSql('ALTER TABLE assessor_requests_vote_place_wishes DROP FOREIGN KEY FK_1517FC13F3F90B30');
        $this->addSql('ALTER TABLE
          assessor_requests_vote_place_wishes
        CHANGE
          vote_place_id vote_place_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          assessor_requests_vote_place_wishes
        ADD
          CONSTRAINT FK_1517FC13F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES election_vote_place (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE assessor_role_association DROP FOREIGN KEY FK_B93395C2F3F90B30');
        $this->addSql('ALTER TABLE
          assessor_role_association
        CHANGE
          vote_place_id vote_place_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          assessor_role_association
        ADD
          CONSTRAINT FK_B93395C2F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES election_vote_place (id)');
        $this->addSql('ALTER TABLE
          election_vote_place
        CHANGE
          address_postal_code address_postal_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349F3F90B30');
        $this->addSql('ALTER TABLE vote_result CHANGE vote_place_id vote_place_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES election_vote_place (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assessor_requests DROP FOREIGN KEY FK_26BC800F3F90B30');
        $this->addSql('ALTER TABLE assessor_requests CHANGE vote_place_id vote_place_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          assessor_requests
        ADD
          CONSTRAINT FK_26BC800F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE assessor_requests_vote_place_wishes DROP FOREIGN KEY FK_1517FC13F3F90B30');
        $this->addSql('ALTER TABLE
          assessor_requests_vote_place_wishes
        CHANGE
          vote_place_id vote_place_id INT NOT NULL');
        $this->addSql('ALTER TABLE
          assessor_requests_vote_place_wishes
        ADD
          CONSTRAINT FK_1517FC13F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE assessor_role_association DROP FOREIGN KEY FK_B93395C2F3F90B30');
        $this->addSql('ALTER TABLE assessor_role_association CHANGE vote_place_id vote_place_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          assessor_role_association
        ADD
          CONSTRAINT FK_B93395C2F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          election_vote_place
        CHANGE
          address_postal_code address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349F3F90B30');
        $this->addSql('ALTER TABLE vote_result CHANGE vote_place_id vote_place_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          vote_result
        ADD
          CONSTRAINT FK_1F8DB349F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
