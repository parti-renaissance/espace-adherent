<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191008141228 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE donator_donator_tag (donator_id INT UNSIGNED NOT NULL, donator_tag_id INT UNSIGNED NOT NULL, INDEX IDX_6BAEC28C831BACAF (donator_id), INDEX IDX_6BAEC28C71F026E6 (donator_tag_id), PRIMARY KEY(donator_id, donator_tag_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE donation_donation_tag (donation_id INT UNSIGNED NOT NULL, donation_tag_id INT UNSIGNED NOT NULL, INDEX IDX_F2D7087F4DC1279C (donation_id), INDEX IDX_F2D7087F790547EA (donation_tag_id), PRIMARY KEY(donation_id, donation_tag_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE donator_donator_tag ADD CONSTRAINT FK_6BAEC28C831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE donator_donator_tag ADD CONSTRAINT FK_6BAEC28C71F026E6 FOREIGN KEY (donator_tag_id) REFERENCES donator_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE donation_donation_tag ADD CONSTRAINT FK_F2D7087F4DC1279C FOREIGN KEY (donation_id) REFERENCES donations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE donation_donation_tag ADD CONSTRAINT FK_F2D7087F790547EA FOREIGN KEY (donation_tag_id) REFERENCES donation_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE donator_donator_tag');
        $this->addSql('DROP TABLE donation_donation_tag');
    }
}
