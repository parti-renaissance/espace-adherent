<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200814180922 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE political_committee_quality (id INT UNSIGNED AUTO_INCREMENT NOT NULL, political_committee_membership_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, joined_at DATE NOT NULL, INDEX IDX_243D6D3A78632915 (political_committee_membership_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE political_committee_membership (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED NOT NULL, political_committee_id INT UNSIGNED NOT NULL, joined_at DATE NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_FD85437B25F06C53 (adherent_id), INDEX IDX_FD85437BC7A72 (political_committee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE political_committee (id INT UNSIGNED AUTO_INCREMENT NOT NULL, territorial_council_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_39FAEE955E237E06 (name), UNIQUE INDEX UNIQ_39FAEE95AAA61A99 (territorial_council_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE political_committee_quality ADD CONSTRAINT FK_243D6D3A78632915 FOREIGN KEY (political_committee_membership_id) REFERENCES political_committee_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE political_committee_membership ADD CONSTRAINT FK_FD85437B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE political_committee_membership ADD CONSTRAINT FK_FD85437BC7A72 FOREIGN KEY (political_committee_id) REFERENCES political_committee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE political_committee ADD CONSTRAINT FK_39FAEE95AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE political_committee_quality DROP FOREIGN KEY FK_243D6D3A78632915');
        $this->addSql('ALTER TABLE political_committee_membership DROP FOREIGN KEY FK_FD85437BC7A72');
        $this->addSql('DROP TABLE political_committee_quality');
        $this->addSql('DROP TABLE political_committee_membership');
        $this->addSql('DROP TABLE political_committee');
    }
}
