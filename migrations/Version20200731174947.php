<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200731174947 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, codes VARCHAR(50) NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX territorial_council_uuid_unique (uuid), UNIQUE INDEX territorial_council_name_unique (name), UNIQUE INDEX territorial_council_codes_unique (codes), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territorial_council_referent_tag (territorial_council_id INT UNSIGNED NOT NULL, referent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_78DBEB90AAA61A99 (territorial_council_id), INDEX IDX_78DBEB909C262DB3 (referent_tag_id), PRIMARY KEY(territorial_council_id, referent_tag_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territorial_council_membership (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED NOT NULL, territorial_council_id INT UNSIGNED NOT NULL, qualities LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', joined_at DATE NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_2A99831625F06C53 (adherent_id), INDEX IDX_2A998316AAA61A99 (territorial_council_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territorial_council_referent_tag ADD CONSTRAINT FK_78DBEB90AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territorial_council_referent_tag ADD CONSTRAINT FK_78DBEB909C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territorial_council_membership ADD CONSTRAINT FK_2A99831625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territorial_council_membership ADD CONSTRAINT FK_2A998316AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_referent_tag DROP FOREIGN KEY FK_78DBEB90AAA61A99');
        $this->addSql('ALTER TABLE territorial_council_membership DROP FOREIGN KEY FK_2A998316AAA61A99');
        $this->addSql('DROP TABLE territorial_council');
        $this->addSql('DROP TABLE territorial_council_referent_tag');
        $this->addSql('DROP TABLE territorial_council_membership');
    }
}
