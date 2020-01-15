<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200114234059 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE donator_kinship (id INT UNSIGNED AUTO_INCREMENT NOT NULL, donator_id INT UNSIGNED NOT NULL, related_id INT UNSIGNED NOT NULL, kinship VARCHAR(100) NOT NULL, INDEX IDX_E542211D831BACAF (donator_id), INDEX IDX_E542211D4162C001 (related_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE donator_kinship ADD CONSTRAINT FK_E542211D831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE donator_kinship ADD CONSTRAINT FK_E542211D4162C001 FOREIGN KEY (related_id) REFERENCES donators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE donations ADD donated_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE donations SET donated_at = created_at');
        $this->addSql('ALTER TABLE donations CHANGE donated_at donated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE donation_transactions DROP FOREIGN KEY FK_723705D14DC1279C');
        $this->addSql('ALTER TABLE donation_transactions CHANGE donation_id donation_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE donation_transactions ADD CONSTRAINT FK_89D6D36B4DC1279C FOREIGN KEY (donation_id) REFERENCES donations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE donations ADD last_success_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP last_success_date');
        $this->addSql('DROP TABLE donator_kinship');
        $this->addSql('ALTER TABLE donation_transactions DROP FOREIGN KEY FK_89D6D36B4DC1279C');
        $this->addSql('ALTER TABLE donation_transactions CHANGE donation_id donation_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE donation_transactions ADD CONSTRAINT FK_723705D14DC1279C FOREIGN KEY (donation_id) REFERENCES donations (id)');
        $this->addSql('ALTER TABLE donations DROP donated_at');
    }
}
