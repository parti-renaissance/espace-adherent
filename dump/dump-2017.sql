-- Database: enmarche
-- Server version	5.7.21

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_subscriptions`
--

DROP TABLE IF EXISTS `activity_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `following_adherent_id` int(10) unsigned DEFAULT NULL,
  `followed_adherent_id` int(10) unsigned DEFAULT NULL,
  `subscribed_at` datetime NOT NULL,
  `unsubscribed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `activity_subscriptions_unique` (`followed_adherent_id`,`following_adherent_id`),
  KEY `IDX_5A543C56016700F` (`following_adherent_id`),
  KEY `IDX_5A543C57D7402F7` (`followed_adherent_id`),
  CONSTRAINT `FK_5A543C56016700F` FOREIGN KEY (`following_adherent_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_5A543C57D7402F7` FOREIGN KEY (`followed_adherent_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_subscriptions`
--

LOCK TABLES `activity_subscriptions` WRITE;
/*!40000 ALTER TABLE `activity_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_activation_keys`
--

DROP TABLE IF EXISTS `adherent_activation_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adherent_activation_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adherent_uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `value` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `expired_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_activation_token_unique` (`value`),
  UNIQUE KEY `adherent_activation_token_account_unique` (`value`,`adherent_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_activation_keys`
--

LOCK TABLES `adherent_activation_keys` WRITE;
/*!40000 ALTER TABLE `adherent_activation_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_activation_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_adherent_tag`
--

DROP TABLE IF EXISTS `adherent_adherent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adherent_adherent_tag` (
  `adherent_id` int(10) unsigned NOT NULL,
  `adherent_tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`adherent_id`,`adherent_tag_id`),
  KEY `IDX_DD297F8225F06C53` (`adherent_id`),
  KEY `IDX_DD297F82AED03543` (`adherent_tag_id`),
  CONSTRAINT `FK_DD297F8225F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DD297F82AED03543` FOREIGN KEY (`adherent_tag_id`) REFERENCES `adherent_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_adherent_tag`
--

LOCK TABLES `adherent_adherent_tag` WRITE;
/*!40000 ALTER TABLE `adherent_adherent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_adherent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_reset_password_tokens`
--

DROP TABLE IF EXISTS `adherent_reset_password_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adherent_reset_password_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adherent_uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `value` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `expired_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_reset_password_token_unique` (`value`),
  UNIQUE KEY `adherent_reset_password_token_account_unique` (`value`,`adherent_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_reset_password_tokens`
--

LOCK TABLES `adherent_reset_password_tokens` WRITE;
/*!40000 ALTER TABLE `adherent_reset_password_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_reset_password_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_tags`
--

DROP TABLE IF EXISTS `adherent_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adherent_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_tag_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_tags`
--

LOCK TABLES `adherent_tags` WRITE;
/*!40000 ALTER TABLE `adherent_tags` DISABLE KEYS */;
INSERT INTO `adherent_tags` VALUES (3,'Actif'),(1,'Élu'),(5,'Médiation'),(4,'Peu actif'),(6,'Suppléant'),(2,'Très actif');
/*!40000 ALTER TABLE `adherent_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherents`
--

DROP TABLE IF EXISTS `adherents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adherents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `old_password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `email_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `birthdate` date DEFAULT NULL,
  `position` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'DISABLED',
  `registered_at` datetime NOT NULL,
  `activated_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `last_logged_at` datetime DEFAULT NULL,
  `interests` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `main_emails_subscription` tinyint(1) NOT NULL,
  `referents_emails_subscription` tinyint(1) NOT NULL,
  `local_host_emails_subscription` tinyint(1) NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `managed_area_codes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
  `managed_area_marker_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `managed_area_marker_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_address` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `legislative_candidate` tinyint(1) NOT NULL DEFAULT '0',
  `com_mobile` tinyint(1) DEFAULT NULL,
  `com_email` tinyint(1) DEFAULT NULL,
  `citizen_project_creation_email_subscription_radius` int(11) NOT NULL DEFAULT '10',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherents_uuid_unique` (`uuid`),
  UNIQUE KEY `adherents_email_address_unique` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherents`
--

LOCK TABLES `adherents` WRITE;
/*!40000 ALTER TABLE `adherents` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `administrators`
--

DROP TABLE IF EXISTS `administrators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administrators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `google_authenticator_secret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `administrators_email_address_unique` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrators`
--

LOCK TABLES `administrators` WRITE;
/*!40000 ALTER TABLE `administrators` DISABLE KEYS */;
/*!40000 ALTER TABLE `administrators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article_proposal_theme`
--

DROP TABLE IF EXISTS `article_proposal_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_proposal_theme` (
  `article_id` bigint(20) NOT NULL,
  `proposal_theme_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`proposal_theme_id`),
  KEY `IDX_F6B9A2217294869C` (`article_id`),
  KEY `IDX_F6B9A221B85948AF` (`proposal_theme_id`),
  CONSTRAINT `FK_F6B9A2217294869C` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F6B9A221B85948AF` FOREIGN KEY (`proposal_theme_id`) REFERENCES `proposals_themes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_proposal_theme`
--

LOCK TABLES `article_proposal_theme` WRITE;
/*!40000 ALTER TABLE `article_proposal_theme` DISABLE KEYS */;
/*!40000 ALTER TABLE `article_proposal_theme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `media_id` bigint(20) DEFAULT NULL,
  `published_at` datetime NOT NULL,
  `published` tinyint(1) NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amp_content` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BFDD3168989D9B62` (`slug`),
  KEY `IDX_BFDD316812469DE2` (`category_id`),
  KEY `IDX_BFDD3168EA9FDD75` (`media_id`),
  CONSTRAINT `FK_BFDD316812469DE2` FOREIGN KEY (`category_id`) REFERENCES `articles_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BFDD3168EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles_categories`
--

DROP TABLE IF EXISTS `articles_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` smallint(6) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DE004A0E989D9B62` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles_categories`
--

LOCK TABLES `articles_categories` WRITE;
/*!40000 ALTER TABLE `articles_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `articles_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `board_member`
--

DROP TABLE IF EXISTS `board_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `board_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adherent_id` int(10) unsigned DEFAULT NULL,
  `area` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DCFABEDF25F06C53` (`adherent_id`),
  CONSTRAINT `FK_DCFABEDF25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `board_member`
--

LOCK TABLES `board_member` WRITE;
/*!40000 ALTER TABLE `board_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `board_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `board_member_roles`
--

DROP TABLE IF EXISTS `board_member_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `board_member_roles` (
  `board_member_id` int(11) NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`board_member_id`,`role_id`),
  KEY `IDX_1DD1E043C7BA2FD5` (`board_member_id`),
  KEY `IDX_1DD1E043D60322AC` (`role_id`),
  CONSTRAINT `FK_1DD1E043C7BA2FD5` FOREIGN KEY (`board_member_id`) REFERENCES `board_member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1DD1E043D60322AC` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `board_member_roles`
--

LOCK TABLES `board_member_roles` WRITE;
/*!40000 ALTER TABLE `board_member_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `board_member_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_action_categories`
--

DROP TABLE IF EXISTS `citizen_action_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_action_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ENABLED',
  PRIMARY KEY (`id`),
  UNIQUE KEY `citizen_action_category_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_action_categories`
--

LOCK TABLES `citizen_action_categories` WRITE;
/*!40000 ALTER TABLE `citizen_action_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_action_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_initiative_categories`
--

DROP TABLE IF EXISTS `citizen_initiative_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_initiative_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ENABLED',
  PRIMARY KEY (`id`),
  UNIQUE KEY `citizen_initiative_category_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_initiative_categories`
--

LOCK TABLES `citizen_initiative_categories` WRITE;
/*!40000 ALTER TABLE `citizen_initiative_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_initiative_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_initiative_skills`
--

DROP TABLE IF EXISTS `citizen_initiative_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_initiative_skills` (
  `citizen_initiative_id` int(10) unsigned NOT NULL,
  `skill_id` int(11) NOT NULL,
  PRIMARY KEY (`citizen_initiative_id`,`skill_id`),
  KEY `IDX_F936A5506FBEFC74` (`citizen_initiative_id`),
  KEY `IDX_F936A5505585C142` (`skill_id`),
  CONSTRAINT `FK_F936A5505585C142` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F936A5506FBEFC74` FOREIGN KEY (`citizen_initiative_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_initiative_skills`
--

LOCK TABLES `citizen_initiative_skills` WRITE;
/*!40000 ALTER TABLE `citizen_initiative_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_initiative_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_project_categories`
--

DROP TABLE IF EXISTS `citizen_project_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_project_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ENABLED',
  PRIMARY KEY (`id`),
  UNIQUE KEY `citizen_project_category_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_project_categories`
--

LOCK TABLES `citizen_project_categories` WRITE;
/*!40000 ALTER TABLE `citizen_project_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_project_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_project_category_skills`
--

DROP TABLE IF EXISTS `citizen_project_category_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_project_category_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL,
  `skill_id` int(11) DEFAULT NULL,
  `promotion` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_168C868A12469DE2` (`category_id`),
  KEY `IDX_168C868A5585C142` (`skill_id`),
  CONSTRAINT `FK_168C868A12469DE2` FOREIGN KEY (`category_id`) REFERENCES `citizen_project_categories` (`id`),
  CONSTRAINT `FK_168C868A5585C142` FOREIGN KEY (`skill_id`) REFERENCES `citizen_project_skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_project_category_skills`
--

LOCK TABLES `citizen_project_category_skills` WRITE;
/*!40000 ALTER TABLE `citizen_project_category_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_project_category_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_project_comment`
--

DROP TABLE IF EXISTS `citizen_project_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_project_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `citizen_project_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned DEFAULT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  KEY `IDX_A57DD65FB3584533` (`citizen_project_id`),
  KEY `IDX_A57DD65FF675F31B` (`author_id`),
  CONSTRAINT `FK_A57DD65FB3584533` FOREIGN KEY (`citizen_project_id`) REFERENCES `citizen_projects` (`id`),
  CONSTRAINT `FK_A57DD65FF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_project_comment`
--

LOCK TABLES `citizen_project_comment` WRITE;
/*!40000 ALTER TABLE `citizen_project_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_project_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_project_committee_supports`
--

DROP TABLE IF EXISTS `citizen_project_committee_supports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_project_committee_supports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizen_project_id` int(10) unsigned DEFAULT NULL,
  `committee_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F694C3BCB3584533` (`citizen_project_id`),
  KEY `IDX_F694C3BCED1A100B` (`committee_id`),
  CONSTRAINT `FK_F694C3BCB3584533` FOREIGN KEY (`citizen_project_id`) REFERENCES `citizen_projects` (`id`),
  CONSTRAINT `FK_F694C3BCED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_project_committee_supports`
--

LOCK TABLES `citizen_project_committee_supports` WRITE;
/*!40000 ALTER TABLE `citizen_project_committee_supports` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_project_committee_supports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_project_memberships`
--

DROP TABLE IF EXISTS `citizen_project_memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_project_memberships` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int(10) unsigned DEFAULT NULL,
  `citizen_project_uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `privilege` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `joined_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_has_joined_citizen_project` (`adherent_id`,`citizen_project_uuid`),
  KEY `IDX_2E4181625F06C53` (`adherent_id`),
  KEY `citizen_project_memberships_role_idx` (`privilege`),
  CONSTRAINT `FK_2E4181625F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_project_memberships`
--

LOCK TABLES `citizen_project_memberships` WRITE;
/*!40000 ALTER TABLE `citizen_project_memberships` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_project_memberships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_project_skills`
--

DROP TABLE IF EXISTS `citizen_project_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_project_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `citizen_project_skill_slug_unique` (`slug`),
  UNIQUE KEY `citizen_project_skill_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_project_skills`
--

LOCK TABLES `citizen_project_skills` WRITE;
/*!40000 ALTER TABLE `citizen_project_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_project_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_projects`
--

DROP TABLE IF EXISTS `citizen_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `canonical_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `approved_at` datetime DEFAULT NULL,
  `refused_at` datetime DEFAULT NULL,
  `created_by` char(36) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `phone` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `members_counts` smallint(5) unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `coordinator_comment` longtext COLLATE utf8_unicode_ci,
  `category_id` int(10) unsigned DEFAULT NULL,
  `subtitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `problem_description` longtext COLLATE utf8_unicode_ci,
  `proposed_solution` longtext COLLATE utf8_unicode_ci,
  `required_means` longtext COLLATE utf8_unicode_ci,
  `assistance_needed` tinyint(1) NOT NULL DEFAULT '0',
  `assistance_content` longtext COLLATE utf8_unicode_ci,
  `image_uploaded` tinyint(1) NOT NULL DEFAULT '0',
  `matched_skills` tinyint(1) NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `admin_comment` longtext COLLATE utf8_unicode_ci,
  `image_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `citizen_project_uuid_unique` (`uuid`),
  UNIQUE KEY `citizen_project_canonical_name_unique` (`canonical_name`),
  UNIQUE KEY `citizen_project_slug_unique` (`slug`),
  KEY `citizen_project_status_idx` (`status`),
  KEY `IDX_651490212469DE2` (`category_id`),
  CONSTRAINT `FK_651490212469DE2` FOREIGN KEY (`category_id`) REFERENCES `citizen_project_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_projects`
--

LOCK TABLES `citizen_projects` WRITE;
/*!40000 ALTER TABLE `citizen_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_projects_skills`
--

DROP TABLE IF EXISTS `citizen_projects_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_projects_skills` (
  `citizen_project_id` int(10) unsigned NOT NULL,
  `citizen_project_skill_id` int(11) NOT NULL,
  PRIMARY KEY (`citizen_project_id`,`citizen_project_skill_id`),
  KEY `IDX_B3D202D9B3584533` (`citizen_project_id`),
  KEY `IDX_B3D202D9EA64A9D0` (`citizen_project_skill_id`),
  CONSTRAINT `FK_B3D202D9B3584533` FOREIGN KEY (`citizen_project_id`) REFERENCES `citizen_projects` (`id`),
  CONSTRAINT `FK_B3D202D9EA64A9D0` FOREIGN KEY (`citizen_project_skill_id`) REFERENCES `citizen_project_skills` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_projects_skills`
--

LOCK TABLES `citizen_projects_skills` WRITE;
/*!40000 ALTER TABLE `citizen_projects_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_projects_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clarifications`
--

DROP TABLE IF EXISTS `clarifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clarifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `media_id` bigint(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amp_content` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2FAB8972989D9B62` (`slug`),
  KEY `IDX_2FAB8972EA9FDD75` (`media_id`),
  CONSTRAINT `FK_2FAB8972EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clarifications`
--

LOCK TABLES `clarifications` WRITE;
/*!40000 ALTER TABLE `clarifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `clarifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_feed_item`
--

DROP TABLE IF EXISTS `committee_feed_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `committee_feed_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `committee_id` int(10) unsigned DEFAULT NULL,
  `author_id` int(10) unsigned DEFAULT NULL,
  `event_id` int(10) unsigned DEFAULT NULL,
  `item_type` varchar(18) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `IDX_4F1CDC80ED1A100B` (`committee_id`),
  KEY `IDX_4F1CDC80F675F31B` (`author_id`),
  KEY `IDX_4F1CDC8071F7E88B` (`event_id`),
  CONSTRAINT `FK_4F1CDC8071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4F1CDC80ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`),
  CONSTRAINT `FK_4F1CDC80F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_feed_item`
--

LOCK TABLES `committee_feed_item` WRITE;
/*!40000 ALTER TABLE `committee_feed_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_feed_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committees`
--

DROP TABLE IF EXISTS `committees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `committees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `canonical_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `facebook_page_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter_nickname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_plus_page_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `approved_at` datetime DEFAULT NULL,
  `refused_at` datetime DEFAULT NULL,
  `created_by` char(36) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `members_counts` smallint(5) unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `admin_comment` longtext COLLATE utf8_unicode_ci,
  `phone` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `coordinator_comment` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `committee_uuid_unique` (`uuid`),
  UNIQUE KEY `committee_canonical_name_unique` (`canonical_name`),
  UNIQUE KEY `committee_slug_unique` (`slug`),
  KEY `committee_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committees`
--

LOCK TABLES `committees` WRITE;
/*!40000 ALTER TABLE `committees` DISABLE KEYS */;
/*!40000 ALTER TABLE `committees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committees_memberships`
--

DROP TABLE IF EXISTS `committees_memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `committees_memberships` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int(10) unsigned DEFAULT NULL,
  `committee_uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `privilege` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `joined_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_has_joined_committee` (`adherent_id`,`committee_uuid`),
  KEY `IDX_E7A6490E25F06C53` (`adherent_id`),
  KEY `committees_memberships_role_idx` (`privilege`),
  CONSTRAINT `FK_E7A6490E25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committees_memberships`
--

LOCK TABLES `committees_memberships` WRITE;
/*!40000 ALTER TABLE `committees_memberships` DISABLE KEYS */;
/*!40000 ALTER TABLE `committees_memberships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coordinator_managed_areas`
--

DROP TABLE IF EXISTS `coordinator_managed_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coordinator_managed_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adherent_id` int(10) unsigned DEFAULT NULL,
  `codes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
  `sector` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C20973D25F06C53` (`adherent_id`),
  CONSTRAINT `FK_C20973D25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coordinator_managed_areas`
--

LOCK TABLES `coordinator_managed_areas` WRITE;
/*!40000 ALTER TABLE `coordinator_managed_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `coordinator_managed_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_search_results`
--

DROP TABLE IF EXISTS `custom_search_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_search_results` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `media_id` bigint(20) DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_38973E54EA9FDD75` (`media_id`),
  CONSTRAINT `FK_38973E54EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_search_results`
--

LOCK TABLES `custom_search_results` WRITE;
/*!40000 ALTER TABLE `custom_search_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_search_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donations`
--

DROP TABLE IF EXISTS `donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `amount` int(11) NOT NULL,
  `gender` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `email_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `paybox_result_code` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paybox_authorization_code` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paybox_payload` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `finished` tinyint(1) NOT NULL,
  `client_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `donated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `address_address` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `duration` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donations`
--

LOCK TABLES `donations` WRITE;
/*!40000 ALTER TABLE `donations` DISABLE KEYS */;
/*!40000 ALTER TABLE `donations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `message_class` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sender` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `recipients` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
  `request_payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `response_payload` longtext COLLATE utf8_unicode_ci,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails`
--

LOCK TABLES `emails` WRITE;
/*!40000 ALTER TABLE `emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organizer_id` int(10) unsigned DEFAULT NULL,
  `committee_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `canonical_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(130) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `capacity` int(11) DEFAULT NULL,
  `begin_at` datetime NOT NULL,
  `finish_at` datetime NOT NULL,
  `participants_count` smallint(5) unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `is_for_legislatives` tinyint(1) DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `interests` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `expert_assistance_needed` tinyint(1) DEFAULT '0',
  `coaching_requested` tinyint(1) DEFAULT '0',
  `coaching_request_problem_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_request_proposed_solution` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_request_required_means` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `citizen_initiative_category_id` int(10) unsigned DEFAULT NULL,
  `expert_found` tinyint(1) NOT NULL DEFAULT '0',
  `was_published` tinyint(1) DEFAULT '0',
  `place` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `citizen_project_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_uuid_unique` (`uuid`),
  UNIQUE KEY `event_slug_unique` (`slug`),
  KEY `IDX_5387574A876C4DDA` (`organizer_id`),
  KEY `IDX_5387574AED1A100B` (`committee_id`),
  KEY `IDX_5387574A12469DE2` (`category_id`),
  KEY `IDX_5387574AE03E2EB9` (`citizen_initiative_category_id`),
  KEY `IDX_5387574AB3584533` (`citizen_project_id`),
  CONSTRAINT `FK_5387574A876C4DDA` FOREIGN KEY (`organizer_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_5387574AB3584533` FOREIGN KEY (`citizen_project_id`) REFERENCES `citizen_projects` (`id`),
  CONSTRAINT `FK_5387574AE03E2EB9` FOREIGN KEY (`citizen_initiative_category_id`) REFERENCES `citizen_initiative_categories` (`id`),
  CONSTRAINT `FK_5387574AED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_categories`
--

DROP TABLE IF EXISTS `events_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ENABLED',
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_category_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_categories`
--

LOCK TABLES `events_categories` WRITE;
/*!40000 ALTER TABLE `events_categories` DISABLE KEYS */;
INSERT INTO `events_categories` VALUES (1,'Kiosque','ENABLED'),(2,'Réunion d\'équipe','ENABLED'),(3,'Conférence-débat','ENABLED'),(4,'Porte-à-porte','ENABLED'),(5,'Atelier du programme','ENABLED'),(6,'Tractage','ENABLED'),(7,'Convivialité','ENABLED'),(8,'Action ciblée','ENABLED'),(9,'Événement innovant','ENABLED'),(10,'Marche','ENABLED'),(11,'Support party','ENABLED');
/*!40000 ALTER TABLE `events_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_invitations`
--

DROP TABLE IF EXISTS `events_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `guests` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B94D5AAD71F7E88B` (`event_id`),
  CONSTRAINT `FK_B94D5AAD71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_invitations`
--

LOCK TABLES `events_invitations` WRITE;
/*!40000 ALTER TABLE `events_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `events_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_registrations`
--

DROP TABLE IF EXISTS `events_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_registrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `newsletter_subscriber` tinyint(1) NOT NULL,
  `adherent_uuid` char(36) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  KEY `IDX_EEFA30C071F7E88B` (`event_id`),
  CONSTRAINT `FK_EEFA30C071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_registrations`
--

LOCK TABLES `events_registrations` WRITE;
/*!40000 ALTER TABLE `events_registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `events_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facebook_profiles`
--

DROP TABLE IF EXISTS `facebook_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facebook_profiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `facebook_id` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `age_range` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `access_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `has_auto_uploaded` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facebook_profile_uuid` (`uuid`),
  UNIQUE KEY `facebook_profile_facebook_id` (`facebook_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facebook_profiles`
--

LOCK TABLES `facebook_profiles` WRITE;
/*!40000 ALTER TABLE `facebook_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `facebook_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facebook_videos`
--

DROP TABLE IF EXISTS `facebook_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facebook_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `facebook_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `twitter_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facebook_videos`
--

LOCK TABLES `facebook_videos` WRITE;
/*!40000 ALTER TABLE `facebook_videos` DISABLE KEYS */;
/*!40000 ALTER TABLE `facebook_videos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `home_blocks`
--

DROP TABLE IF EXISTS `home_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_blocks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `media_id` bigint(20) DEFAULT NULL,
  `position` smallint(6) NOT NULL,
  `position_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `subtitle` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `display_filter` tinyint(1) NOT NULL DEFAULT '1',
  `display_titles` tinyint(1) NOT NULL DEFAULT '0',
  `display_block` tinyint(1) NOT NULL DEFAULT '1',
  `title_cta` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color_cta` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bg_color` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video_controls` tinyint(1) NOT NULL DEFAULT '0',
  `video_autoplay_loop` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3EE9FCC5462CE4F5` (`position`),
  UNIQUE KEY `UNIQ_3EE9FCC54DBB5058` (`position_name`),
  KEY `IDX_3EE9FCC5EA9FDD75` (`media_id`),
  CONSTRAINT `FK_3EE9FCC5EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home_blocks`
--

LOCK TABLES `home_blocks` WRITE;
/*!40000 ALTER TABLE `home_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `home_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interactive_choices`
--

DROP TABLE IF EXISTS `interactive_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interactive_choices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `step` smallint(5) unsigned NOT NULL,
  `content_key` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `interactive_choices_uuid_unique` (`uuid`),
  UNIQUE KEY `interactive_choices_content_key_unique` (`content_key`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interactive_choices`
--

LOCK TABLES `interactive_choices` WRITE;
/*!40000 ALTER TABLE `interactive_choices` DISABLE KEYS */;
INSERT INTO `interactive_choices` VALUES (1,0,'S00C01','Introduction','%friend_first_name%, ces dernières semaines, nous avons beaucoup parlé du projet de loi de finances, le premier du quinquennat d\'Emmanuel Macron. Tu as peut-être lu ou entendu que c\'est un projet de transformation, qui prévoit de rénover notre fiscalité et d’investir massivement dans l’innovation, les compétences et la transition écologique. C\'est vrai. Tu as peut-être aussi entendu que cette transformation serait brutale et qu\'elle pénaliserait le pouvoir d\'achat. C\'est faux. Au contraire, ce sont des mesures en faveur de pouvoir d\'achat qui profiteront à tous. Voila Pourquoi.\n','28ceb6d3-ec64-4a58-99a4-71357600d07c','purchasing_power'),(2,0,'S00C02','Conclusion','La République En Marche a publié des documents plus précis à ce sujet (HYPERLIEN) et je reste pour ma part à ta disposition pour en reparler!','3d735d18-348c-4d02-8046-7976f86e5ecc','purchasing_power'),(3,0,'S00C03','Mesures communes','Comme 80% des Français, tu vas peut-être bénéficier de la supression progressive de ta taxe d\'habitation. Elle sera diminuée de 35% dès l\'année prochaine, puis de 60% en 2019 et enfin supprimée en 2020.\nPar ailleurs, tu as peut-être entendu parler d\'une réforme de la fiscalité des revenus du capital (intérêts, dividendes etc) qui vise à réduire et à simplifier notre système, très complexe, en créant un taux d\'imposition unique : 30%. Bon à savoir : les placements les plus populaires qui ont des régime très favorables (comme le Livret A) ne sont pas concernés, et tu pourras toujours conserver l\'actuel mode de calcul (CSG et impôt sur le revenu) si cela t\'es plus avantageux.\n','a642dbc7-aba5-49e4-877a-06bc1ef23168','purchasing_power'),(4,1,'S01C01','Salarié du secteur privé','Dès l\'année prochaine, ton salaire net va augmenter, grâce à la suppression de certaines charges sociales (cotisations chômage et maladie). Cette suppression sera totalement financée par une hausse limitée de la CSG, donc au global tu seras gagnant dans tous les cas (et nos finances publiques aussi!).\n','443b06fb-5cf2-4025-8732-84e407a420f8','purchasing_power'),(5,1,'S01C02','Salarié de la fonction publique','Tu n’as pas àt\'inquiéter de la hausse de la CSG, qui permet de redonner du pouvoir d\'achat aux salariés et indépendants du secteur privé : tu seras intégralement compensé. La contribution exceptionnelle de solidarité sera supprimée et une prime exceptionnelle te sera reversée.\n','aa832573-34c3-4f28-b069-9413ca5f865c','purchasing_power'),(6,1,'S01C03','Indépendant','Dès l\'année prochaine tes cotisations sociales vont diminuer, cette diminution sera au moins équivalente à la hausse de la CSG pour qu\'aucun indépendant ne voit son pouvoir d\'achat baisser. Aussi, à partir de 2018, le RSI sera progressivement supprimé pour que tu puisses avoir une meilleure qualité de service sans que tes règles de cotisations ne soient modifiées.\n','0421b645-71c0-4d2b-88ca-21969c0e1b8a','purchasing_power'),(7,1,'S01C04','Étudiant','Le coût de la sécurité sociale va baisser dès la rentrée prochaine. Tu seras désormais rattaché au régime général (et non plus étudiant), ce représentera un gain de pouvoir d’achat de 70 à 217€ par an, tout en conservant la même couverture médical. En parallèle, le coût de la vie étudiante (droits d\'inscription, le ticket du Resto U, les loyers du Crous) n’augmente pas cette année. Et si tu touches des bourses, elles seront simplifiées et versées à date fixe.\n','754f8ce5-3950-43d9-8f05-2ef8b8e91615','purchasing_power'),(8,1,'S01C05','Retraité modeste','Si tu fais partie des 40% de retraités modestes (ce qui correspond à peu près à une pension nette de 2200 euros net par mois pour un couple de retraité de plus de 65 ans), tu ne seras pas concerné par la hausse de la CSG. Si jamais tes revenus sont plus importants, ta CSG augmentera modestement, mais tu seras probablement compensé par la suppression progressive de la taxe d\'habitation.\n','79f7b99d-3795-4e43-b58c-c49d86d26804','purchasing_power'),(9,1,'S01C06','Retraité aisé','L\'année prochaine, si tu ne fais pas parti des retraités modestes (ce qui correspond à peu près à une pension nette de 2200 euros net par mois pour un couple de retraité de plus de 65 ans), tu constateras une hausse modérée de ta CSG. C\'est un effort de solidarité intergénérationnel qui permet de baisser les charges sociales de ceux qui travaillent aujourd\'hui, pour renforcer leur pouvoir d\'achat, qui n\'a que très peu augmenté ces dernières années.\n','0c6812bf-d1ac-472b-8879-0f59a0176e2f','purchasing_power'),(10,1,'S01C07','Ne travaille pas','','3b6fbde2-058a-4e15-90c5-2bfa7359db6e','purchasing_power'),(11,2,'S02C01','Il bénéficie peut-être de l\'allocation adulte handicapé','L\'Allocation Adulte Handicapé (AAH) va augmenter de 50€ fin 2018 et de nouveau de 40€ fin 2019. Elle atteindra alors 900 euros par mois.\n','52b738ad-c078-4952-bea5-caba65b688f6','purchasing_power'),(12,2,'S02C02','Il est en situation de précarité énergétique','Le chèque énergie sera généralisé pour aider 4 millions de ménages à payer leurs factures d’énergie et financer des travaux de rénovation. Il sera de 150 €/an dès 2018 et 200 €/an en moyenne en 2019. Aussi, si tu possèdes une vieille chaudière au fioul, tu pourras toucher jusqu\'à 3 000€ d\'aides pour la remplacer par une chaudière utilisant des énergies renouvelables.\n','642527cd-7427-41fa-959b-ab64ab50f0f5','purchasing_power'),(13,2,'S02C03','Il bénéficie peut-être du minimum vieillesse','Le minimum vieillesse va augmenter. Dès 2018, il sera revalorisé de 30 euros par mois. En 2020, il atteindra environ 900 euros par mois, soit une hausse de 100 euros.\n','1d5762da-220c-4a0f-8abc-d2f5b155748d','purchasing_power'),(14,2,'S02C04','Il s\'occupe seul d\'un enfant et bénéfice peut-être du complément mode de garde','Le complément mode de garde va augmenter. Cela représentera une hausse 30% dès octobre 2018 pour toutes les familles monoparentales qui en bénéficient.\n','04236bb1-7a00-481e-ab18-1900e8d3344c','purchasing_power'),(15,2,'S02C05','Il bénéficie de la prime d\'activité','La prime d’activité augmentera tous les ans. Le montant mensuel de la prime d’activité va augmenter chaque année de 20 euros pendant 4 ans. D’ici 2022, c’est donc 80 euros de plus chaque mois pour 2,5 millions de foyers.\n','0ebfffcc-1ea2-476c-bd8b-b7b32efe27cf','purchasing_power'),(16,2,'S02C06','Il souhaite changer une veille voiture','Une prime pour l’achat d’un véhicule moins polluant est créée. Elle s\'élevera à 1 000€ pour tout achat d\'un véhicule sobre en énergie (vignettes Crit’Air 0,1 ou 2), et la mise à la case d\'un vieux véhicule (avant 1997 pour une essence, avant 2001 pour un diesel). Si jamais tu ne paies pas d\'impôts sur le revenu cette année, la prime est portée à 2 000€, et tu peux même mettre à la casse un diesel immatriculé entre 2001 et 2006.\n','eeb85893-ef5c-4e7f-ad64-a4f640a0a7ad','purchasing_power'),(17,2,'S02C07','Il a besoin d\'aides chez lui ou chez un proche','Les aides à domicile vont coûter moins cher : désormais, 50% des dépenses d’aide à domicile seront remboursées quelque soit la situation de la personne bénéficiant de ces aides. Par exemple, avant cette réforme, les aides à domicile pour les personnes retraitées (payés par le retraité ou ses descendants) n\'étaient pris en charge que pour ceux qui paient l\'impôt sur le revenu.\n','9b75b13c-06c9-437e-bc88-726c19ca7050','purchasing_power'),(18,2,'S02C08','Il veut créer une entreprise','Bon à savoir pour ton projet entrepreneurial : dès 2019, les créateurs d\'une microentreprise aurant droit à une \"année blanche\" sur leurs cotisations sociales.\n','0612691b-d6c6-4ed8-8d35-fac7f00e7046','purchasing_power'),(19,3,'S03C01','Le travail','Nous sommes convaincus que le travail doit mieux payer pour tous les actifs. C\'est pourquoi la principale mesure de ce budget est la baisse des charges sociales. Nous voulons aussi inciter et récompenser ceux qui ont une activité partielle ou peu rémunérée. Pour cela, la prime d\'activité sera augmentée de 20€ par mois tous les ans pendant 4 ans. D\'ici 2022, ce seront donc 80€ par mois de pouvoir d\'achat supplémentaires.\n','7fc4e370-1b81-47de-93d9-7e001213ceb6','purchasing_power'),(20,3,'S03C02','La solidarité','La protection des publics fragiles est au cœur de ce projet de loi de finances. De nombreuses mesures visent à donner spécifiquement davantage de pouvoir d\'achat aux plus modestes, dans une ampleur inégalées depuis de nombreuses années. C\'est notamment le cas de la hausse du minimum vieillesse (+ 100 € par mois d\'ici 2020), de l\'allocation adulte handicapée (+ 90 € par mois d\'ici fin 2019), du complément mode de garde pour les familles monoparentales (+ 30%), ou de la généralisation du chèque énergie (150€ en 2018, 200€ en 2019) pour aider les ménages modestes à payer leurs factures d\'énergies et réaliser des travaux. Aussi, la baisse des charges sociales, financée par la hausse de la CSG, est une mesure de solidarité intergénérationnelle des retraités aisés en faveur de ceux qui travaillent aujourd\'hui.\n','b966e9c5-7afe-49fe-945b-780fb9439e47','purchasing_power'),(21,3,'S03C03','L\'écologie','Ce budget marque notre engagement en faveur de la transition écologique. Il y a certes une hausse de la fiscalité écologique, par exemple l\'augmentation du prix des carburants - notamment diesel, pour inciter les ménages à changer leur comportement. Mais il y a aussi des mesures d\'accompagnement, en particulier pour les plus modestes : prime pour l\'achat d\'une automobile récente simultanée à la casse d\'un vieux diesel (1000€ pour tous, 2000€ pour les ménages non imposables), prime immédiatement perceptible pour la réalisation de travaux chez soi, généralisation du chèque énergie (150€ en 2018 en 200€ en 2019), jusqu\'à 3 000€ d\'aides pour changer une chaudière, etc.\n','bcc44956-fa6c-4b32-b53f-0e843c42f2a4','purchasing_power'),(22,3,'S03C04','La responsabilité ','Le gouvernement va ramener les déficits en dessous de 3% du PIB dès 2017 et presque à 0 en 2022. C’est aussi pourquoi la CSG augmente pour financer la baisse des charges salariales, qui va rendre du pouvoir d\'achat aux actifs.\n','9d3972e4-bb24-4754-8909-5c15ec968279','purchasing_power'),(23,2,'S02C09','Aucune','','b61dbe63-7c26-4ad7-bd86-5d2f767e6d8b','purchasing_power');
/*!40000 ALTER TABLE `interactive_choices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interactive_invitation_has_choices`
--

DROP TABLE IF EXISTS `interactive_invitation_has_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interactive_invitation_has_choices` (
  `invitation_id` int(10) unsigned NOT NULL,
  `choice_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`invitation_id`,`choice_id`),
  KEY `IDX_31A811A2A35D7AF0` (`invitation_id`),
  KEY `IDX_31A811A2998666D1` (`choice_id`),
  CONSTRAINT `FK_31A811A2998666D1` FOREIGN KEY (`choice_id`) REFERENCES `interactive_choices` (`id`),
  CONSTRAINT `FK_31A811A2A35D7AF0` FOREIGN KEY (`invitation_id`) REFERENCES `interactive_invitations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interactive_invitation_has_choices`
--

LOCK TABLES `interactive_invitation_has_choices` WRITE;
/*!40000 ALTER TABLE `interactive_invitation_has_choices` DISABLE KEYS */;
/*!40000 ALTER TABLE `interactive_invitation_has_choices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interactive_invitations`
--

DROP TABLE IF EXISTS `interactive_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interactive_invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `friend_first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `friend_age` smallint(5) unsigned NOT NULL,
  `friend_gender` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `friend_position` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `friend_email_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_first_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_last_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_email_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail_subject` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail_body` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `interactive_invitations_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interactive_invitations`
--

LOCK TABLES `interactive_invitations` WRITE;
/*!40000 ALTER TABLE `interactive_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `interactive_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `client_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invitations`
--

LOCK TABLES `invitations` WRITE;
/*!40000 ALTER TABLE `invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `je_marche_reports`
--

DROP TABLE IF EXISTS `je_marche_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `je_marche_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `email_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `postal_code` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `convinced` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
  `almost_convinced` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
  `not_convinced` smallint(5) unsigned DEFAULT NULL,
  `reaction` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `je_marche_reports`
--

LOCK TABLES `je_marche_reports` WRITE;
/*!40000 ALTER TABLE `je_marche_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `je_marche_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legislative_candidates`
--

DROP TABLE IF EXISTS `legislative_candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `legislative_candidates` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `facebook_page_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `district_zone_id` smallint(5) unsigned DEFAULT NULL,
  `media_id` bigint(20) DEFAULT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `gender` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `email_address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter_page_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `donation_page_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `district_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `district_number` smallint(6) NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `career` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `geojson` longtext COLLATE utf8_unicode_ci,
  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  PRIMARY KEY (`id`),
  UNIQUE KEY `legislative_candidates_slug_unique` (`slug`),
  KEY `IDX_AE55AF9B23F5C396` (`district_zone_id`),
  KEY `IDX_AE55AF9BEA9FDD75` (`media_id`),
  CONSTRAINT `FK_AE55AF9B23F5C396` FOREIGN KEY (`district_zone_id`) REFERENCES `legislative_district_zones` (`id`),
  CONSTRAINT `FK_AE55AF9BEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legislative_candidates`
--

LOCK TABLES `legislative_candidates` WRITE;
/*!40000 ALTER TABLE `legislative_candidates` DISABLE KEYS */;
/*!40000 ALTER TABLE `legislative_candidates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legislative_district_zones`
--

DROP TABLE IF EXISTS `legislative_district_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `legislative_district_zones` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `area_code` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `area_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` longtext COLLATE utf8_unicode_ci NOT NULL,
  `rank` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `legislative_district_zones_area_code_unique` (`area_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legislative_district_zones`
--

LOCK TABLES `legislative_district_zones` WRITE;
/*!40000 ALTER TABLE `legislative_district_zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `legislative_district_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `live_links`
--

DROP TABLE IF EXISTS `live_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `live_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` smallint(6) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `live_links`
--

LOCK TABLES `live_links` WRITE;
/*!40000 ALTER TABLE `live_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `live_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medias`
--

DROP TABLE IF EXISTS `medias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medias` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `size` bigint(20) NOT NULL,
  `mime_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `compressed_display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_12D2AF81B548B0F` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medias`
--

LOCK TABLES `medias` WRITE;
/*!40000 ALTER TABLE `medias` DISABLE KEYS */;
/*!40000 ALTER TABLE `medias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_summary_job_experiences`
--

DROP TABLE IF EXISTS `member_summary_job_experiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_summary_job_experiences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `summary_id` int(11) DEFAULT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_facebook_page` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_twitter_nickname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `started_at` date NOT NULL,
  `ended_at` date DEFAULT NULL,
  `on_going` tinyint(1) NOT NULL DEFAULT '0',
  `contract` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `duration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `display_order` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `IDX_72DD8B7F2AC2D45C` (`summary_id`),
  CONSTRAINT `FK_72DD8B7F2AC2D45C` FOREIGN KEY (`summary_id`) REFERENCES `summaries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_summary_job_experiences`
--

LOCK TABLES `member_summary_job_experiences` WRITE;
/*!40000 ALTER TABLE `member_summary_job_experiences` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_summary_job_experiences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_summary_languages`
--

DROP TABLE IF EXISTS `member_summary_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_summary_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `summary_id` int(11) DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `level` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_70C88322AC2D45C` (`summary_id`),
  CONSTRAINT `FK_70C88322AC2D45C` FOREIGN KEY (`summary_id`) REFERENCES `summaries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_summary_languages`
--

LOCK TABLES `member_summary_languages` WRITE;
/*!40000 ALTER TABLE `member_summary_languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_summary_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_summary_mission_types`
--

DROP TABLE IF EXISTS `member_summary_mission_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_summary_mission_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_summary_mission_type_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_summary_mission_types`
--

LOCK TABLES `member_summary_mission_types` WRITE;
/*!40000 ALTER TABLE `member_summary_mission_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_summary_mission_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_summary_trainings`
--

DROP TABLE IF EXISTS `member_summary_trainings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_summary_trainings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `summary_id` int(11) DEFAULT NULL,
  `organization` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `diploma` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `study_field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `started_at` date NOT NULL,
  `ended_at` date DEFAULT NULL,
  `on_going` tinyint(1) NOT NULL DEFAULT '0',
  `description` longtext COLLATE utf8_unicode_ci,
  `extra_curricular` longtext COLLATE utf8_unicode_ci,
  `display_order` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `IDX_C101987B2AC2D45C` (`summary_id`),
  CONSTRAINT `FK_C101987B2AC2D45C` FOREIGN KEY (`summary_id`) REFERENCES `summaries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_summary_trainings`
--

LOCK TABLES `member_summary_trainings` WRITE;
/*!40000 ALTER TABLE `member_summary_trainings` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_summary_trainings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_invitations`
--

DROP TABLE IF EXISTS `newsletter_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `client_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_invitations`
--

LOCK TABLES `newsletter_invitations` WRITE;
/*!40000 ALTER TABLE `newsletter_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_subscriptions`
--

DROP TABLE IF EXISTS `newsletter_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_subscriptions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `postal_code` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B3C13B0BE7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_subscriptions`
--

LOCK TABLES `newsletter_subscriptions` WRITE;
/*!40000 ALTER TABLE `newsletter_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_articles`
--

DROP TABLE IF EXISTS `order_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_id` bigint(20) DEFAULT NULL,
  `position` smallint(6) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `twitter_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `amp_content` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5E25D3D9989D9B62` (`slug`),
  KEY `IDX_5E25D3D9EA9FDD75` (`media_id`),
  CONSTRAINT `FK_5E25D3D9EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_articles`
--

LOCK TABLES `order_articles` WRITE;
/*!40000 ALTER TABLE `order_articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_section_order_article`
--

DROP TABLE IF EXISTS `order_section_order_article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_section_order_article` (
  `order_article_id` int(11) NOT NULL,
  `order_section_id` int(11) NOT NULL,
  PRIMARY KEY (`order_article_id`,`order_section_id`),
  KEY `IDX_69D950ADC14E7BC9` (`order_article_id`),
  KEY `IDX_69D950AD6BF91E2F` (`order_section_id`),
  CONSTRAINT `FK_69D950AD6BF91E2F` FOREIGN KEY (`order_section_id`) REFERENCES `order_sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_69D950ADC14E7BC9` FOREIGN KEY (`order_article_id`) REFERENCES `order_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_section_order_article`
--

LOCK TABLES `order_section_order_article` WRITE;
/*!40000 ALTER TABLE `order_section_order_article` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_section_order_article` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_sections`
--

DROP TABLE IF EXISTS `order_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `position` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_sections`
--

LOCK TABLES `order_sections` WRITE;
/*!40000 ALTER TABLE `order_sections` DISABLE KEYS */;
INSERT INTO `order_sections` VALUES (1,'Articles',1),(2,'Tribune',2),(3,'Lexique',3),(4,'Autre resources',4);
/*!40000 ALTER TABLE `order_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `media_id` bigint(20) DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_media` tinyint(1) NOT NULL,
  `twitter_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amp_content` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2074E575989D9B62` (`slug`),
  KEY `IDX_2074E575EA9FDD75` (`media_id`),
  CONSTRAINT `FK_2074E575EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'2018-02-14 09:23:58','2018-02-14 09:23:58',NULL,'Les ordonnances expliquées','les-ordonnances-expliquees','Ici vous trouverez les ordonnances expliquées','<div class=\"explainer__description\">\n    Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.\n</div>\n\n<div class=\"explainer__video-row\">\n    <h2>Points de vus</h2>\n    <a href=\"#\" target=\"_blank\">Toutes les videos &raquo;</a>\n    <ul>\n        <li>\n            <figure class=\"facebook\">\n                <amp-facebook layout=\"responsive\" data-href=\"https://www.facebook.com/EmmanuelMacron/posts/1986040634961846\" data-embed-as=\"post\" height=\"504\" width=\"500\"></amp-facebook>\n            </figure>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\"  style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\"  style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\"  style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\" style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n    </ul>\n</div>\n\n<div class=\"explainer__video-row explainer__video-row--triptique\">\n    <h2>Témoignages</h2>\n    <a href=\"#\" target=\"_blank\">Toutes les videos &raquo;</a>\n    <ul>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\"  style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\"  style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\" style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n    </ul>\n</div>\n\n<div class=\"explainer__video-row\">\n    <h2>Désintox</h2>\n    <a href=\"#\" target=\"_blank\">Toutes les videos &raquo;</a>\n    <ul>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\"  style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\"  style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\"  style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n        <li>\n            <div>\n                <iframe src=\"https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FEdouardPhilippePM%2Fvideos%2F2170853183141349%2F&show_text=0&width=560\" style=\"border:none;overflow:hidden\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" allowFullScreen=\"true\"></iframe>\n            </div>\n        </li>\n    </ul>\n</div>\n',NULL,'loi travail ordonnances explications',0,NULL,NULL);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_managed_areas`
--

DROP TABLE IF EXISTS `procuration_managed_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procuration_managed_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adherent_id` int(10) unsigned DEFAULT NULL,
  `codes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_117496A025F06C53` (`adherent_id`),
  CONSTRAINT `FK_117496A025F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_managed_areas`
--

LOCK TABLES `procuration_managed_areas` WRITE;
/*!40000 ALTER TABLE `procuration_managed_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_managed_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_proxies`
--

DROP TABLE IF EXISTS `procuration_proxies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procuration_proxies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gender` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `first_names` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city_insee` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `email_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `birthdate` date DEFAULT NULL,
  `vote_postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vote_city_insee` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vote_city_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vote_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `vote_office` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `election_presidential_first_round` tinyint(1) NOT NULL,
  `election_presidential_second_round` tinyint(1) NOT NULL,
  `election_legislative_first_round` tinyint(1) NOT NULL,
  `election_legislative_second_round` tinyint(1) NOT NULL,
  `referent_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `procuration_request_id` int(11) DEFAULT NULL,
  `reliability` smallint(6) NOT NULL,
  `disabled` tinyint(1) NOT NULL,
  `invite_source_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invite_source_first_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reliability_description` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9B5E777A128D9C53` (`procuration_request_id`),
  KEY `IDX_9B5E777A35E47E35` (`referent_id`),
  CONSTRAINT `FK_9B5E777A128D9C53` FOREIGN KEY (`procuration_request_id`) REFERENCES `procuration_requests` (`id`),
  CONSTRAINT `FK_9B5E777A35E47E35` FOREIGN KEY (`referent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_proxies`
--

LOCK TABLES `procuration_proxies` WRITE;
/*!40000 ALTER TABLE `procuration_proxies` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_proxies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_requests`
--

DROP TABLE IF EXISTS `procuration_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procuration_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gender` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `first_names` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city_insee` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `email_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `birthdate` date DEFAULT NULL,
  `vote_postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vote_city_insee` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vote_city_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vote_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `vote_office` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `election_presidential_first_round` tinyint(1) NOT NULL,
  `election_presidential_second_round` tinyint(1) NOT NULL,
  `election_legislative_first_round` tinyint(1) NOT NULL,
  `election_legislative_second_round` tinyint(1) NOT NULL,
  `reason` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `processed` tinyint(1) NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `procuration_request_found_by_id` int(10) unsigned DEFAULT NULL,
  `reminded` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9769FD84888FDEEE` (`procuration_request_found_by_id`),
  CONSTRAINT `FK_9769FD84888FDEEE` FOREIGN KEY (`procuration_request_found_by_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_requests`
--

LOCK TABLES `procuration_requests` WRITE;
/*!40000 ALTER TABLE `procuration_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projection_referent_managed_users`
--

DROP TABLE IF EXISTS `projection_referent_managed_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projection_referent_managed_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status` smallint(6) NOT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `original_id` bigint(20) unsigned NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `age` smallint(6) DEFAULT NULL,
  `phone` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `committees` longtext COLLATE utf8_unicode_ci,
  `is_committee_member` tinyint(1) NOT NULL,
  `is_committee_host` tinyint(1) NOT NULL,
  `is_mail_subscriber` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `is_committee_supervisor` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projection_referent_managed_users_search` (`status`,`postal_code`,`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projection_referent_managed_users`
--

LOCK TABLES `projection_referent_managed_users` WRITE;
/*!40000 ALTER TABLE `projection_referent_managed_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `projection_referent_managed_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proposal_proposal_theme`
--

DROP TABLE IF EXISTS `proposal_proposal_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proposal_proposal_theme` (
  `proposal_id` int(11) NOT NULL,
  `proposal_theme_id` int(11) NOT NULL,
  PRIMARY KEY (`proposal_id`,`proposal_theme_id`),
  KEY `IDX_6B80CE41F4792058` (`proposal_id`),
  KEY `IDX_6B80CE41B85948AF` (`proposal_theme_id`),
  CONSTRAINT `FK_6B80CE41B85948AF` FOREIGN KEY (`proposal_theme_id`) REFERENCES `proposals_themes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6B80CE41F4792058` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proposal_proposal_theme`
--

LOCK TABLES `proposal_proposal_theme` WRITE;
/*!40000 ALTER TABLE `proposal_proposal_theme` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposal_proposal_theme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proposals`
--

DROP TABLE IF EXISTS `proposals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proposals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_id` bigint(20) DEFAULT NULL,
  `position` smallint(6) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amp_content` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A5BA3A8F989D9B62` (`slug`),
  KEY `IDX_A5BA3A8FEA9FDD75` (`media_id`),
  CONSTRAINT `FK_A5BA3A8FEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proposals`
--

LOCK TABLES `proposals` WRITE;
/*!40000 ALTER TABLE `proposals` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proposals_themes`
--

DROP TABLE IF EXISTS `proposals_themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proposals_themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `color` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proposals_themes`
--

LOCK TABLES `proposals_themes` WRITE;
/*!40000 ALTER TABLE `proposals_themes` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposals_themes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redirections`
--

DROP TABLE IF EXISTS `redirections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `redirections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url_from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url_to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `redirections`
--

LOCK TABLES `redirections` WRITE;
/*!40000 ALTER TABLE `redirections` DISABLE KEYS */;
/*!40000 ALTER TABLE `redirections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent`
--

DROP TABLE IF EXISTS `referent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referent` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `media_id` bigint(20) DEFAULT NULL,
  `gender` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `email_address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `facebook_page_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter_page_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `geojson` longtext COLLATE utf8_unicode_ci,
  `description` longtext COLLATE utf8_unicode_ci,
  `area_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'DISABLED',
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referent_slug_unique` (`slug`),
  KEY `IDX_FE9AAC6CEA9FDD75` (`media_id`),
  CONSTRAINT `FK_FE9AAC6CEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent`
--

LOCK TABLES `referent` WRITE;
/*!40000 ALTER TABLE `referent` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_area`
--

DROP TABLE IF EXISTS `referent_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referent_area` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `area_code` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `area_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referent_area_area_code_unique` (`area_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_area`
--

LOCK TABLES `referent_area` WRITE;
/*!40000 ALTER TABLE `referent_area` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_area` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_areas`
--

DROP TABLE IF EXISTS `referent_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referent_areas` (
  `referent_id` smallint(5) unsigned NOT NULL,
  `area_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`referent_id`,`area_id`),
  KEY `IDX_75CEBC6C35E47E35` (`referent_id`),
  KEY `IDX_75CEBC6CBD0F409C` (`area_id`),
  CONSTRAINT `FK_75CEBC6C35E47E35` FOREIGN KEY (`referent_id`) REFERENCES `referent` (`id`),
  CONSTRAINT `FK_75CEBC6CBD0F409C` FOREIGN KEY (`area_id`) REFERENCES `referent_area` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_areas`
--

LOCK TABLES `referent_areas` WRITE;
/*!40000 ALTER TABLE `referent_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_managed_users_message`
--

DROP TABLE IF EXISTS `referent_managed_users_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referent_managed_users_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int(10) unsigned DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `include_newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `include_adherents_no_committee` tinyint(1) NOT NULL DEFAULT '0',
  `include_adherents_in_committee` tinyint(1) NOT NULL DEFAULT '0',
  `include_hosts` tinyint(1) NOT NULL DEFAULT '0',
  `include_supevisors` tinyint(1) NOT NULL DEFAULT '0',
  `query_area_code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `query_city` longtext COLLATE utf8_unicode_ci NOT NULL,
  `query_id` longtext COLLATE utf8_unicode_ci NOT NULL,
  `offset` bigint(20) NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1E41AC6125F06C53` (`adherent_id`),
  CONSTRAINT `FK_1E41AC6125F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_managed_users_message`
--

LOCK TABLES `referent_managed_users_message` WRITE;
/*!40000 ALTER TABLE `referent_managed_users_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_managed_users_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(10) unsigned DEFAULT NULL,
  `citizen_project_id` int(10) unsigned DEFAULT NULL,
  `reasons` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `comment` longtext COLLATE utf8_unicode_ci,
  `status` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_uuid_unique` (`uuid`),
  KEY `IDX_F11FA745B3584533` (`citizen_project_id`),
  KEY `IDX_F11FA745F675F31B` (`author_id`),
  KEY `report_status_idx` (`status`),
  KEY `report_type_idx` (`type`),
  CONSTRAINT `FK_F11FA745A76ED395` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_F11FA745B3584533` FOREIGN KEY (`citizen_project_id`) REFERENCES `citizen_projects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `board_member_role_code_unique` (`code`),
  UNIQUE KEY `board_member_role_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'adherent','Adhérent(e) membre de la société civile'),(2,'supervisor','Animateur(trice) de comités locaux'),(3,'referent','Référent(e) territorial(e)'),(4,'deputy','Député(e) national(e)'),(5,'european_deputy','Député(e) européen(ne)'),(6,'minister','Membre du gouvernement'),(7,'senator','Sénateur(trice)'),(8,'consular','Conseiller(ère) Consulaire'),(9,'president_larem','Président(e) du groupe LaREM d\'un exécutif local'),(10,'president','Président(e) de région / département / assemblée territoriale'),(11,'mayor_less','Maire d\'une commune de moins de 50 000 habitants'),(12,'mayor_more','Maire d\'une commune de plus de 50 000 habitants'),(13,'president_less','Président(e) d\'établissement de coopération intercommunale EPCI < 100 000 habitants'),(14,'president_more','Président(e) d\'établissement de coopération intercommunale EPCI > 100 000 habitants'),(15,'personality','Personnalité');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_board_members`
--

DROP TABLE IF EXISTS `saved_board_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saved_board_members` (
  `board_member_owner_id` int(11) NOT NULL,
  `board_member_saved_id` int(11) NOT NULL,
  PRIMARY KEY (`board_member_owner_id`,`board_member_saved_id`),
  KEY `IDX_32865A32FDCCD727` (`board_member_owner_id`),
  KEY `IDX_32865A324821D202` (`board_member_saved_id`),
  CONSTRAINT `FK_32865A324821D202` FOREIGN KEY (`board_member_saved_id`) REFERENCES `board_member` (`id`),
  CONSTRAINT `FK_32865A32FDCCD727` FOREIGN KEY (`board_member_owner_id`) REFERENCES `board_member` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_board_members`
--

LOCK TABLES `saved_board_members` WRITE;
/*!40000 ALTER TABLE `saved_board_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_board_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skill_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES (1,'Agriculture','agriculture'),(2,'Elevage','elevage'),(3,'Culture','culture'),(4,'Architecture','architecture'),(5,'Arts','arts'),(6,'Peinture','peinture'),(7,'Sculpture','sculpture'),(8,'Décoration','decoration'),(9,'Musique','musique'),(10,'Théâtre','theatre'),(11,'Dessin','dessin'),(12,'Photographie','photographie'),(13,'Cirque','cirque'),(14,'Pêche','peche'),(15,'Soins aux animaux','soins-aux-animaux'),(16,'Nature et Environnement','nature-et-environnement'),(17,'Paysage','paysage'),(18,'Jardinage/Botanique','jardinage-botanique'),(19,'Gestion des parcs nationaux','gestion-des-parcs-nationaux'),(20,'Isolation thermique et acoustique','isolation-thermique-et-acoustique'),(21,'Horticulture','horticulture'),(22,'Gestion des déchets','gestion-des-dechets'),(23,'Communication/Marketing/Médias','communication-marketing-medias'),(24,'Organisation d\'événements','organisation-d-evenements'),(25,'Gestion de la presse','gestion-de-la-presse'),(26,'Relations publiques','relations-publiques'),(27,'Supports de communication ','supports-de-communication'),(28,'Veille','veille'),(29,'Community management','community-management'),(30,'Plan média','plan-media'),(31,'Reportage','reportage'),(32,'Rédaction d\'articles','redaction-d-articles'),(33,'Conception de contenus multimédias','conception-de-contenus-multimedias'),(34,'Enquêtes de satisfaction','enquetes-de-satisfaction'),(35,'Mailings','mailings'),(36,'Comptabilité','comptabilite'),(37,'Finance','finance'),(38,'Levée de fonds','levee-de-fonds'),(39,'Elaboration de budget','elaboration-de-budget'),(40,'Audit','audit'),(41,'Ingénieur/Technicien','ingenieur-technicien'),(42,'Conduite de chantier','conduite-de-chantier'),(43,'Maçonnerie','maconnerie'),(44,'Plomberie','plomberie'),(45,'Construction/Travaux','construction-travaux'),(46,'Electricité','electricite'),(47,'Droit','droit'),(48,'Droit du patrimoine','droit-du-patrimoine'),(49,'Droit pénal','droit-penal'),(50,'Droit des pensions civiles et militaires de retraite','droit-des-pensions-civiles-et-militaires-de-retraite'),(51,'Droit des pensions de retraite des marins français du commerce, de pêche ou de plaisance','droit-des-pensions-de-retraite-des-marins-francais-du-commerce-de-peche-ou-de-plaisance'),(52,'Droit des pensions militaires d\'invalidité et des victimes de la guerre','droit-des-pensions-militaires-d-invalidite-et-des-victimes-de-la-guerre'),(53,'Droit des ports maritimes','droit-des-ports-maritimes'),(54,'Droit des postes et des communications électroniques','droit-des-postes-et-des-communications-electroniques'),(55,'Droit de la propriété intellectuelle','droit-de-la-propriete-intellectuelle'),(56,'Droit de la recherche','droit-de-la-recherche'),(57,'Droit de la route','droit-de-la-route'),(58,'Droit rural et de la pêche maritime','droit-rural-et-de-la-peche-maritime'),(59,'Droit de la santé publique','droit-de-la-sante-publique'),(60,'Droit de la sécurité intérieure','droit-de-la-securite-interieure'),(61,'Droit de la sécurité sociale','droit-de-la-securite-sociale'),(62,'Droit du service national','droit-du-service-national'),(63,'Droit du sport','droit-du-sport'),(64,'Droit du tourisme','droit-du-tourisme'),(65,'Droit du travail (... à Mayotte)','droit-du-travail-a-mayotte'),(66,'Droit du travail maritime','droit-du-travail-maritime'),(67,'Droit de l\'urbanisme','droit-de-l-urbanisme'),(68,'Droit de la voirie routière','droit-de-la-voirie-routiere'),(69,'Conseil juridique','conseil-juridique'),(70,'Contentieux','contentieux'),(71,'Rédaction d\'actes juridiques','redaction-d-actes-juridiques'),(72,'Veille juridique','veille-juridique'),(73,'Education','education'),(74,'Cours de Mathématiques','cours-de-mathematiques'),(75,'Cours de Philosophie','cours-de-philosophie'),(76,'Cours d\'Histoire/Géo','cours-d-histoire-geo'),(77,'Cours de SVT','cours-de-svt'),(78,'Cours de Français','cours-de-francais'),(79,'Cours d\'Anglais','cours-d-anglais'),(80,'Cours d\'Espagnol','cours-d-espagnol'),(81,'Cours d\'Arabe','cours-d-arabe'),(82,'Cours d\'Hébreu','cours-d-hebreu'),(83,'Cours de Géométrie','cours-de-geometrie'),(84,'Cours de Chinois','cours-de-chinois'),(85,'Cours d\'Italien','cours-d-italien'),(86,'Cours de guitare','cours-de-guitare'),(87,'Cours de Yoga','cours-de-yoga'),(88,'Graphisme','graphisme'),(89,'Conception de logo, brochure, etc','conception-de-logo-brochure-etc'),(90,'Hôtellerie','hotellerie'),(91,'Restauration','restauration'),(92,'Chef cuisinier','chef-cuisinier'),(93,'Restauration rapide','restauration-rapide'),(94,'Pâtisserie','patisserie'),(95,'Tourisme/loisirs','tourisme-loisirs'),(96,'Animation','animation'),(97,'BAFA','bafa'),(98,'Enfants','enfants'),(99,'Tous publics','tous-publics'),(100,'Clown','clown'),(101,'Cuisine','cuisine'),(102,'Informatique/Numérique/Digital','informatique-numerique-digital'),(103,'Création d\'un site Internet','creation-d-un-site-internet'),(104,'Programmation web/code (PHP, Java, SQL, ASP, etc.)','programmation-web-code-php-java-sql-asp-etc'),(105,'Gestion de bases de données','gestion-de-bases-de-donnees'),(106,'Développement d\'applications','developpement-d-applications'),(107,'Santé','sante'),(108,'Médecin','medecin'),(109,'Infirmier-e','infirmier-e'),(110,'Aide-soignant','aide-soignant'),(111,'Psychologue','psychologue'),(112,'Secouriste','secouriste'),(113,'Lien social','lien-social'),(114,'Conversation','conversation'),(115,'Assistance aux personnes âgées','assistance-aux-personnes-agees'),(116,'Traduction','traduction'),(117,'Français - Chinois','francais-chinois'),(118,'Français - Anglais','francais-anglais'),(119,'Français – Espagnol','francais-espagnol'),(120,'Français - Hindi','francais-hindi'),(121,'Français – Allemand','francais-allemand'),(122,'Français - Russe','francais-russe'),(123,'Français - Malais-indonésien','francais-malais-indonesien'),(124,'Français - Arabe','francais-arabe'),(125,'Français - Portugais','francais-portugais'),(126,'Français - Bengali','francais-bengali');
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_share_categories`
--

DROP TABLE IF EXISTS `social_share_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_share_categories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_share_categories`
--

LOCK TABLES `social_share_categories` WRITE;
/*!40000 ALTER TABLE `social_share_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_share_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_shares`
--

DROP TABLE IF EXISTS `social_shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_shares` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `social_share_category_id` bigint(20) DEFAULT NULL,
  `media_id` bigint(20) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `default_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `facebook_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `published` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8E1413A085040FAD` (`social_share_category_id`),
  KEY `IDX_8E1413A0EA9FDD75` (`media_id`),
  CONSTRAINT `FK_8E1413A085040FAD` FOREIGN KEY (`social_share_category_id`) REFERENCES `social_share_categories` (`id`),
  CONSTRAINT `FK_8E1413A0EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_shares`
--

LOCK TABLES `social_shares` WRITE;
/*!40000 ALTER TABLE `social_shares` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `summaries`
--

DROP TABLE IF EXISTS `summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `summaries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `current_profession` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contribution_wish` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `professional_synopsis` longtext COLLATE utf8_unicode_ci NOT NULL,
  `motivation` longtext COLLATE utf8_unicode_ci NOT NULL,
  `contact_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `linked_in_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `facebook_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter_nickname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `viadeo_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `availabilities` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
  `job_locations` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `showing_recent_activities` tinyint(1) NOT NULL,
  `picture_uploaded` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_66783CCA7597D3FE` (`member_id`),
  CONSTRAINT `FK_66783CCA7597D3FE` FOREIGN KEY (`member_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `summaries`
--

LOCK TABLES `summaries` WRITE;
/*!40000 ALTER TABLE `summaries` DISABLE KEYS */;
/*!40000 ALTER TABLE `summaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `summary_mission_type_wishes`
--

DROP TABLE IF EXISTS `summary_mission_type_wishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `summary_mission_type_wishes` (
  `summary_id` int(11) NOT NULL,
  `mission_type_id` int(11) NOT NULL,
  PRIMARY KEY (`summary_id`,`mission_type_id`),
  KEY `IDX_7F3FC70F2AC2D45C` (`summary_id`),
  KEY `IDX_7F3FC70F547018DE` (`mission_type_id`),
  CONSTRAINT `FK_7F3FC70F2AC2D45C` FOREIGN KEY (`summary_id`) REFERENCES `summaries` (`id`),
  CONSTRAINT `FK_7F3FC70F547018DE` FOREIGN KEY (`mission_type_id`) REFERENCES `member_summary_mission_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `summary_mission_type_wishes`
--

LOCK TABLES `summary_mission_type_wishes` WRITE;
/*!40000 ALTER TABLE `summary_mission_type_wishes` DISABLE KEYS */;
/*!40000 ALTER TABLE `summary_mission_type_wishes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `summary_skills`
--

DROP TABLE IF EXISTS `summary_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `summary_skills` (
  `summary_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  PRIMARY KEY (`summary_id`,`skill_id`),
  KEY `IDX_2FD2B63C2AC2D45C` (`summary_id`),
  KEY `IDX_2FD2B63C5585C142` (`skill_id`),
  CONSTRAINT `FK_2FD2B63C2AC2D45C` FOREIGN KEY (`summary_id`) REFERENCES `summaries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2FD2B63C5585C142` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `summary_skills`
--

LOCK TABLES `summary_skills` WRITE;
/*!40000 ALTER TABLE `summary_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `summary_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_measures`
--

DROP TABLE IF EXISTS `timeline_measures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline_measures` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `global` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_measures`
--

LOCK TABLES `timeline_measures` WRITE;
/*!40000 ALTER TABLE `timeline_measures` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_measures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_measures_profiles`
--

DROP TABLE IF EXISTS `timeline_measures_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline_measures_profiles` (
  `measure_id` bigint(20) NOT NULL,
  `profile_id` bigint(20) NOT NULL,
  PRIMARY KEY (`measure_id`,`profile_id`),
  KEY `IDX_B83D81AE5DA37D00` (`measure_id`),
  KEY `IDX_B83D81AECCFA12B8` (`profile_id`),
  CONSTRAINT `FK_B83D81AE5DA37D00` FOREIGN KEY (`measure_id`) REFERENCES `timeline_measures` (`id`),
  CONSTRAINT `FK_B83D81AECCFA12B8` FOREIGN KEY (`profile_id`) REFERENCES `timeline_profiles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_measures_profiles`
--

LOCK TABLES `timeline_measures_profiles` WRITE;
/*!40000 ALTER TABLE `timeline_measures_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_measures_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_profiles`
--

DROP TABLE IF EXISTS `timeline_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline_profiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DB00DE3B989D9B62` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_profiles`
--

LOCK TABLES `timeline_profiles` WRITE;
/*!40000 ALTER TABLE `timeline_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_themes`
--

DROP TABLE IF EXISTS `timeline_themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline_themes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `media_id` bigint(20) DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `display_media` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8ADDB8F6989D9B62` (`slug`),
  KEY `IDX_8ADDB8F6EA9FDD75` (`media_id`),
  CONSTRAINT `FK_8ADDB8F6EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_themes`
--

LOCK TABLES `timeline_themes` WRITE;
/*!40000 ALTER TABLE `timeline_themes` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_themes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_themes_measures`
--

DROP TABLE IF EXISTS `timeline_themes_measures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline_themes_measures` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `theme_id` bigint(20) DEFAULT NULL,
  `measure_id` bigint(20) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_EB8A7B0C59027487` (`theme_id`),
  KEY `IDX_EB8A7B0C5DA37D00` (`measure_id`),
  CONSTRAINT `FK_EB8A7B0C59027487` FOREIGN KEY (`theme_id`) REFERENCES `timeline_themes` (`id`),
  CONSTRAINT `FK_EB8A7B0C5DA37D00` FOREIGN KEY (`measure_id`) REFERENCES `timeline_measures` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_themes_measures`
--

LOCK TABLES `timeline_themes_measures` WRITE;
/*!40000 ALTER TABLE `timeline_themes_measures` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_themes_measures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ton_macron_choices`
--

DROP TABLE IF EXISTS `ton_macron_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ton_macron_choices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `step` smallint(5) unsigned NOT NULL,
  `content_key` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ton_macron_choices_uuid_unique` (`uuid`),
  UNIQUE KEY `ton_macron_choices_content_key_unique` (`content_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ton_macron_choices`
--

LOCK TABLES `ton_macron_choices` WRITE;
/*!40000 ALTER TABLE `ton_macron_choices` DISABLE KEYS */;
/*!40000 ALTER TABLE `ton_macron_choices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ton_macron_friend_invitation_has_choices`
--

DROP TABLE IF EXISTS `ton_macron_friend_invitation_has_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ton_macron_friend_invitation_has_choices` (
  `invitation_id` int(10) unsigned NOT NULL,
  `choice_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`invitation_id`,`choice_id`),
  KEY `IDX_BB3BCAEEA35D7AF0` (`invitation_id`),
  KEY `IDX_BB3BCAEE998666D1` (`choice_id`),
  CONSTRAINT `FK_BB3BCAEE998666D1` FOREIGN KEY (`choice_id`) REFERENCES `ton_macron_choices` (`id`),
  CONSTRAINT `FK_BB3BCAEEA35D7AF0` FOREIGN KEY (`invitation_id`) REFERENCES `ton_macron_friend_invitations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ton_macron_friend_invitation_has_choices`
--

LOCK TABLES `ton_macron_friend_invitation_has_choices` WRITE;
/*!40000 ALTER TABLE `ton_macron_friend_invitation_has_choices` DISABLE KEYS */;
/*!40000 ALTER TABLE `ton_macron_friend_invitation_has_choices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ton_macron_friend_invitations`
--

DROP TABLE IF EXISTS `ton_macron_friend_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ton_macron_friend_invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `friend_first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `friend_age` smallint(5) unsigned NOT NULL,
  `friend_gender` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `friend_position` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `friend_email_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_first_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_last_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author_email_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail_subject` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail_body` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime NOT NULL,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ton_macron_friend_invitations_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ton_macron_friend_invitations`
--

LOCK TABLES `ton_macron_friend_invitations` WRITE;
/*!40000 ALTER TABLE `ton_macron_friend_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ton_macron_friend_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unregistrations`
--

DROP TABLE IF EXISTS `unregistrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unregistrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `postal_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reasons` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `comment` longtext COLLATE utf8_unicode_ci,
  `registered_at` datetime NOT NULL,
  `unregistered_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unregistrations`
--

LOCK TABLES `unregistrations` WRITE;
/*!40000 ALTER TABLE `unregistrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `unregistrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'enmarche'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-02-14  9:24:38

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES ('20170224124802'),('20170227135708'),('20170301230652'),('20170301233005'),('20170302005646'),('20170305181656'),('20170316160611'),('20170318150212'),('20170318183424'),('20170319151600'),('20170320162000'),('20170321155653'),('20170322112107'),('20170323231620'),('20170324172552'),('20170324220504'),('20170326131919'),('20170328193329'),('20170328230144'),('20170328232712'),('20170329182850'),('20170329230710'),('20170402145007'),('20170402220916'),('20170404154958'),('20170406113002'),('20170410002430'),('20170411110645'),('20170413205542'),('20170414155627'),('20170415154517'),('20170415233923'),('20170416192241'),('20170416215738'),('20170416220534'),('20170417004202'),('20170417025900'),('20170419181904'),('20170420161601'),('20170424132400'),('20170424164217'),('20170427194728'),('20170502113039'),('20170503015627'),('20170516173343'),('20170517100000'),('20170523141753'),('20170525190824'),('20170526002334'),('20170526163645'),('20170529215440'),('20170530110634'),('20170531154659'),('20170601095909'),('20170601194424'),('20170604163915'),('20170610174545'),('20170611165500'),('20170612143204'),('20170612233557'),('20170613113525'),('20170613114117'),('20170614200628'),('20170618214419'),('20170620164351'),('20170621190629'),('20170622000014'),('20170622150723'),('20170626174545'),('20170706102307'),('20170707135601'),('20170711154148'),('20170719104838'),('20170727095245'),('20170728084630'),('20170731164115'),('20170807160045'),('20170807164446'),('20170810154040'),('20170816150411'),('20170821164940'),('20170822145517'),('20170830103508'),('20170830113820'),('20170831142727'),('20170901110008'),('20170901155126'),('20170904153003'),('20170905110626'),('20170914111538'),('20170915104055'),('20170918170256'),('20170919103349'),('20170920110151'),('20170920151934'),('20170926103812'),('20170929155932'),('20171003164518'),('20171005145433'),('20171011100525'),('20171012114958'),('20171017115955'),('20171024022242'),('20171026170529'),('20171027123140'),('20171027153402'),('20171028202706'),('20171103193650'),('20171109144147'),('20171113171313'),('20171114105055'),('20171114160211'),('20171117175255'),('20171120153620'),('20171122172312'),('20171127093926'),('20171129011217'),('20171130161957'),('20171203025556'),('20171204142404'),('20171206160235'),('20171207163211'),('20171208171018'),('20171211123655'),('20171217185611'),('20171227163801'),('20171228120315'),('20171228161433'),('20171229105402');
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-02-15 14:53:34
