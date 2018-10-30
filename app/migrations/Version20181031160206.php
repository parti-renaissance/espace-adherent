<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181031160206 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mailer_template_versions (id INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', template_id INT UNSIGNED NOT NULL COMMENT \'(DC2Type:integer)\', uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', body LONGTEXT NOT NULL COMMENT \'(DC2Type:text)\', subject LONGTEXT NOT NULL COMMENT \'(DC2Type:text)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_80E1EEBC5DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mailer_mail_requests (id INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', vars_id INT UNSIGNED NOT NULL COMMENT \'(DC2Type:integer)\', campaign CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', request_payload JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', response_payload JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E6A08DE7B2E4466F (vars_id), INDEX chunk_campaign_idx (campaign), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mailer_recipient_vars (id INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', address_id INT UNSIGNED NOT NULL COMMENT \'(DC2Type:integer)\', mail_request_id INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:integer)\', template_vars JSON NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_5BC905CEF5B7AF75 (address_id), INDEX IDX_5BC905CE96BD5259 (mail_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mailer_addresses (id INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', email VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', canonical_email VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX unique_email_name (canonical_email, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mailer_mail_vars (id INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', reply_to_id INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:integer)\', app VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', type VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', template_name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', template_vars JSON NOT NULL COMMENT \'(DC2Type:json_array)\', campaign CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sender_name VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', sender_email VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', subject VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX UNIQ_766519DD1F1512DD (campaign), INDEX IDX_766519DDFFDF7169 (reply_to_id), INDEX app_idx (app), INDEX type_idx (type), INDEX mail_campaign_idx (campaign), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mailer_mails_cc (mail_vars_id INT UNSIGNED NOT NULL COMMENT \'(DC2Type:integer)\', address_id INT UNSIGNED NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_33CC128C6859C700 (mail_vars_id), INDEX IDX_33CC128CF5B7AF75 (address_id), PRIMARY KEY(mail_vars_id, address_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mailer_mails_bcc (mail_vars_id INT UNSIGNED NOT NULL COMMENT \'(DC2Type:integer)\', address_id INT UNSIGNED NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_E346B6416859C700 (mail_vars_id), INDEX IDX_E346B641F5B7AF75 (address_id), PRIMARY KEY(mail_vars_id, address_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mailer_templates (id INT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', last_version_id INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:integer)\', app_name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', mail_class VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', mail_type VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', UNIQUE INDEX UNIQ_46364D83A2C84DEF (last_version_id), INDEX IDX_46364D835B0D5BA6D988BBF0 (app_name, mail_class), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mailer_template_versions ADD CONSTRAINT FK_80E1EEBC5DA0FB8 FOREIGN KEY (template_id) REFERENCES mailer_templates (id)');
        $this->addSql('ALTER TABLE mailer_mail_requests ADD CONSTRAINT FK_E6A08DE7B2E4466F FOREIGN KEY (vars_id) REFERENCES mailer_mail_vars (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mailer_recipient_vars ADD CONSTRAINT FK_5BC905CEF5B7AF75 FOREIGN KEY (address_id) REFERENCES mailer_addresses (id)');
        $this->addSql('ALTER TABLE mailer_recipient_vars ADD CONSTRAINT FK_5BC905CE96BD5259 FOREIGN KEY (mail_request_id) REFERENCES mailer_mail_requests (id)');
        $this->addSql('ALTER TABLE mailer_mail_vars ADD CONSTRAINT FK_766519DDFFDF7169 FOREIGN KEY (reply_to_id) REFERENCES mailer_addresses (id)');
        $this->addSql('ALTER TABLE mailer_mails_cc ADD CONSTRAINT FK_33CC128C6859C700 FOREIGN KEY (mail_vars_id) REFERENCES mailer_mail_vars (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mailer_mails_cc ADD CONSTRAINT FK_33CC128CF5B7AF75 FOREIGN KEY (address_id) REFERENCES mailer_addresses (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mailer_mails_bcc ADD CONSTRAINT FK_E346B6416859C700 FOREIGN KEY (mail_vars_id) REFERENCES mailer_mail_vars (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mailer_mails_bcc ADD CONSTRAINT FK_E346B641F5B7AF75 FOREIGN KEY (address_id) REFERENCES mailer_addresses (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mailer_templates ADD CONSTRAINT FK_46364D83A2C84DEF FOREIGN KEY (last_version_id) REFERENCES mailer_template_versions (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mailer_templates DROP FOREIGN KEY FK_46364D83A2C84DEF');
        $this->addSql('ALTER TABLE mailer_recipient_vars DROP FOREIGN KEY FK_5BC905CE96BD5259');
        $this->addSql('ALTER TABLE mailer_recipient_vars DROP FOREIGN KEY FK_5BC905CEF5B7AF75');
        $this->addSql('ALTER TABLE mailer_mail_vars DROP FOREIGN KEY FK_766519DDFFDF7169');
        $this->addSql('ALTER TABLE mailer_mails_cc DROP FOREIGN KEY FK_33CC128CF5B7AF75');
        $this->addSql('ALTER TABLE mailer_mails_bcc DROP FOREIGN KEY FK_E346B641F5B7AF75');
        $this->addSql('ALTER TABLE mailer_mail_requests DROP FOREIGN KEY FK_E6A08DE7B2E4466F');
        $this->addSql('ALTER TABLE mailer_mails_cc DROP FOREIGN KEY FK_33CC128C6859C700');
        $this->addSql('ALTER TABLE mailer_mails_bcc DROP FOREIGN KEY FK_E346B6416859C700');
        $this->addSql('ALTER TABLE mailer_template_versions DROP FOREIGN KEY FK_80E1EEBC5DA0FB8');
        $this->addSql('DROP TABLE mailer_template_versions');
        $this->addSql('DROP TABLE mailer_mail_requests');
        $this->addSql('DROP TABLE mailer_recipient_vars');
        $this->addSql('DROP TABLE mailer_addresses');
        $this->addSql('DROP TABLE mailer_mail_vars');
        $this->addSql('DROP TABLE mailer_mails_cc');
        $this->addSql('DROP TABLE mailer_mails_bcc');
        $this->addSql('DROP TABLE mailer_templates');
    }
}
