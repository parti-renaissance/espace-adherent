/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `adherent_activation_code`
--

DROP TABLE IF EXISTS `adherent_activation_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_activation_code` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `expired_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3A628E8F25F06C53` (`adherent_id`),
  KEY `IDX_3A628E8F25F06C531D775834` (`adherent_id`,`value`),
  CONSTRAINT `FK_3A628E8F25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_activation_code`
--

LOCK TABLES `adherent_activation_code` WRITE;
/*!40000 ALTER TABLE `adherent_activation_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_activation_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_activation_keys`
--

DROP TABLE IF EXISTS `adherent_activation_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_activation_keys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `value` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `expired_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_activation_token_account_unique` (`value`,`adherent_uuid`),
  UNIQUE KEY `adherent_activation_token_unique` (`value`),
  UNIQUE KEY `UNIQ_F9F9FAFBD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_activation_keys`
--

LOCK TABLES `adherent_activation_keys` WRITE;
/*!40000 ALTER TABLE `adherent_activation_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_activation_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_certification_histories`
--

DROP TABLE IF EXISTS `adherent_certification_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_certification_histories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `administrator_id` int DEFAULT NULL,
  `action` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `adherent_certification_histories_adherent_id_idx` (`adherent_id`),
  KEY `adherent_certification_histories_administrator_id_idx` (`administrator_id`),
  KEY `adherent_certification_histories_date_idx` (`date`),
  CONSTRAINT `FK_732EE81A25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_732EE81A4B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_certification_histories`
--

LOCK TABLES `adherent_certification_histories` WRITE;
/*!40000 ALTER TABLE `adherent_certification_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_certification_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_change_email_token`
--

DROP TABLE IF EXISTS `adherent_change_email_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_change_email_token` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `value` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `expired_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6F8B4B5AD17F50A6` (`uuid`),
  KEY `IDX_6F8B4B5AE7927C7477241BAC253ECC4` (`email`,`used_at`,`expired_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_change_email_token`
--

LOCK TABLES `adherent_change_email_token` WRITE;
/*!40000 ALTER TABLE `adherent_change_email_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_change_email_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_charter`
--

DROP TABLE IF EXISTS `adherent_charter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_charter` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `accepted_at` datetime NOT NULL,
  `dtype` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D6F94F2B25F06C5370AAEA5` (`adherent_id`,`dtype`),
  KEY `IDX_D6F94F2B25F06C53` (`adherent_id`),
  CONSTRAINT `FK_D6F94F2B25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_charter`
--

LOCK TABLES `adherent_charter` WRITE;
/*!40000 ALTER TABLE `adherent_charter` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_charter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_commitment`
--

DROP TABLE IF EXISTS `adherent_commitment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_commitment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `commitment_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `debate_and_propose_ideas_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `act_for_territory_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `progressivism_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `availability` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D239EF6F25F06C53` (`adherent_id`),
  CONSTRAINT `FK_D239EF6F25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_commitment`
--

LOCK TABLES `adherent_commitment` WRITE;
/*!40000 ALTER TABLE `adherent_commitment` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_commitment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_declared_mandate_history`
--

DROP TABLE IF EXISTS `adherent_declared_mandate_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_declared_mandate_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `added_mandates` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `removed_mandates` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `administrator_id` int DEFAULT NULL,
  `notified_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_A92880F925F06C53` (`adherent_id`),
  KEY `IDX_A92880F94B09E92C` (`administrator_id`),
  CONSTRAINT `FK_A92880F925F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A92880F94B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_declared_mandate_history`
--

LOCK TABLES `adherent_declared_mandate_history` WRITE;
/*!40000 ALTER TABLE `adherent_declared_mandate_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_declared_mandate_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_email_subscribe_token`
--

DROP TABLE IF EXISTS `adherent_email_subscribe_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_email_subscribe_token` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `adherent_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `value` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `expired_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `trigger_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_376DBA01D775834` (`value`),
  UNIQUE KEY `UNIQ_376DBA01D7758346D804024` (`value`,`adherent_uuid`),
  UNIQUE KEY `UNIQ_376DBA0D17F50A6` (`uuid`),
  KEY `IDX_376DBA09DF5350C` (`created_by_administrator_id`),
  KEY `IDX_376DBA0CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_376DBA0F675F31B` (`author_id`),
  CONSTRAINT `FK_376DBA09DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_376DBA0CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_376DBA0F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_email_subscribe_token`
--

LOCK TABLES `adherent_email_subscribe_token` WRITE;
/*!40000 ALTER TABLE `adherent_email_subscribe_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_email_subscribe_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_email_subscription_histories`
--

DROP TABLE IF EXISTS `adherent_email_subscription_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_email_subscription_histories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subscription_type_id` int unsigned NOT NULL,
  `adherent_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `action` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `adherent_email_subscription_histories_adherent_action_idx` (`action`),
  KEY `adherent_email_subscription_histories_adherent_date_idx` (`date`),
  KEY `adherent_email_subscription_histories_adherent_uuid_idx` (`adherent_uuid`),
  KEY `IDX_51AD8354B6596C08` (`subscription_type_id`),
  CONSTRAINT `FK_51AD8354B6596C08` FOREIGN KEY (`subscription_type_id`) REFERENCES `subscription_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_email_subscription_histories`
--

LOCK TABLES `adherent_email_subscription_histories` WRITE;
/*!40000 ALTER TABLE `adherent_email_subscription_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_email_subscription_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_email_subscription_history_referent_tag`
--

DROP TABLE IF EXISTS `adherent_email_subscription_history_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_email_subscription_history_referent_tag` (
  `email_subscription_history_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`email_subscription_history_id`,`referent_tag_id`),
  KEY `IDX_6FFBE6E88FCB8132` (`email_subscription_history_id`),
  KEY `IDX_6FFBE6E89C262DB3` (`referent_tag_id`),
  CONSTRAINT `FK_6FFBE6E88FCB8132` FOREIGN KEY (`email_subscription_history_id`) REFERENCES `adherent_email_subscription_histories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6FFBE6E89C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_email_subscription_history_referent_tag`
--

LOCK TABLES `adherent_email_subscription_history_referent_tag` WRITE;
/*!40000 ALTER TABLE `adherent_email_subscription_history_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_email_subscription_history_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_formation`
--

DROP TABLE IF EXISTS `adherent_formation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_formation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `print_count` smallint unsigned NOT NULL,
  `position` smallint NOT NULL DEFAULT '0',
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `content_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `visibility` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2D97408BD17F50A6` (`uuid`),
  KEY `IDX_2D97408B9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_2D97408BCF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_2D97408B85C9D733` (`created_by_adherent_id`),
  KEY `IDX_2D97408BDF6CFDC9` (`updated_by_adherent_id`),
  KEY `IDX_2D97408B9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_2D97408B85C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_2D97408B9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_2D97408B9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_2D97408BCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_2D97408BDF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_formation`
--

LOCK TABLES `adherent_formation` WRITE;
/*!40000 ALTER TABLE `adherent_formation` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_formation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_formation_print_by_adherents`
--

DROP TABLE IF EXISTS `adherent_formation_print_by_adherents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_formation_print_by_adherents` (
  `formation_id` int unsigned NOT NULL,
  `adherent_id` int unsigned NOT NULL,
  PRIMARY KEY (`formation_id`,`adherent_id`),
  KEY `IDX_881E4C655200282E` (`formation_id`),
  KEY `IDX_881E4C6525F06C53` (`adherent_id`),
  CONSTRAINT `FK_881E4C6525F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_881E4C655200282E` FOREIGN KEY (`formation_id`) REFERENCES `adherent_formation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_formation_print_by_adherents`
--

LOCK TABLES `adherent_formation_print_by_adherents` WRITE;
/*!40000 ALTER TABLE `adherent_formation_print_by_adherents` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_formation_print_by_adherents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_instance_quality`
--

DROP TABLE IF EXISTS `adherent_instance_quality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_instance_quality` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `instance_quality_id` int unsigned NOT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `territorial_council_id` int unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_instance_quality_unique` (`adherent_id`,`instance_quality_id`),
  UNIQUE KEY `UNIQ_D63B17FAD17F50A6` (`uuid`),
  KEY `IDX_D63B17FA25F06C53` (`adherent_id`),
  KEY `IDX_D63B17FA9F2C3FAB` (`zone_id`),
  KEY `IDX_D63B17FAA623BBD7` (`instance_quality_id`),
  KEY `IDX_D63B17FAAAA61A99` (`territorial_council_id`),
  CONSTRAINT `FK_D63B17FA25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D63B17FA9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_D63B17FAA623BBD7` FOREIGN KEY (`instance_quality_id`) REFERENCES `instance_quality` (`id`),
  CONSTRAINT `FK_D63B17FAAAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_instance_quality`
--

LOCK TABLES `adherent_instance_quality` WRITE;
/*!40000 ALTER TABLE `adherent_instance_quality` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_instance_quality` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_mandate`
--

DROP TABLE IF EXISTS `adherent_mandate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_mandate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `committee_id` int unsigned DEFAULT NULL,
  `territorial_council_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `gender` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `begin_at` datetime NOT NULL,
  `finish_at` datetime DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quality` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_additionally_elected` tinyint(1) DEFAULT '0',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provisional` tinyint(1) NOT NULL DEFAULT '0',
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `mandate_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delegation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9C0C3D60D17F50A6` (`uuid`),
  KEY `IDX_9C0C3D6025F06C53` (`adherent_id`),
  KEY `IDX_9C0C3D60AAA61A99` (`territorial_council_id`),
  KEY `IDX_9C0C3D60ED1A100B` (`committee_id`),
  KEY `IDX_9C0C3D609DF5350C` (`created_by_administrator_id`),
  KEY `IDX_9C0C3D60CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_9C0C3D6085C9D733` (`created_by_adherent_id`),
  KEY `IDX_9C0C3D60DF6CFDC9` (`updated_by_adherent_id`),
  KEY `IDX_9C0C3D609F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_9C0C3D6025F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9C0C3D6085C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9C0C3D609DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9C0C3D609F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9C0C3D60AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9C0C3D60CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9C0C3D60DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9C0C3D60ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_mandate`
--

LOCK TABLES `adherent_mandate` WRITE;
/*!40000 ALTER TABLE `adherent_mandate` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_mandate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_message_filter_zone`
--

DROP TABLE IF EXISTS `adherent_message_filter_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_message_filter_zone` (
  `adherent_message_filter_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`adherent_message_filter_id`,`zone_id`),
  KEY `IDX_64171C029F2C3FAB` (`zone_id`),
  KEY `IDX_64171C02FBF331D5` (`adherent_message_filter_id`),
  CONSTRAINT `FK_64171C029F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64171C02FBF331D5` FOREIGN KEY (`adherent_message_filter_id`) REFERENCES `adherent_message_filters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_message_filter_zone`
--

LOCK TABLES `adherent_message_filter_zone` WRITE;
/*!40000 ALTER TABLE `adherent_message_filter_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_message_filter_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_message_filters`
--

DROP TABLE IF EXISTS `adherent_message_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_message_filters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `referent_tag_id` int unsigned DEFAULT NULL,
  `committee_id` int unsigned DEFAULT NULL,
  `adherent_segment_id` int unsigned DEFAULT NULL,
  `territorial_council_id` int unsigned DEFAULT NULL,
  `political_committee_id` int unsigned DEFAULT NULL,
  `user_list_definition_id` int unsigned DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `segment_id` int unsigned DEFAULT NULL,
  `synchronized` tinyint(1) NOT NULL DEFAULT '0',
  `dtype` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `include_adherents_no_committee` tinyint(1) DEFAULT NULL,
  `include_adherents_in_committee` tinyint(1) DEFAULT NULL,
  `include_committee_supervisors` tinyint(1) DEFAULT NULL,
  `include_committee_hosts` tinyint(1) DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_min` int DEFAULT NULL,
  `age_max` int DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interests` json DEFAULT NULL,
  `registered_since` date DEFAULT NULL,
  `registered_until` date DEFAULT NULL,
  `contact_only_volunteers` tinyint(1) DEFAULT '0',
  `contact_only_running_mates` tinyint(1) DEFAULT '0',
  `postal_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mandate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `political_function` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qualities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `include_committee_provisional_supervisors` tinyint(1) DEFAULT NULL,
  `is_certified` tinyint(1) DEFAULT NULL,
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `audience_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `renaissance_membership` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_committee_member` tinyint(1) DEFAULT NULL,
  `last_membership_since` date DEFAULT NULL,
  `last_membership_before` date DEFAULT NULL,
  `mandate_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_campus_registered` tinyint(1) DEFAULT NULL,
  `donator_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `declared_mandate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adherent_tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `elect_tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_28CA9F949C262DB3` (`referent_tag_id`),
  KEY `IDX_28CA9F949F2C3FAB` (`zone_id`),
  KEY `IDX_28CA9F94AAA61A99` (`territorial_council_id`),
  KEY `IDX_28CA9F94C7A72` (`political_committee_id`),
  KEY `IDX_28CA9F94DB296AAD` (`segment_id`),
  KEY `IDX_28CA9F94ED1A100B` (`committee_id`),
  KEY `IDX_28CA9F94F74563E3` (`user_list_definition_id`),
  KEY `IDX_28CA9F94FAF04979` (`adherent_segment_id`),
  CONSTRAINT `FK_28CA9F949C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`),
  CONSTRAINT `FK_28CA9F949F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_28CA9F94AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`),
  CONSTRAINT `FK_28CA9F94C7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`),
  CONSTRAINT `FK_28CA9F94DB296AAD` FOREIGN KEY (`segment_id`) REFERENCES `audience_segment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_28CA9F94ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_28CA9F94F74563E3` FOREIGN KEY (`user_list_definition_id`) REFERENCES `user_list_definition` (`id`),
  CONSTRAINT `FK_28CA9F94FAF04979` FOREIGN KEY (`adherent_segment_id`) REFERENCES `adherent_segment` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_message_filters`
--

LOCK TABLES `adherent_message_filters` WRITE;
/*!40000 ALTER TABLE `adherent_message_filters` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_message_filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_messages`
--

DROP TABLE IF EXISTS `adherent_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
  `filter_id` int unsigned DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `send_to_timeline` tinyint(1) NOT NULL DEFAULT '0',
  `recipient_count` int DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'platform',
  `json_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D187C183D17F50A6` (`uuid`),
  KEY `IDX_D187C183D395B25E` (`filter_id`),
  KEY `IDX_D187C183F675F31B` (`author_id`),
  CONSTRAINT `FK_D187C183D395B25E` FOREIGN KEY (`filter_id`) REFERENCES `adherent_message_filters` (`id`),
  CONSTRAINT `FK_D187C183F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_messages`
--

LOCK TABLES `adherent_messages` WRITE;
/*!40000 ALTER TABLE `adherent_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_referent_tag`
--

DROP TABLE IF EXISTS `adherent_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_referent_tag` (
  `adherent_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`adherent_id`,`referent_tag_id`),
  KEY `IDX_79E8AFFD25F06C53` (`adherent_id`),
  KEY `IDX_79E8AFFD9C262DB3` (`referent_tag_id`),
  CONSTRAINT `FK_79E8AFFD25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_79E8AFFD9C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_referent_tag`
--

LOCK TABLES `adherent_referent_tag` WRITE;
/*!40000 ALTER TABLE `adherent_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_request`
--

DROP TABLE IF EXISTS `adherent_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_request` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `token` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `amount` double NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allow_email_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `allow_mobile_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `token_used_at` datetime DEFAULT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BEE6BD11D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_request`
--

LOCK TABLES `adherent_request` WRITE;
/*!40000 ALTER TABLE `adherent_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_reset_password_tokens`
--

DROP TABLE IF EXISTS `adherent_reset_password_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_reset_password_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `value` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `expired_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_reset_password_token_account_unique` (`value`,`adherent_uuid`),
  UNIQUE KEY `adherent_reset_password_token_unique` (`value`),
  UNIQUE KEY `UNIQ_66D163EAD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_reset_password_tokens`
--

LOCK TABLES `adherent_reset_password_tokens` WRITE;
/*!40000 ALTER TABLE `adherent_reset_password_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_reset_password_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_segment`
--

DROP TABLE IF EXISTS `adherent_segment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_segment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `mailchimp_id` int DEFAULT NULL,
  `synchronized` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `segment_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9DF0C7EBD17F50A6` (`uuid`),
  KEY `IDX_9DF0C7EBF675F31B` (`author_id`),
  CONSTRAINT `FK_9DF0C7EBF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_segment`
--

LOCK TABLES `adherent_segment` WRITE;
/*!40000 ALTER TABLE `adherent_segment` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_segment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_subscription_type`
--

DROP TABLE IF EXISTS `adherent_subscription_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_subscription_type` (
  `adherent_id` int unsigned NOT NULL,
  `subscription_type_id` int unsigned NOT NULL,
  PRIMARY KEY (`adherent_id`,`subscription_type_id`),
  KEY `IDX_F93DC28A25F06C53` (`adherent_id`),
  KEY `IDX_F93DC28AB6596C08` (`subscription_type_id`),
  CONSTRAINT `FK_F93DC28A25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F93DC28AB6596C08` FOREIGN KEY (`subscription_type_id`) REFERENCES `subscription_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_subscription_type`
--

LOCK TABLES `adherent_subscription_type` WRITE;
/*!40000 ALTER TABLE `adherent_subscription_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_subscription_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_thematic_community`
--

DROP TABLE IF EXISTS `adherent_thematic_community`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_thematic_community` (
  `adherent_id` int unsigned NOT NULL,
  `thematic_community_id` int unsigned NOT NULL,
  PRIMARY KEY (`adherent_id`,`thematic_community_id`),
  KEY `IDX_DAB0B4EC1BE5825E` (`thematic_community_id`),
  KEY `IDX_DAB0B4EC25F06C53` (`adherent_id`),
  CONSTRAINT `FK_DAB0B4EC1BE5825E` FOREIGN KEY (`thematic_community_id`) REFERENCES `thematic_community` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DAB0B4EC25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_thematic_community`
--

LOCK TABLES `adherent_thematic_community` WRITE;
/*!40000 ALTER TABLE `adherent_thematic_community` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_thematic_community` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_zone`
--

DROP TABLE IF EXISTS `adherent_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_zone` (
  `adherent_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`adherent_id`,`zone_id`),
  KEY `IDX_1C14D08525F06C53` (`adherent_id`),
  KEY `IDX_1C14D0859F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_1C14D08525F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1C14D0859F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_zone`
--

LOCK TABLES `adherent_zone` WRITE;
/*!40000 ALTER TABLE `adherent_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_zone_based_role`
--

DROP TABLE IF EXISTS `adherent_zone_based_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_zone_based_role` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_390E4D38D17F50A6` (`uuid`),
  KEY `IDX_390E4D3825F06C53` (`adherent_id`),
  CONSTRAINT `FK_390E4D3825F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_zone_based_role`
--

LOCK TABLES `adherent_zone_based_role` WRITE;
/*!40000 ALTER TABLE `adherent_zone_based_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_zone_based_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_zone_based_role_zone`
--

DROP TABLE IF EXISTS `adherent_zone_based_role_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_zone_based_role_zone` (
  `adherent_zone_based_role_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`adherent_zone_based_role_id`,`zone_id`),
  KEY `IDX_1FB630BEE566D6E` (`adherent_zone_based_role_id`),
  KEY `IDX_1FB630B9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_1FB630B9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1FB630BEE566D6E` FOREIGN KEY (`adherent_zone_based_role_id`) REFERENCES `adherent_zone_based_role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_zone_based_role_zone`
--

LOCK TABLES `adherent_zone_based_role_zone` WRITE;
/*!40000 ALTER TABLE `adherent_zone_based_role_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_zone_based_role_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherents`
--

DROP TABLE IF EXISTS `adherents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `managed_area_id` int DEFAULT NULL,
  `coordinator_committee_area_id` int DEFAULT NULL,
  `media_id` bigint DEFAULT NULL,
  `procuration_managed_area_id` int DEFAULT NULL,
  `assessor_managed_area_id` int DEFAULT NULL,
  `jecoute_managed_area_id` int DEFAULT NULL,
  `senator_area_id` int DEFAULT NULL,
  `managed_district_id` int unsigned DEFAULT NULL,
  `consular_managed_area_id` int DEFAULT NULL,
  `assessor_role_id` int DEFAULT NULL,
  `senatorial_candidate_managed_area_id` int DEFAULT NULL,
  `candidate_managed_area_id` int unsigned DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `birthdate` date DEFAULT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DISABLED',
  `registered_at` datetime NOT NULL,
  `activated_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `last_logged_at` datetime DEFAULT NULL,
  `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `adherent` tinyint(1) NOT NULL DEFAULT '0',
  `remind_sent` tinyint(1) NOT NULL DEFAULT '0',
  `mandates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname_used` tinyint(1) NOT NULL DEFAULT '0',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `facebook_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_media` tinyint(1) NOT NULL,
  `nationality` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_gender` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canary_tester` tinyint(1) NOT NULL DEFAULT '0',
  `email_unsubscribed` tinyint(1) NOT NULL DEFAULT '0',
  `email_unsubscribed_at` datetime DEFAULT NULL,
  `election_results_reporter` tinyint(1) NOT NULL DEFAULT '0',
  `certified_at` datetime DEFAULT NULL,
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_reminded_at` datetime DEFAULT NULL,
  `notified_for_election` tinyint(1) NOT NULL DEFAULT '0',
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vote_inspector` tinyint(1) NOT NULL DEFAULT '0',
  `national_role` tinyint(1) NOT NULL DEFAULT '0',
  `mailchimp_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phoning_manager_role` tinyint(1) NOT NULL DEFAULT '0',
  `pap_national_manager_role` tinyint(1) NOT NULL DEFAULT '0',
  `phone_verified_at` datetime DEFAULT NULL,
  `national_communication_role` tinyint(1) NOT NULL DEFAULT '0',
  `pap_user_role` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `global_notification_sent_at` datetime DEFAULT NULL,
  `activism_zone_id` int unsigned DEFAULT NULL,
  `last_membership_donation` datetime DEFAULT NULL,
  `exclusive_membership` tinyint(1) NOT NULL DEFAULT '0',
  `territoire_progres_membership` tinyint(1) NOT NULL DEFAULT '0',
  `agir_membership` tinyint(1) NOT NULL DEFAULT '0',
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `email_status_comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_contribution_id` int unsigned DEFAULT NULL,
  `contribution_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contributed_at` datetime DEFAULT NULL,
  `exempt_from_cotisation` tinyint(1) NOT NULL DEFAULT '0',
  `tags` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `other_party_membership` tinyint(1) NOT NULL DEFAULT '0',
  `v2` tinyint(1) NOT NULL DEFAULT '0',
  `finished_adhesion_steps` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_562C7DA3B08E074E` (`email_address`),
  UNIQUE KEY `UNIQ_562C7DA3D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_562C7DA393494FA8` (`senator_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3FCCAF6D5` (`senatorial_candidate_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3E4A5D7A5` (`assessor_role_id`),
  UNIQUE KEY `UNIQ_562C7DA3E1B55931` (`assessor_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3DC184E71` (`managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3A188FE64` (`nickname`),
  UNIQUE KEY `UNIQ_562C7DA3A132C3C5` (`managed_district_id`),
  UNIQUE KEY `UNIQ_562C7DA394E3BB99` (`jecoute_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA37657F304` (`candidate_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA339054338` (`procuration_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA31A912B27` (`coordinator_committee_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3122E5FF4` (`consular_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA314E51F8D` (`last_contribution_id`),
  KEY `IDX_562C7DA3EA9FDD75` (`media_id`),
  KEY `IDX_562C7DA38C8E414F` (`activism_zone_id`),
  KEY `IDX_562C7DA39DF5350C` (`created_by_administrator_id`),
  KEY `IDX_562C7DA3CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_562C7DA3122E5FF4` FOREIGN KEY (`consular_managed_area_id`) REFERENCES `consular_managed_area` (`id`),
  CONSTRAINT `FK_562C7DA314E51F8D` FOREIGN KEY (`last_contribution_id`) REFERENCES `contribution` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA31A912B27` FOREIGN KEY (`coordinator_committee_area_id`) REFERENCES `coordinator_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA339054338` FOREIGN KEY (`procuration_managed_area_id`) REFERENCES `procuration_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA37657F304` FOREIGN KEY (`candidate_managed_area_id`) REFERENCES `candidate_managed_area` (`id`),
  CONSTRAINT `FK_562C7DA38C8E414F` FOREIGN KEY (`activism_zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_562C7DA393494FA8` FOREIGN KEY (`senator_area_id`) REFERENCES `senator_area` (`id`),
  CONSTRAINT `FK_562C7DA394E3BB99` FOREIGN KEY (`jecoute_managed_area_id`) REFERENCES `jecoute_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA39DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA3A132C3C5` FOREIGN KEY (`managed_district_id`) REFERENCES `districts` (`id`),
  CONSTRAINT `FK_562C7DA3CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA3DC184E71` FOREIGN KEY (`managed_area_id`) REFERENCES `referent_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA3E1B55931` FOREIGN KEY (`assessor_managed_area_id`) REFERENCES `assessor_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA3E4A5D7A5` FOREIGN KEY (`assessor_role_id`) REFERENCES `assessor_role_association` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA3EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`),
  CONSTRAINT `FK_562C7DA3FCCAF6D5` FOREIGN KEY (`senatorial_candidate_managed_area_id`) REFERENCES `senatorial_candidate_areas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherents`
--

LOCK TABLES `adherents` WRITE;
/*!40000 ALTER TABLE `adherents` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `administrator_export_history`
--

DROP TABLE IF EXISTS `administrator_export_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrator_export_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `administrator_id` int NOT NULL,
  `route_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` json NOT NULL,
  `exported_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_10499F014B09E92C` (`administrator_id`),
  CONSTRAINT `FK_10499F014B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrator_export_history`
--

LOCK TABLES `administrator_export_history` WRITE;
/*!40000 ALTER TABLE `administrator_export_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `administrator_export_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `administrator_role`
--

DROP TABLE IF EXISTS `administrator_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrator_role` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `group_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DEE3E68777153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrator_role`
--

LOCK TABLES `administrator_role` WRITE;
/*!40000 ALTER TABLE `administrator_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `administrator_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `administrator_role_history`
--

DROP TABLE IF EXISTS `administrator_role_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrator_role_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `administrator_id` int NOT NULL,
  `author_id` int DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_CC6926CC4B09E92C` (`administrator_id`),
  KEY `IDX_CC6926CCF675F31B` (`author_id`),
  CONSTRAINT `FK_CC6926CC4B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CC6926CCF675F31B` FOREIGN KEY (`author_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrator_role_history`
--

LOCK TABLES `administrator_role_history` WRITE;
/*!40000 ALTER TABLE `administrator_role_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `administrator_role_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `administrators`
--

DROP TABLE IF EXISTS `administrators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrators` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_authenticator_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `activated` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_73A716FB08E074E` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrators`
--

LOCK TABLES `administrators` WRITE;
/*!40000 ALTER TABLE `administrators` DISABLE KEYS */;
/*!40000 ALTER TABLE `administrators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `administrators_roles`
--

DROP TABLE IF EXISTS `administrators_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrators_roles` (
  `administrator_id` int NOT NULL,
  `administrator_role_id` int unsigned NOT NULL,
  PRIMARY KEY (`administrator_id`,`administrator_role_id`),
  KEY `IDX_9BCFB8EB4B09E92C` (`administrator_id`),
  KEY `IDX_9BCFB8EBB31C2F43` (`administrator_role_id`),
  CONSTRAINT `FK_9BCFB8EB4B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9BCFB8EBB31C2F43` FOREIGN KEY (`administrator_role_id`) REFERENCES `administrator_role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrators_roles`
--

LOCK TABLES `administrators_roles` WRITE;
/*!40000 ALTER TABLE `administrators_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `administrators_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `algolia_candidature`
--

DROP TABLE IF EXISTS `algolia_candidature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `algolia_candidature` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `algolia_candidature`
--

LOCK TABLES `algolia_candidature` WRITE;
/*!40000 ALTER TABLE `algolia_candidature` DISABLE KEYS */;
/*!40000 ALTER TABLE `algolia_candidature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `algolia_je_mengage_timeline_feed`
--

DROP TABLE IF EXISTS `algolia_je_mengage_timeline_feed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `algolia_je_mengage_timeline_feed` (
  `object_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `algolia_je_mengage_timeline_feed`
--

LOCK TABLES `algolia_je_mengage_timeline_feed` WRITE;
/*!40000 ALTER TABLE `algolia_je_mengage_timeline_feed` DISABLE KEYS */;
/*!40000 ALTER TABLE `algolia_je_mengage_timeline_feed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `application_request_running_mate`
--

DROP TABLE IF EXISTS `application_request_running_mate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_request_running_mate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `curriculum_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_local_association_member` tinyint(1) NOT NULL DEFAULT '0',
  `local_association_domain` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_political_activist` tinyint(1) NOT NULL DEFAULT '0',
  `political_activist_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_previous_elected_official` tinyint(1) NOT NULL DEFAULT '0',
  `previous_elected_official_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `favorite_theme_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `professional_assets` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `favorite_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `profession` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_favorite_theme` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taken_for_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `displayed` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D1D60956D17F50A6` (`uuid`),
  KEY `IDX_D1D6095625F06C53` (`adherent_id`),
  CONSTRAINT `FK_D1D6095625F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application_request_running_mate`
--

LOCK TABLES `application_request_running_mate` WRITE;
/*!40000 ALTER TABLE `application_request_running_mate` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_request_running_mate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `application_request_tag`
--

DROP TABLE IF EXISTS `application_request_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_request_tag` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application_request_tag`
--

LOCK TABLES `application_request_tag` WRITE;
/*!40000 ALTER TABLE `application_request_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_request_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `application_request_technical_skill`
--

DROP TABLE IF EXISTS `application_request_technical_skill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_request_technical_skill` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application_request_technical_skill`
--

LOCK TABLES `application_request_technical_skill` WRITE;
/*!40000 ALTER TABLE `application_request_technical_skill` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_request_technical_skill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `application_request_theme`
--

DROP TABLE IF EXISTS `application_request_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_request_theme` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application_request_theme`
--

LOCK TABLES `application_request_theme` WRITE;
/*!40000 ALTER TABLE `application_request_theme` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_request_theme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `application_request_volunteer`
--

DROP TABLE IF EXISTS `application_request_volunteer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_request_volunteer` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `custom_technical_skills` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_previous_campaign_member` tinyint(1) NOT NULL,
  `previous_campaign_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `share_associative_commitment` tinyint(1) NOT NULL,
  `associative_commitment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `favorite_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `profession` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_favorite_theme` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taken_for_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `displayed` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_11396570D17F50A6` (`uuid`),
  KEY `IDX_1139657025F06C53` (`adherent_id`),
  CONSTRAINT `FK_1139657025F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application_request_volunteer`
--

LOCK TABLES `application_request_volunteer` WRITE;
/*!40000 ALTER TABLE `application_request_volunteer` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_request_volunteer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article_proposal_theme`
--

DROP TABLE IF EXISTS `article_proposal_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_proposal_theme` (
  `article_id` bigint NOT NULL,
  `proposal_theme_id` int NOT NULL,
  PRIMARY KEY (`article_id`,`proposal_theme_id`),
  KEY `IDX_F6B9A2217294869C` (`article_id`),
  KEY `IDX_F6B9A221B85948AF` (`proposal_theme_id`),
  CONSTRAINT `FK_F6B9A2217294869C` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F6B9A221B85948AF` FOREIGN KEY (`proposal_theme_id`) REFERENCES `proposals_themes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `media_id` bigint DEFAULT NULL,
  `published_at` datetime NOT NULL,
  `published` tinyint(1) NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `for_renaissance` tinyint(1) NOT NULL DEFAULT '0',
  `json_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BFDD3168989D9B62` (`slug`),
  KEY `IDX_BFDD316812469DE2` (`category_id`),
  KEY `IDX_BFDD3168EA9FDD75` (`media_id`),
  CONSTRAINT `FK_BFDD316812469DE2` FOREIGN KEY (`category_id`) REFERENCES `articles_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BFDD3168EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position` smallint NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cta_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DE004A0E989D9B62` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles_categories`
--

LOCK TABLES `articles_categories` WRITE;
/*!40000 ALTER TABLE `articles_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `articles_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessor_managed_areas`
--

DROP TABLE IF EXISTS `assessor_managed_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessor_managed_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessor_managed_areas`
--

LOCK TABLES `assessor_managed_areas` WRITE;
/*!40000 ALTER TABLE `assessor_managed_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessor_managed_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessor_requests`
--

DROP TABLE IF EXISTS `assessor_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessor_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vote_place_id` int unsigned DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date NOT NULL,
  `birth_city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vote_city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `office_number` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:phone_number)',
  `assessor_city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed` tinyint(1) NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `assessor_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assessor_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reachable` tinyint(1) NOT NULL DEFAULT '0',
  `voter_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `election_rounds` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FR',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_26BC800D17F50A6` (`uuid`),
  KEY `IDX_26BC800F3F90B30` (`vote_place_id`),
  CONSTRAINT `FK_26BC800F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `election_vote_place` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessor_requests`
--

LOCK TABLES `assessor_requests` WRITE;
/*!40000 ALTER TABLE `assessor_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessor_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessor_requests_vote_place_wishes`
--

DROP TABLE IF EXISTS `assessor_requests_vote_place_wishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessor_requests_vote_place_wishes` (
  `assessor_request_id` int unsigned NOT NULL,
  `vote_place_id` int unsigned NOT NULL,
  PRIMARY KEY (`assessor_request_id`,`vote_place_id`),
  KEY `IDX_1517FC131BD1903D` (`assessor_request_id`),
  KEY `IDX_1517FC13F3F90B30` (`vote_place_id`),
  CONSTRAINT `FK_1517FC131BD1903D` FOREIGN KEY (`assessor_request_id`) REFERENCES `assessor_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1517FC13F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `election_vote_place` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessor_requests_vote_place_wishes`
--

LOCK TABLES `assessor_requests_vote_place_wishes` WRITE;
/*!40000 ALTER TABLE `assessor_requests_vote_place_wishes` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessor_requests_vote_place_wishes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assessor_role_association`
--

DROP TABLE IF EXISTS `assessor_role_association`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessor_role_association` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vote_place_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B93395C2F3F90B30` (`vote_place_id`),
  CONSTRAINT `FK_B93395C2F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `election_vote_place` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessor_role_association`
--

LOCK TABLES `assessor_role_association` WRITE;
/*!40000 ALTER TABLE `assessor_role_association` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessor_role_association` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audience`
--

DROP TABLE IF EXISTS `audience`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audience` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_min` int DEFAULT NULL,
  `age_max` int DEFAULT NULL,
  `registered_since` date DEFAULT NULL,
  `registered_until` date DEFAULT NULL,
  `is_committee_member` tinyint(1) DEFAULT NULL,
  `is_certified` tinyint(1) DEFAULT NULL,
  `has_email_subscription` tinyint(1) DEFAULT NULL,
  `has_sms_subscription` tinyint(1) DEFAULT NULL,
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `renaissance_membership` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FDCD9418D17F50A6` (`uuid`),
  KEY `IDX_FDCD94189F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_FDCD94189F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audience`
--

LOCK TABLES `audience` WRITE;
/*!40000 ALTER TABLE `audience` DISABLE KEYS */;
/*!40000 ALTER TABLE `audience` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audience_segment`
--

DROP TABLE IF EXISTS `audience_segment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audience_segment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `filter_id` int unsigned NOT NULL,
  `author_id` int unsigned NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `recipient_count` int unsigned DEFAULT NULL,
  `synchronized` tinyint(1) NOT NULL DEFAULT '0',
  `mailchimp_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C5C2F52FD395B25E` (`filter_id`),
  UNIQUE KEY `UNIQ_C5C2F52FD17F50A6` (`uuid`),
  KEY `IDX_C5C2F52FF675F31B` (`author_id`),
  CONSTRAINT `FK_C5C2F52FD395B25E` FOREIGN KEY (`filter_id`) REFERENCES `adherent_message_filters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C5C2F52FF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audience_segment`
--

LOCK TABLES `audience_segment` WRITE;
/*!40000 ALTER TABLE `audience_segment` DISABLE KEYS */;
/*!40000 ALTER TABLE `audience_segment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audience_snapshot`
--

DROP TABLE IF EXISTS `audience_snapshot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audience_snapshot` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_min` int DEFAULT NULL,
  `age_max` int DEFAULT NULL,
  `registered_since` date DEFAULT NULL,
  `registered_until` date DEFAULT NULL,
  `is_committee_member` tinyint(1) DEFAULT NULL,
  `is_certified` tinyint(1) DEFAULT NULL,
  `has_email_subscription` tinyint(1) DEFAULT NULL,
  `has_sms_subscription` tinyint(1) DEFAULT NULL,
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `renaissance_membership` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BA99FEBBD17F50A6` (`uuid`),
  KEY `IDX_BA99FEBB9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_BA99FEBB9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audience_snapshot`
--

LOCK TABLES `audience_snapshot` WRITE;
/*!40000 ALTER TABLE `audience_snapshot` DISABLE KEYS */;
/*!40000 ALTER TABLE `audience_snapshot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audience_snapshot_zone`
--

DROP TABLE IF EXISTS `audience_snapshot_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audience_snapshot_zone` (
  `audience_snapshot_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`audience_snapshot_id`,`zone_id`),
  KEY `IDX_10882DC09F2C3FAB` (`zone_id`),
  KEY `IDX_10882DC0ACA633A8` (`audience_snapshot_id`),
  CONSTRAINT `FK_10882DC09F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_10882DC0ACA633A8` FOREIGN KEY (`audience_snapshot_id`) REFERENCES `audience_snapshot` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audience_snapshot_zone`
--

LOCK TABLES `audience_snapshot_zone` WRITE;
/*!40000 ALTER TABLE `audience_snapshot_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `audience_snapshot_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audience_zone`
--

DROP TABLE IF EXISTS `audience_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audience_zone` (
  `audience_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`audience_id`,`zone_id`),
  KEY `IDX_A719804F848CC616` (`audience_id`),
  KEY `IDX_A719804F9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_A719804F848CC616` FOREIGN KEY (`audience_id`) REFERENCES `audience` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A719804F9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audience_zone`
--

LOCK TABLES `audience_zone` WRITE;
/*!40000 ALTER TABLE `audience_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `audience_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banned_adherent`
--

DROP TABLE IF EXISTS `banned_adherent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banned_adherent` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B85ACFECD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banned_adherent`
--

LOCK TABLES `banned_adherent` WRITE;
/*!40000 ALTER TABLE `banned_adherent` DISABLE KEYS */;
/*!40000 ALTER TABLE `banned_adherent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `biography_executive_office_member`
--

DROP TABLE IF EXISTS `biography_executive_office_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `biography_executive_office_member` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `job` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `executive_officer` tinyint(1) NOT NULL DEFAULT '0',
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `facebook_profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linked_in_profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deputy_general_delegate` tinyint(1) NOT NULL DEFAULT '0',
  `president` tinyint(1) NOT NULL DEFAULT '0',
  `for_renaissance` tinyint(1) NOT NULL DEFAULT '0',
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_44A61059989D9B62` (`slug`),
  UNIQUE KEY `UNIQ_44A61059D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `biography_executive_office_member`
--

LOCK TABLES `biography_executive_office_member` WRITE;
/*!40000 ALTER TABLE `biography_executive_office_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `biography_executive_office_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `board_member`
--

DROP TABLE IF EXISTS `board_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `board_member` (
  `id` int NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `area` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DCFABEDF25F06C53` (`adherent_id`),
  CONSTRAINT `FK_DCFABEDF25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `board_member_roles` (
  `board_member_id` int NOT NULL,
  `role_id` int unsigned NOT NULL,
  PRIMARY KEY (`board_member_id`,`role_id`),
  KEY `IDX_1DD1E043C7BA2FD5` (`board_member_id`),
  KEY `IDX_1DD1E043D60322AC` (`role_id`),
  CONSTRAINT `FK_1DD1E043C7BA2FD5` FOREIGN KEY (`board_member_id`) REFERENCES `board_member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1DD1E043D60322AC` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `board_member_roles`
--

LOCK TABLES `board_member_roles` WRITE;
/*!40000 ALTER TABLE `board_member_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `board_member_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campus_registration`
--

DROP TABLE IF EXISTS `campus_registration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campus_registration` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `event_maker_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `campus_event_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_maker_order_uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registered_at` datetime DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_30249D7BD17F50A6` (`uuid`),
  KEY `IDX_30249D7B25F06C53` (`adherent_id`),
  CONSTRAINT `FK_30249D7B25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campus_registration`
--

LOCK TABLES `campus_registration` WRITE;
/*!40000 ALTER TABLE `campus_registration` DISABLE KEYS */;
/*!40000 ALTER TABLE `campus_registration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_managed_area`
--

DROP TABLE IF EXISTS `candidate_managed_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidate_managed_area` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C604D2EA9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_C604D2EA9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_managed_area`
--

LOCK TABLES `candidate_managed_area` WRITE;
/*!40000 ALTER TABLE `candidate_managed_area` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate_managed_area` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certification_request`
--

DROP TABLE IF EXISTS `certification_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certification_request` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `processed_by_id` int DEFAULT NULL,
  `found_duplicated_adherent_id` int unsigned DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_mime_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `block_reason` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_block_reason` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `block_comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `refusal_reason` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_refusal_reason` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `refusal_comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ocr_payload` json DEFAULT NULL,
  `ocr_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocr_result` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6E7481A9D17F50A6` (`uuid`),
  KEY `IDX_6E7481A925F06C53` (`adherent_id`),
  KEY `IDX_6E7481A92FFD4FD3` (`processed_by_id`),
  KEY `IDX_6E7481A96EA98020` (`found_duplicated_adherent_id`),
  CONSTRAINT `FK_6E7481A925F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6E7481A92FFD4FD3` FOREIGN KEY (`processed_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_6E7481A96EA98020` FOREIGN KEY (`found_duplicated_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certification_request`
--

LOCK TABLES `certification_request` WRITE;
/*!40000 ALTER TABLE `certification_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `certification_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chez_vous_cities`
--

DROP TABLE IF EXISTS `chez_vous_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chez_vous_cities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_codes` json NOT NULL,
  `insee_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` float(10,6) NOT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) NOT NULL COMMENT '(DC2Type:geo_point)',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A42D9BED15A3C1BC` (`insee_code`),
  UNIQUE KEY `UNIQ_A42D9BED989D9B62` (`slug`),
  KEY `IDX_A42D9BEDAE80F5DF` (`department_id`),
  CONSTRAINT `FK_A42D9BEDAE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `chez_vous_departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chez_vous_cities`
--

LOCK TABLES `chez_vous_cities` WRITE;
/*!40000 ALTER TABLE `chez_vous_cities` DISABLE KEYS */;
/*!40000 ALTER TABLE `chez_vous_cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chez_vous_departments`
--

DROP TABLE IF EXISTS `chez_vous_departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chez_vous_departments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `region_id` int unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_29E7DD5777153098` (`code`),
  KEY `IDX_29E7DD5798260155` (`region_id`),
  CONSTRAINT `FK_29E7DD5798260155` FOREIGN KEY (`region_id`) REFERENCES `chez_vous_regions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chez_vous_departments`
--

LOCK TABLES `chez_vous_departments` WRITE;
/*!40000 ALTER TABLE `chez_vous_departments` DISABLE KEYS */;
/*!40000 ALTER TABLE `chez_vous_departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chez_vous_markers`
--

DROP TABLE IF EXISTS `chez_vous_markers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chez_vous_markers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` float(10,6) NOT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) NOT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  KEY `IDX_452F890F8BAC62AF` (`city_id`),
  CONSTRAINT `FK_452F890F8BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `chez_vous_cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chez_vous_markers`
--

LOCK TABLES `chez_vous_markers` WRITE;
/*!40000 ALTER TABLE `chez_vous_markers` DISABLE KEYS */;
/*!40000 ALTER TABLE `chez_vous_markers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chez_vous_measure_types`
--

DROP TABLE IF EXISTS `chez_vous_measure_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chez_vous_measure_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `source_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oldolf_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eligibility_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B80D46F577153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chez_vous_measure_types`
--

LOCK TABLES `chez_vous_measure_types` WRITE;
/*!40000 ALTER TABLE `chez_vous_measure_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `chez_vous_measure_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chez_vous_measures`
--

DROP TABLE IF EXISTS `chez_vous_measures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chez_vous_measures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int unsigned NOT NULL,
  `type_id` int unsigned NOT NULL,
  `payload` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chez_vous_measures_city_type_unique` (`city_id`,`type_id`),
  KEY `IDX_E6E8973E8BAC62AF` (`city_id`),
  KEY `IDX_E6E8973EC54C8C93` (`type_id`),
  CONSTRAINT `FK_E6E8973E8BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `chez_vous_cities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E6E8973EC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `chez_vous_measure_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chez_vous_measures`
--

LOCK TABLES `chez_vous_measures` WRITE;
/*!40000 ALTER TABLE `chez_vous_measures` DISABLE KEYS */;
/*!40000 ALTER TABLE `chez_vous_measures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chez_vous_regions`
--

DROP TABLE IF EXISTS `chez_vous_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chez_vous_regions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A6C12FCC77153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chez_vous_regions`
--

LOCK TABLES `chez_vous_regions` WRITE;
/*!40000 ALTER TABLE `chez_vous_regions` DISABLE KEYS */;
/*!40000 ALTER TABLE `chez_vous_regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `insee_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D95DB16B15A3C1BC` (`insee_code`),
  KEY `IDX_D95DB16BAE80F5DF` (`department_id`),
  CONSTRAINT `FK_D95DB16BAE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `department` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cities`
--

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;
/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clarifications`
--

DROP TABLE IF EXISTS `clarifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clarifications` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2FAB8972989D9B62` (`slug`),
  KEY `IDX_2FAB8972EA9FDD75` (`media_id`),
  CONSTRAINT `FK_2FAB8972EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clarifications`
--

LOCK TABLES `clarifications` WRITE;
/*!40000 ALTER TABLE `clarifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `clarifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cms_block`
--

DROP TABLE IF EXISTS `cms_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_block` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AD680C0E5E237E06` (`name`),
  KEY `IDX_AD680C0E9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_AD680C0ECF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_AD680C0E9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_AD680C0ECF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cms_block`
--

LOCK TABLES `cms_block` WRITE;
/*!40000 ALTER TABLE `cms_block` DISABLE KEYS */;
/*!40000 ALTER TABLE `cms_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `command_history`
--

DROP TABLE IF EXISTS `command_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `command_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `command_history`
--

LOCK TABLES `command_history` WRITE;
/*!40000 ALTER TABLE `command_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `command_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commitment`
--

DROP TABLE IF EXISTS `commitment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commitment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F3E0CCBBD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commitment`
--

LOCK TABLES `commitment` WRITE;
/*!40000 ALTER TABLE `commitment` DISABLE KEYS */;
/*!40000 ALTER TABLE `commitment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_candidacies_group`
--

DROP TABLE IF EXISTS `committee_candidacies_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AF772F42D17F50A6` (`uuid`),
  KEY `IDX_AF772F42A708DAFF` (`election_id`),
  CONSTRAINT `FK_AF772F42A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `committee_election` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_candidacies_group`
--

LOCK TABLES `committee_candidacies_group` WRITE;
/*!40000 ALTER TABLE `committee_candidacies_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_candidacies_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_candidacy`
--

DROP TABLE IF EXISTS `committee_candidacy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_candidacy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `committee_election_id` int unsigned NOT NULL,
  `committee_membership_id` int unsigned NOT NULL,
  `candidacies_group_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `faith_statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_public_faith_statement` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9A04454D17F50A6` (`uuid`),
  KEY `IDX_9A044544E891720` (`committee_election_id`),
  KEY `IDX_9A04454FC1537C1` (`candidacies_group_id`),
  KEY `IDX_9A04454FCC6DA91` (`committee_membership_id`),
  CONSTRAINT `FK_9A044544E891720` FOREIGN KEY (`committee_election_id`) REFERENCES `committee_election` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9A04454FC1537C1` FOREIGN KEY (`candidacies_group_id`) REFERENCES `committee_candidacies_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9A04454FCC6DA91` FOREIGN KEY (`committee_membership_id`) REFERENCES `committees_memberships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_candidacy`
--

LOCK TABLES `committee_candidacy` WRITE;
/*!40000 ALTER TABLE `committee_candidacy` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_candidacy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_candidacy_invitation`
--

DROP TABLE IF EXISTS `committee_candidacy_invitation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_candidacy_invitation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `membership_id` int unsigned NOT NULL,
  `candidacy_id` int unsigned NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `declined_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_368B0161D17F50A6` (`uuid`),
  KEY `IDX_368B01611FB354CD` (`membership_id`),
  KEY `IDX_368B016159B22434` (`candidacy_id`),
  CONSTRAINT `FK_368B01611FB354CD` FOREIGN KEY (`membership_id`) REFERENCES `committees_memberships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_368B016159B22434` FOREIGN KEY (`candidacy_id`) REFERENCES `committee_candidacy` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_candidacy_invitation`
--

LOCK TABLES `committee_candidacy_invitation` WRITE;
/*!40000 ALTER TABLE `committee_candidacy_invitation` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_candidacy_invitation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_election`
--

DROP TABLE IF EXISTS `committee_election`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_election` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `committee_id` int unsigned NOT NULL,
  `designation_id` int unsigned DEFAULT NULL,
  `adherent_notified` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2CA406E5D17F50A6` (`uuid`),
  KEY `IDX_2CA406E5ED1A100B` (`committee_id`),
  KEY `IDX_2CA406E5FAC7D83F` (`designation_id`),
  CONSTRAINT `FK_2CA406E5ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2CA406E5FAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_election`
--

LOCK TABLES `committee_election` WRITE;
/*!40000 ALTER TABLE `committee_election` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_election` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_feed_item`
--

DROP TABLE IF EXISTS `committee_feed_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_feed_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `committee_id` int unsigned DEFAULT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `event_id` int unsigned DEFAULT NULL,
  `item_type` varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4F1CDC80D17F50A6` (`uuid`),
  KEY `IDX_4F1CDC8071F7E88B` (`event_id`),
  KEY `IDX_4F1CDC80ED1A100B` (`committee_id`),
  KEY `IDX_4F1CDC80F675F31B` (`author_id`),
  CONSTRAINT `FK_4F1CDC8071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4F1CDC80ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4F1CDC80F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_feed_item`
--

LOCK TABLES `committee_feed_item` WRITE;
/*!40000 ALTER TABLE `committee_feed_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_feed_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_feed_item_user_documents`
--

DROP TABLE IF EXISTS `committee_feed_item_user_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_feed_item_user_documents` (
  `committee_feed_item_id` int unsigned NOT NULL,
  `user_document_id` int unsigned NOT NULL,
  PRIMARY KEY (`committee_feed_item_id`,`user_document_id`),
  KEY `IDX_D269D0AA6A24B1A2` (`user_document_id`),
  KEY `IDX_D269D0AABEF808A3` (`committee_feed_item_id`),
  CONSTRAINT `FK_D269D0AA6A24B1A2` FOREIGN KEY (`user_document_id`) REFERENCES `user_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D269D0AABEF808A3` FOREIGN KEY (`committee_feed_item_id`) REFERENCES `committee_feed_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_feed_item_user_documents`
--

LOCK TABLES `committee_feed_item_user_documents` WRITE;
/*!40000 ALTER TABLE `committee_feed_item_user_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_feed_item_user_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_membership_history_referent_tag`
--

DROP TABLE IF EXISTS `committee_membership_history_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_membership_history_referent_tag` (
  `committee_membership_history_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`committee_membership_history_id`,`referent_tag_id`),
  KEY `IDX_B6A8C718123C64CE` (`committee_membership_history_id`),
  KEY `IDX_B6A8C7189C262DB3` (`referent_tag_id`),
  CONSTRAINT `FK_B6A8C718123C64CE` FOREIGN KEY (`committee_membership_history_id`) REFERENCES `committees_membership_histories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B6A8C7189C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_membership_history_referent_tag`
--

LOCK TABLES `committee_membership_history_referent_tag` WRITE;
/*!40000 ALTER TABLE `committee_membership_history_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_membership_history_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_merge_histories`
--

DROP TABLE IF EXISTS `committee_merge_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_merge_histories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source_committee_id` int unsigned NOT NULL,
  `destination_committee_id` int unsigned NOT NULL,
  `merged_by_id` int DEFAULT NULL,
  `reverted_by_id` int DEFAULT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `reverted_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `committee_merge_histories_date_idx` (`date`),
  KEY `committee_merge_histories_destination_committee_id_idx` (`destination_committee_id`),
  KEY `committee_merge_histories_source_committee_id_idx` (`source_committee_id`),
  KEY `IDX_BB95FBBC50FA8329` (`merged_by_id`),
  KEY `IDX_BB95FBBCA8E1562` (`reverted_by_id`),
  CONSTRAINT `FK_BB95FBBC3BF0CCB3` FOREIGN KEY (`source_committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BB95FBBC50FA8329` FOREIGN KEY (`merged_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BB95FBBC5C34CBC4` FOREIGN KEY (`destination_committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BB95FBBCA8E1562` FOREIGN KEY (`reverted_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_merge_histories`
--

LOCK TABLES `committee_merge_histories` WRITE;
/*!40000 ALTER TABLE `committee_merge_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_merge_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_merge_histories_merged_memberships`
--

DROP TABLE IF EXISTS `committee_merge_histories_merged_memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_merge_histories_merged_memberships` (
  `committee_merge_history_id` int unsigned NOT NULL,
  `committee_membership_id` int unsigned NOT NULL,
  PRIMARY KEY (`committee_merge_history_id`,`committee_membership_id`),
  UNIQUE KEY `UNIQ_CB8E336FFCC6DA91` (`committee_membership_id`),
  KEY `IDX_CB8E336F9379ED92` (`committee_merge_history_id`),
  CONSTRAINT `FK_CB8E336F9379ED92` FOREIGN KEY (`committee_merge_history_id`) REFERENCES `committee_merge_histories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CB8E336FFCC6DA91` FOREIGN KEY (`committee_membership_id`) REFERENCES `committees_memberships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_merge_histories_merged_memberships`
--

LOCK TABLES `committee_merge_histories_merged_memberships` WRITE;
/*!40000 ALTER TABLE `committee_merge_histories_merged_memberships` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_merge_histories_merged_memberships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_provisional_supervisor`
--

DROP TABLE IF EXISTS `committee_provisional_supervisor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_provisional_supervisor` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `committee_id` int unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E394C3D425F06C53` (`adherent_id`),
  KEY `IDX_E394C3D4ED1A100B` (`committee_id`),
  CONSTRAINT `FK_E394C3D425F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_E394C3D4ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_provisional_supervisor`
--

LOCK TABLES `committee_provisional_supervisor` WRITE;
/*!40000 ALTER TABLE `committee_provisional_supervisor` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_provisional_supervisor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_referent_tag`
--

DROP TABLE IF EXISTS `committee_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_referent_tag` (
  `committee_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`committee_id`,`referent_tag_id`),
  KEY `IDX_285EB1C59C262DB3` (`referent_tag_id`),
  KEY `IDX_285EB1C5ED1A100B` (`committee_id`),
  CONSTRAINT `FK_285EB1C59C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_285EB1C5ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_referent_tag`
--

LOCK TABLES `committee_referent_tag` WRITE;
/*!40000 ALTER TABLE `committee_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_zone`
--

DROP TABLE IF EXISTS `committee_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_zone` (
  `committee_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`committee_id`,`zone_id`),
  KEY `IDX_37C5F2249F2C3FAB` (`zone_id`),
  KEY `IDX_37C5F224ED1A100B` (`committee_id`),
  CONSTRAINT `FK_37C5F2249F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_37C5F224ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_zone`
--

LOCK TABLES `committee_zone` WRITE;
/*!40000 ALTER TABLE `committee_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committees`
--

DROP TABLE IF EXISTS `committees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committees` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `current_designation_id` int unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `canonical_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebook_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_at` datetime DEFAULT NULL,
  `refused_at` datetime DEFAULT NULL,
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `members_count` smallint unsigned NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `name_locked` tinyint(1) NOT NULL DEFAULT '0',
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mailchimp_id` int DEFAULT NULL,
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `version` smallint unsigned NOT NULL DEFAULT '2',
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `sympathizers_count` smallint unsigned NOT NULL DEFAULT '0',
  `animator_id` int unsigned DEFAULT NULL,
  `members_em_count` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A36198C6D17F50A6` (`uuid`),
  KEY `IDX_A36198C67B00651C` (`status`),
  KEY `IDX_A36198C6B4D2A5D1` (`current_designation_id`),
  KEY `IDX_A36198C685C9D733` (`created_by_adherent_id`),
  KEY `IDX_A36198C6DF6CFDC9` (`updated_by_adherent_id`),
  KEY `IDX_A36198C6BF1CD3C3` (`version`),
  KEY `IDX_A36198C69DF5350C` (`created_by_administrator_id`),
  KEY `IDX_A36198C6CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_A36198C670FBD26D` (`animator_id`),
  CONSTRAINT `FK_A36198C670FBD26D` FOREIGN KEY (`animator_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A36198C685C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A36198C69DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A36198C6B4D2A5D1` FOREIGN KEY (`current_designation_id`) REFERENCES `designation` (`id`),
  CONSTRAINT `FK_A36198C6CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A36198C6DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committees`
--

LOCK TABLES `committees` WRITE;
/*!40000 ALTER TABLE `committees` DISABLE KEYS */;
/*!40000 ALTER TABLE `committees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committees_membership_histories`
--

DROP TABLE IF EXISTS `committees_membership_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committees_membership_histories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `committee_id` int unsigned DEFAULT NULL,
  `adherent_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `action` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `privilege` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `committees_membership_histories_action_idx` (`action`),
  KEY `committees_membership_histories_adherent_uuid_idx` (`adherent_uuid`),
  KEY `committees_membership_histories_date_idx` (`date`),
  KEY `IDX_4BBAE2C7ED1A100B` (`committee_id`),
  CONSTRAINT `FK_4BBAE2C7ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committees_membership_histories`
--

LOCK TABLES `committees_membership_histories` WRITE;
/*!40000 ALTER TABLE `committees_membership_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `committees_membership_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committees_memberships`
--

DROP TABLE IF EXISTS `committees_memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committees_memberships` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `committee_id` int unsigned NOT NULL,
  `privilege` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `joined_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `enable_vote` tinyint(1) DEFAULT NULL,
  `trigger` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_has_joined_committee` (`adherent_id`,`committee_id`),
  UNIQUE KEY `UNIQ_E7A6490ED17F50A6` (`uuid`),
  UNIQUE KEY `adherent_votes_in_committee` (`adherent_id`,`enable_vote`),
  KEY `committees_memberships_role_idx` (`privilege`),
  KEY `IDX_E7A6490E25F06C53` (`adherent_id`),
  KEY `IDX_E7A6490EED1A100B` (`committee_id`),
  CONSTRAINT `FK_E7A6490E25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_E7A6490EED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committees_memberships`
--

LOCK TABLES `committees_memberships` WRITE;
/*!40000 ALTER TABLE `committees_memberships` DISABLE KEYS */;
/*!40000 ALTER TABLE `committees_memberships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consular_district`
--

DROP TABLE IF EXISTS `consular_district`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consular_district` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `countries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` smallint NOT NULL,
  `points` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_77152B8877153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consular_district`
--

LOCK TABLES `consular_district` WRITE;
/*!40000 ALTER TABLE `consular_district` DISABLE KEYS */;
/*!40000 ALTER TABLE `consular_district` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consular_managed_area`
--

DROP TABLE IF EXISTS `consular_managed_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consular_managed_area` (
  `id` int NOT NULL AUTO_INCREMENT,
  `consular_district_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7937A51292CA96FD` (`consular_district_id`),
  CONSTRAINT `FK_7937A51292CA96FD` FOREIGN KEY (`consular_district_id`) REFERENCES `consular_district` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consular_managed_area`
--

LOCK TABLES `consular_managed_area` WRITE;
/*!40000 ALTER TABLE `consular_managed_area` DISABLE KEYS */;
/*!40000 ALTER TABLE `consular_managed_area` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consultation`
--

DROP TABLE IF EXISTS `consultation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_964685A6D17F50A6` (`uuid`),
  KEY `IDX_964685A69DF5350C` (`created_by_administrator_id`),
  KEY `IDX_964685A6CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_964685A69DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_964685A6CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultation`
--

LOCK TABLES `consultation` WRITE;
/*!40000 ALTER TABLE `consultation` DISABLE KEYS */;
/*!40000 ALTER TABLE `consultation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `birthdate` date DEFAULT NULL,
  `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail_contact` tinyint(1) NOT NULL DEFAULT '0',
  `phone_contact` tinyint(1) NOT NULL DEFAULT '0',
  `cgu_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `interests_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4C62E638B08E074E` (`email_address`),
  UNIQUE KEY `UNIQ_4C62E638D17F50A6` (`uuid`),
  KEY `IDX_4C62E63825F06C53` (`adherent_id`),
  CONSTRAINT `FK_4C62E63825F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contribution`
--

DROP TABLE IF EXISTS `contribution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contribution` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `gocardless_customer_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_bank_account_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_bank_account_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `gocardless_mandate_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_mandate_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_subscription_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_subscription_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EA351E15D17F50A6` (`uuid`),
  KEY `IDX_EA351E1525F06C53` (`adherent_id`),
  CONSTRAINT `FK_EA351E1525F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contribution`
--

LOCK TABLES `contribution` WRITE;
/*!40000 ALTER TABLE `contribution` DISABLE KEYS */;
/*!40000 ALTER TABLE `contribution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contribution_payment`
--

DROP TABLE IF EXISTS `contribution_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contribution_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `ohme_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime DEFAULT NULL,
  `method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` int NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2C09F4CCD17F50A6` (`uuid`),
  KEY `IDX_2C09F4CC25F06C53` (`adherent_id`),
  CONSTRAINT `FK_2C09F4CC25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contribution_payment`
--

LOCK TABLES `contribution_payment` WRITE;
/*!40000 ALTER TABLE `contribution_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `contribution_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contribution_revenue_declaration`
--

DROP TABLE IF EXISTS `contribution_revenue_declaration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contribution_revenue_declaration` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `amount` int NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_84181073D17F50A6` (`uuid`),
  KEY `IDX_8418107325F06C53` (`adherent_id`),
  CONSTRAINT `FK_8418107325F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contribution_revenue_declaration`
--

LOCK TABLES `contribution_revenue_declaration` WRITE;
/*!40000 ALTER TABLE `contribution_revenue_declaration` DISABLE KEYS */;
/*!40000 ALTER TABLE `contribution_revenue_declaration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coordinator_managed_areas`
--

DROP TABLE IF EXISTS `coordinator_managed_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coordinator_managed_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `sector` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_search_results` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_38973E54EA9FDD75` (`media_id`),
  CONSTRAINT `FK_38973E54EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_search_results`
--

LOCK TABLES `custom_search_results` WRITE;
/*!40000 ALTER TABLE `custom_search_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_search_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `region_id` int unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CD1DE18A77153098` (`code`),
  KEY `IDX_CD1DE18A98260155` (`region_id`),
  CONSTRAINT `FK_CD1DE18A98260155` FOREIGN KEY (`region_id`) REFERENCES `region` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department`
--

LOCK TABLES `department` WRITE;
/*!40000 ALTER TABLE `department` DISABLE KEYS */;
/*!40000 ALTER TABLE `department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_site`
--

DROP TABLE IF EXISTS `department_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department_site` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `json_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CB596EB1989D9B62` (`slug`),
  UNIQUE KEY `UNIQ_CB596EB1D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_CB596EB19F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_CB596EB19F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_site`
--

LOCK TABLES `department_site` WRITE;
/*!40000 ALTER TABLE `department_site` DISABLE KEYS */;
/*!40000 ALTER TABLE `department_site` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designation`
--

DROP TABLE IF EXISTS `designation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `global_zones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `candidacy_start_date` datetime DEFAULT NULL,
  `candidacy_end_date` datetime DEFAULT NULL,
  `vote_start_date` datetime DEFAULT NULL,
  `vote_end_date` datetime DEFAULT NULL,
  `result_display_delay` smallint unsigned NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `additional_round_duration` smallint unsigned NOT NULL,
  `lock_period_threshold` smallint unsigned NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `limited` tinyint(1) NOT NULL DEFAULT '0',
  `denomination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'désignation',
  `pools` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `result_schedule_delay` double unsigned NOT NULL DEFAULT '0',
  `notifications` int DEFAULT NULL,
  `election_creation_date` datetime DEFAULT NULL,
  `is_blank_vote_enabled` tinyint(1) NOT NULL,
  `poll_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `custom_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wording_welcome_page_id` int unsigned DEFAULT NULL,
  `seats` smallint unsigned DEFAULT NULL,
  `majority_prime` smallint unsigned DEFAULT NULL,
  `majority_prime_round_sup_mode` tinyint(1) DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `election_entity_identifier` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `is_canceled` tinyint(1) NOT NULL DEFAULT '0',
  `wording_regulation_page_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8947610DD17F50A6` (`uuid`),
  KEY `IDX_8947610D3C947C0F` (`poll_id`),
  KEY `IDX_8947610D9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_8947610DCF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_8947610DDD49221F` (`wording_welcome_page_id`),
  KEY `IDX_8947610D85C9D733` (`created_by_adherent_id`),
  KEY `IDX_8947610DDF6CFDC9` (`updated_by_adherent_id`),
  KEY `IDX_8947610DE3A77273` (`wording_regulation_page_id`),
  CONSTRAINT `FK_8947610D3C947C0F` FOREIGN KEY (`poll_id`) REFERENCES `designation_poll` (`id`),
  CONSTRAINT `FK_8947610D85C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8947610D9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8947610DCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8947610DDD49221F` FOREIGN KEY (`wording_welcome_page_id`) REFERENCES `cms_block` (`id`),
  CONSTRAINT `FK_8947610DDF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8947610DE3A77273` FOREIGN KEY (`wording_regulation_page_id`) REFERENCES `cms_block` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designation`
--

LOCK TABLES `designation` WRITE;
/*!40000 ALTER TABLE `designation` DISABLE KEYS */;
/*!40000 ALTER TABLE `designation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designation_candidacy_pool`
--

DROP TABLE IF EXISTS `designation_candidacy_pool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_candidacy_pool` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `designation_id` int unsigned DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4DE072DAD17F50A6` (`uuid`),
  KEY `IDX_4DE072DAFAC7D83F` (`designation_id`),
  CONSTRAINT `FK_4DE072DAFAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designation_candidacy_pool`
--

LOCK TABLES `designation_candidacy_pool` WRITE;
/*!40000 ALTER TABLE `designation_candidacy_pool` DISABLE KEYS */;
/*!40000 ALTER TABLE `designation_candidacy_pool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designation_candidacy_pool_candidacies_group`
--

DROP TABLE IF EXISTS `designation_candidacy_pool_candidacies_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_candidacy_pool_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `candidacy_pool_id` int unsigned NOT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A9E819837B63808A` (`candidacy_pool_id`),
  KEY `IDX_A9E819839DF5350C` (`created_by_administrator_id`),
  KEY `IDX_A9E81983CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_A9E819837B63808A` FOREIGN KEY (`candidacy_pool_id`) REFERENCES `designation_candidacy_pool` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A9E819839DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A9E81983CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designation_candidacy_pool_candidacies_group`
--

LOCK TABLES `designation_candidacy_pool_candidacies_group` WRITE;
/*!40000 ALTER TABLE `designation_candidacy_pool_candidacies_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `designation_candidacy_pool_candidacies_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designation_candidacy_pool_candidacy`
--

DROP TABLE IF EXISTS `designation_candidacy_pool_candidacy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_candidacy_pool_candidacy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `candidacy_pool_id` int unsigned NOT NULL,
  `candidacies_group_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext COLLATE utf8mb4_unicode_ci,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_substitute` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `faith_statement` longtext COLLATE utf8mb4_unicode_ci,
  `is_public_faith_statement` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A4C6328BD17F50A6` (`uuid`),
  KEY `IDX_A4C6328B7B63808A` (`candidacy_pool_id`),
  KEY `IDX_A4C6328BFC1537C1` (`candidacies_group_id`),
  KEY `IDX_A4C6328B25F06C53` (`adherent_id`),
  CONSTRAINT `FK_A4C6328B25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_A4C6328B7B63808A` FOREIGN KEY (`candidacy_pool_id`) REFERENCES `designation_candidacy_pool` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A4C6328BFC1537C1` FOREIGN KEY (`candidacies_group_id`) REFERENCES `designation_candidacy_pool_candidacies_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designation_candidacy_pool_candidacy`
--

LOCK TABLES `designation_candidacy_pool_candidacy` WRITE;
/*!40000 ALTER TABLE `designation_candidacy_pool_candidacy` DISABLE KEYS */;
/*!40000 ALTER TABLE `designation_candidacy_pool_candidacy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designation_poll`
--

DROP TABLE IF EXISTS `designation_poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_poll` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3D0766CED17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designation_poll`
--

LOCK TABLES `designation_poll` WRITE;
/*!40000 ALTER TABLE `designation_poll` DISABLE KEYS */;
/*!40000 ALTER TABLE `designation_poll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designation_poll_question`
--

DROP TABLE IF EXISTS `designation_poll_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_poll_question` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` int unsigned NOT NULL,
  `content` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `position` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_83F55735D17F50A6` (`uuid`),
  KEY `IDX_83F557353C947C0F` (`poll_id`),
  CONSTRAINT `FK_83F557353C947C0F` FOREIGN KEY (`poll_id`) REFERENCES `designation_poll` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designation_poll_question`
--

LOCK TABLES `designation_poll_question` WRITE;
/*!40000 ALTER TABLE `designation_poll_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `designation_poll_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designation_poll_question_choice`
--

DROP TABLE IF EXISTS `designation_poll_question_choice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_poll_question_choice` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int unsigned NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AC70C953D17F50A6` (`uuid`),
  KEY `IDX_AC70C9531E27F6BF` (`question_id`),
  CONSTRAINT `FK_AC70C9531E27F6BF` FOREIGN KEY (`question_id`) REFERENCES `designation_poll_question` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designation_poll_question_choice`
--

LOCK TABLES `designation_poll_question_choice` WRITE;
/*!40000 ALTER TABLE `designation_poll_question_choice` DISABLE KEYS */;
/*!40000 ALTER TABLE `designation_poll_question_choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designation_referent_tag`
--

DROP TABLE IF EXISTS `designation_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_referent_tag` (
  `designation_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`designation_id`,`referent_tag_id`),
  KEY `IDX_7538F35A9C262DB3` (`referent_tag_id`),
  KEY `IDX_7538F35AFAC7D83F` (`designation_id`),
  CONSTRAINT `FK_7538F35A9C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7538F35AFAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designation_referent_tag`
--

LOCK TABLES `designation_referent_tag` WRITE;
/*!40000 ALTER TABLE `designation_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `designation_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designation_zone`
--

DROP TABLE IF EXISTS `designation_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_zone` (
  `designation_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`designation_id`,`zone_id`),
  KEY `IDX_19505C8CFAC7D83F` (`designation_id`),
  KEY `IDX_19505C8C9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_19505C8C9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_19505C8CFAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designation_zone`
--

LOCK TABLES `designation_zone` WRITE;
/*!40000 ALTER TABLE `designation_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `designation_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_zone`
--

DROP TABLE IF EXISTS `device_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_zone` (
  `device_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`device_id`,`zone_id`),
  KEY `IDX_29D2153D94A4C7D4` (`device_id`),
  KEY `IDX_29D2153D9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_29D2153D94A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_29D2153D9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_zone`
--

LOCK TABLES `device_zone` WRITE;
/*!40000 ALTER TABLE `device_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `devices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `device_uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_logged_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_11074E9A5846859C` (`device_uuid`),
  UNIQUE KEY `UNIQ_11074E9AD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `districts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `geo_data_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned DEFAULT NULL,
  `countries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` smallint unsigned NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_68E318DC77153098` (`code`),
  UNIQUE KEY `district_department_code_number` (`department_code`,`number`),
  UNIQUE KEY `UNIQ_68E318DC80E32C3E` (`geo_data_id`),
  UNIQUE KEY `UNIQ_68E318DC9C262DB3` (`referent_tag_id`),
  CONSTRAINT `FK_68E318DC80E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_68E318DC9C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `districts`
--

LOCK TABLES `districts` WRITE;
/*!40000 ALTER TABLE `districts` DISABLE KEYS */;
/*!40000 ALTER TABLE `districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document`
--

DROP TABLE IF EXISTS `document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` longtext COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D8698A762B36786B` (`title`),
  UNIQUE KEY `UNIQ_D8698A76D17F50A6` (`uuid`),
  KEY `IDX_D8698A769DF5350C` (`created_by_administrator_id`),
  KEY `IDX_D8698A76CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_D8698A769DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_D8698A76CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document`
--

LOCK TABLES `document` WRITE;
/*!40000 ALTER TABLE `document` DISABLE KEYS */;
/*!40000 ALTER TABLE `document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donation_donation_tag`
--

DROP TABLE IF EXISTS `donation_donation_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donation_donation_tag` (
  `donation_id` int unsigned NOT NULL,
  `donation_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`donation_id`,`donation_tag_id`),
  KEY `IDX_F2D7087F4DC1279C` (`donation_id`),
  KEY `IDX_F2D7087F790547EA` (`donation_tag_id`),
  CONSTRAINT `FK_F2D7087F4DC1279C` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F2D7087F790547EA` FOREIGN KEY (`donation_tag_id`) REFERENCES `donation_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donation_donation_tag`
--

LOCK TABLES `donation_donation_tag` WRITE;
/*!40000 ALTER TABLE `donation_donation_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `donation_donation_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donation_tags`
--

DROP TABLE IF EXISTS `donation_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donation_tags` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7E2FBF0CEA750E8` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donation_tags`
--

LOCK TABLES `donation_tags` WRITE;
/*!40000 ALTER TABLE `donation_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `donation_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donation_transactions`
--

DROP TABLE IF EXISTS `donation_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donation_transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `donation_id` int unsigned NOT NULL,
  `paybox_result_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paybox_authorization_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paybox_payload` json DEFAULT NULL,
  `paybox_date_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `paybox_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paybox_subscription_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_89D6D36B5A4036C7` (`paybox_transaction_id`),
  KEY `donation_transactions_result_idx` (`paybox_result_code`),
  KEY `IDX_89D6D36B4DC1279C` (`donation_id`),
  CONSTRAINT `FK_89D6D36B4DC1279C` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donation_transactions`
--

LOCK TABLES `donation_transactions` WRITE;
/*!40000 ALTER TABLE `donation_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `donation_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donations`
--

DROP TABLE IF EXISTS `donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `donator_id` int unsigned NOT NULL,
  `amount` int NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `client_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `duration` smallint NOT NULL DEFAULT '0',
  `subscription_ended_at` datetime DEFAULT NULL,
  `status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `paybox_order_ref` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `check_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transfer_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `donated_at` datetime NOT NULL,
  `last_success_date` datetime DEFAULT NULL,
  `code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiary` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership` tinyint(1) NOT NULL DEFAULT '0',
  `zone_id` int unsigned DEFAULT NULL,
  `visibility` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `re_adhesion` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CDE98962D17F50A6` (`uuid`),
  KEY `donation_duration_idx` (`duration`),
  KEY `donation_status_idx` (`status`),
  KEY `IDX_CDE98962831BACAF` (`donator_id`),
  KEY `IDX_CDE989629F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_CDE98962831BACAF` FOREIGN KEY (`donator_id`) REFERENCES `donators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CDE989629F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donations`
--

LOCK TABLES `donations` WRITE;
/*!40000 ALTER TABLE `donations` DISABLE KEYS */;
/*!40000 ALTER TABLE `donations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donator_donator_tag`
--

DROP TABLE IF EXISTS `donator_donator_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donator_donator_tag` (
  `donator_id` int unsigned NOT NULL,
  `donator_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`donator_id`,`donator_tag_id`),
  KEY `IDX_6BAEC28C71F026E6` (`donator_tag_id`),
  KEY `IDX_6BAEC28C831BACAF` (`donator_id`),
  CONSTRAINT `FK_6BAEC28C71F026E6` FOREIGN KEY (`donator_tag_id`) REFERENCES `donator_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6BAEC28C831BACAF` FOREIGN KEY (`donator_id`) REFERENCES `donators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donator_donator_tag`
--

LOCK TABLES `donator_donator_tag` WRITE;
/*!40000 ALTER TABLE `donator_donator_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `donator_donator_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donator_identifier`
--

DROP TABLE IF EXISTS `donator_identifier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donator_identifier` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donator_identifier`
--

LOCK TABLES `donator_identifier` WRITE;
/*!40000 ALTER TABLE `donator_identifier` DISABLE KEYS */;
/*!40000 ALTER TABLE `donator_identifier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donator_kinship`
--

DROP TABLE IF EXISTS `donator_kinship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donator_kinship` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `donator_id` int unsigned NOT NULL,
  `related_id` int unsigned NOT NULL,
  `kinship` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E542211D4162C001` (`related_id`),
  KEY `IDX_E542211D831BACAF` (`donator_id`),
  CONSTRAINT `FK_E542211D4162C001` FOREIGN KEY (`related_id`) REFERENCES `donators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E542211D831BACAF` FOREIGN KEY (`donator_id`) REFERENCES `donators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donator_kinship`
--

LOCK TABLES `donator_kinship` WRITE;
/*!40000 ALTER TABLE `donator_kinship` DISABLE KEYS */;
/*!40000 ALTER TABLE `donator_kinship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donator_tags`
--

DROP TABLE IF EXISTS `donator_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donator_tags` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F02E4E4EEA750E8` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donator_tags`
--

LOCK TABLES `donator_tags` WRITE;
/*!40000 ALTER TABLE `donator_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `donator_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donators`
--

DROP TABLE IF EXISTS `donators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donators` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `reference_donation_id` int unsigned DEFAULT NULL,
  `last_successful_donation_id` int unsigned DEFAULT NULL,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A902FDD7772E836A` (`identifier`),
  UNIQUE KEY `UNIQ_A902FDD7D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_A902FDD7ABF665A8` (`reference_donation_id`),
  UNIQUE KEY `UNIQ_A902FDD7DE59CB1A` (`last_successful_donation_id`),
  KEY `IDX_A902FDD725F06C53` (`adherent_id`),
  KEY `IDX_A902FDD7B08E074EA9D1C132C808BA5A` (`email_address`,`first_name`,`last_name`),
  CONSTRAINT `FK_A902FDD725F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A902FDD7ABF665A8` FOREIGN KEY (`reference_donation_id`) REFERENCES `donations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A902FDD7DE59CB1A` FOREIGN KEY (`last_successful_donation_id`) REFERENCES `donations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donators`
--

LOCK TABLES `donators` WRITE;
/*!40000 ALTER TABLE `donators` DISABLE KEYS */;
/*!40000 ALTER TABLE `donators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative`
--

DROP TABLE IF EXISTS `elected_representative`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date NOT NULL,
  `birth_place` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `has_followed_training` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `email_unsubscribed_at` datetime DEFAULT NULL,
  `email_unsubscribed` tinyint(1) NOT NULL DEFAULT '0',
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `last_contribution_id` int unsigned DEFAULT NULL,
  `contribution_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contributed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BF51F0FDD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_BF51F0FD25F06C53` (`adherent_id`),
  UNIQUE KEY `UNIQ_BF51F0FD14E51F8D` (`last_contribution_id`),
  KEY `IDX_BF51F0FD9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_BF51F0FDCF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_BF51F0FD85C9D733` (`created_by_adherent_id`),
  KEY `IDX_BF51F0FDDF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_BF51F0FD14E51F8D` FOREIGN KEY (`last_contribution_id`) REFERENCES `elected_representative_contribution` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BF51F0FD25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BF51F0FD85C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BF51F0FD9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BF51F0FDCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BF51F0FDDF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative`
--

LOCK TABLES `elected_representative` WRITE;
/*!40000 ALTER TABLE `elected_representative` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_contribution`
--

DROP TABLE IF EXISTS `elected_representative_contribution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_contribution` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `gocardless_customer_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `gocardless_bank_account_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_mandate_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_subscription_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_bank_account_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `gocardless_mandate_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_subscription_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6F9C7915D17F50A6` (`uuid`),
  KEY `IDX_6F9C7915D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_6F9C7915D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_contribution`
--

LOCK TABLES `elected_representative_contribution` WRITE;
/*!40000 ALTER TABLE `elected_representative_contribution` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_contribution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_label`
--

DROP TABLE IF EXISTS `elected_representative_label`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_label` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `on_going` tinyint(1) NOT NULL DEFAULT '1',
  `begin_year` int DEFAULT NULL,
  `finish_year` int DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D8143704D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_D8143704D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_label`
--

LOCK TABLES `elected_representative_label` WRITE;
/*!40000 ALTER TABLE `elected_representative_label` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_label` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_mandate`
--

DROP TABLE IF EXISTS `elected_representative_mandate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_mandate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `zone_id` int DEFAULT NULL,
  `geo_zone_id` int unsigned DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_elected` tinyint(1) NOT NULL DEFAULT '0',
  `begin_at` date NOT NULL,
  `finish_at` date DEFAULT NULL,
  `political_affiliation` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `la_remsupport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `on_going` tinyint(1) NOT NULL DEFAULT '1',
  `number` smallint NOT NULL DEFAULT '1',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `attached_zone_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_38609146D17F50A6` (`uuid`),
  KEY `IDX_38609146283AB2A9` (`geo_zone_id`),
  KEY `IDX_386091469F2C3FAB` (`zone_id`),
  KEY `IDX_38609146D38DA5D3` (`elected_representative_id`),
  KEY `IDX_38609146DAC800AF` (`attached_zone_id`),
  CONSTRAINT `FK_38609146283AB2A9` FOREIGN KEY (`geo_zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_386091469F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `elected_representative_zone` (`id`),
  CONSTRAINT `FK_38609146D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_38609146DAC800AF` FOREIGN KEY (`attached_zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_mandate`
--

LOCK TABLES `elected_representative_mandate` WRITE;
/*!40000 ALTER TABLE `elected_representative_mandate` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_mandate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_payment`
--

DROP TABLE IF EXISTS `elected_representative_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `ohme_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime DEFAULT NULL,
  `method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `amount` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4C351AA5D17F50A6` (`uuid`),
  KEY `IDX_4C351AA5D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_4C351AA5D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_payment`
--

LOCK TABLES `elected_representative_payment` WRITE;
/*!40000 ALTER TABLE `elected_representative_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_political_function`
--

DROP TABLE IF EXISTS `elected_representative_political_function`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_political_function` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `mandate_id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `clarification` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `on_going` tinyint(1) NOT NULL DEFAULT '1',
  `begin_at` date NOT NULL,
  `finish_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_303BAF416C1129CD` (`mandate_id`),
  KEY `IDX_303BAF41D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_303BAF416C1129CD` FOREIGN KEY (`mandate_id`) REFERENCES `elected_representative_mandate` (`id`),
  CONSTRAINT `FK_303BAF41D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_political_function`
--

LOCK TABLES `elected_representative_political_function` WRITE;
/*!40000 ALTER TABLE `elected_representative_political_function` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_political_function` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_revenue_declaration`
--

DROP TABLE IF EXISTS `elected_representative_revenue_declaration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_revenue_declaration` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `amount` int NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6A0C2D59D17F50A6` (`uuid`),
  KEY `IDX_6A0C2D59D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_6A0C2D59D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_revenue_declaration`
--

LOCK TABLES `elected_representative_revenue_declaration` WRITE;
/*!40000 ALTER TABLE `elected_representative_revenue_declaration` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_revenue_declaration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_social_network_link`
--

DROP TABLE IF EXISTS `elected_representative_social_network_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_social_network_link` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `social_network_elected_representative_unique` (`type`,`elected_representative_id`),
  KEY `IDX_231377B5D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_231377B5D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_social_network_link`
--

LOCK TABLES `elected_representative_social_network_link` WRITE;
/*!40000 ALTER TABLE `elected_representative_social_network_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_social_network_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_sponsorship`
--

DROP TABLE IF EXISTS `elected_representative_sponsorship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_sponsorship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `presidential_election_year` int NOT NULL,
  `candidate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CA6D486D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_CA6D486D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_sponsorship`
--

LOCK TABLES `elected_representative_sponsorship` WRITE;
/*!40000 ALTER TABLE `elected_representative_sponsorship` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_sponsorship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_user_list_definition`
--

DROP TABLE IF EXISTS `elected_representative_user_list_definition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_user_list_definition` (
  `elected_representative_id` int unsigned NOT NULL,
  `user_list_definition_id` int unsigned NOT NULL,
  PRIMARY KEY (`elected_representative_id`,`user_list_definition_id`),
  KEY `IDX_A9C53A24D38DA5D3` (`elected_representative_id`),
  KEY `IDX_A9C53A24F74563E3` (`user_list_definition_id`),
  CONSTRAINT `FK_A9C53A24D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A9C53A24F74563E3` FOREIGN KEY (`user_list_definition_id`) REFERENCES `user_list_definition` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_user_list_definition`
--

LOCK TABLES `elected_representative_user_list_definition` WRITE;
/*!40000 ALTER TABLE `elected_representative_user_list_definition` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_user_list_definition` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_user_list_definition_history`
--

DROP TABLE IF EXISTS `elected_representative_user_list_definition_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_user_list_definition_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `user_list_definition_id` int unsigned NOT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `administrator_id` int DEFAULT NULL,
  `action` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_1ECF756625F06C53` (`adherent_id`),
  KEY `IDX_1ECF75664B09E92C` (`administrator_id`),
  KEY `IDX_1ECF7566D38DA5D3` (`elected_representative_id`),
  KEY `IDX_1ECF7566F74563E3` (`user_list_definition_id`),
  CONSTRAINT `FK_1ECF756625F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_1ECF75664B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_1ECF7566D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1ECF7566F74563E3` FOREIGN KEY (`user_list_definition_id`) REFERENCES `user_list_definition` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_user_list_definition_history`
--

LOCK TABLES `elected_representative_user_list_definition_history` WRITE;
/*!40000 ALTER TABLE `elected_representative_user_list_definition_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_user_list_definition_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_zone`
--

DROP TABLE IF EXISTS `elected_representative_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_zone` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `elected_representative_zone_name_category_unique` (`name`,`category_id`),
  KEY `elected_repr_zone_code` (`code`),
  KEY `IDX_C52FC4A712469DE2` (`category_id`),
  CONSTRAINT `FK_C52FC4A712469DE2` FOREIGN KEY (`category_id`) REFERENCES `elected_representative_zone_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_zone`
--

LOCK TABLES `elected_representative_zone` WRITE;
/*!40000 ALTER TABLE `elected_representative_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_zone_category`
--

DROP TABLE IF EXISTS `elected_representative_zone_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_zone_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2E753C3B5E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_zone_category`
--

LOCK TABLES `elected_representative_zone_category` WRITE;
/*!40000 ALTER TABLE `elected_representative_zone_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_zone_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_zone_parent`
--

DROP TABLE IF EXISTS `elected_representative_zone_parent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_zone_parent` (
  `child_id` int NOT NULL,
  `parent_id` int NOT NULL,
  PRIMARY KEY (`child_id`,`parent_id`),
  KEY `IDX_CECA906F727ACA70` (`parent_id`),
  KEY `IDX_CECA906FDD62C21B` (`child_id`),
  CONSTRAINT `FK_CECA906F727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `elected_representative_zone` (`id`),
  CONSTRAINT `FK_CECA906FDD62C21B` FOREIGN KEY (`child_id`) REFERENCES `elected_representative_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_zone_parent`
--

LOCK TABLES `elected_representative_zone_parent` WRITE;
/*!40000 ALTER TABLE `elected_representative_zone_parent` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_zone_parent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elected_representative_zone_referent_tag`
--

DROP TABLE IF EXISTS `elected_representative_zone_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_zone_referent_tag` (
  `elected_representative_zone_id` int NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`elected_representative_zone_id`,`referent_tag_id`),
  KEY `IDX_D2B7A8C59C262DB3` (`referent_tag_id`),
  KEY `IDX_D2B7A8C5BE31A103` (`elected_representative_zone_id`),
  CONSTRAINT `FK_D2B7A8C59C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D2B7A8C5BE31A103` FOREIGN KEY (`elected_representative_zone_id`) REFERENCES `elected_representative_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elected_representative_zone_referent_tag`
--

LOCK TABLES `elected_representative_zone_referent_tag` WRITE;
/*!40000 ALTER TABLE `elected_representative_zone_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `elected_representative_zone_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_city_candidate`
--

DROP TABLE IF EXISTS `election_city_candidate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_city_candidate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `political_scheme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alliances` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agreement` tinyint(1) NOT NULL DEFAULT '0',
  `eligible_advisers_count` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `investiture_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_city_candidate`
--

LOCK TABLES `election_city_candidate` WRITE;
/*!40000 ALTER TABLE `election_city_candidate` DISABLE KEYS */;
/*!40000 ALTER TABLE `election_city_candidate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_city_card`
--

DROP TABLE IF EXISTS `election_city_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_city_card` (
  `id` int NOT NULL AUTO_INCREMENT,
  `city_id` int unsigned NOT NULL,
  `first_candidate_id` int DEFAULT NULL,
  `headquarters_manager_id` int DEFAULT NULL,
  `politic_manager_id` int DEFAULT NULL,
  `task_force_manager_id` int DEFAULT NULL,
  `preparation_prevision_id` int DEFAULT NULL,
  `candidate_prevision_id` int DEFAULT NULL,
  `national_prevision_id` int DEFAULT NULL,
  `candidate_option_prevision_id` int DEFAULT NULL,
  `third_option_prevision_id` int DEFAULT NULL,
  `population` int DEFAULT NULL,
  `priority` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `risk` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EB01E8D18BAC62AF` (`city_id`),
  UNIQUE KEY `UNIQ_EB01E8D1354DEDE5` (`candidate_option_prevision_id`),
  UNIQUE KEY `UNIQ_EB01E8D15EC54712` (`preparation_prevision_id`),
  UNIQUE KEY `UNIQ_EB01E8D1781FEED9` (`task_force_manager_id`),
  UNIQUE KEY `UNIQ_EB01E8D1B29FABBC` (`headquarters_manager_id`),
  UNIQUE KEY `UNIQ_EB01E8D1B86B270B` (`national_prevision_id`),
  UNIQUE KEY `UNIQ_EB01E8D1E449D110` (`first_candidate_id`),
  UNIQUE KEY `UNIQ_EB01E8D1E4A014FA` (`politic_manager_id`),
  UNIQUE KEY `UNIQ_EB01E8D1EBF42685` (`candidate_prevision_id`),
  UNIQUE KEY `UNIQ_EB01E8D1F543170A` (`third_option_prevision_id`),
  CONSTRAINT `FK_EB01E8D1354DEDE5` FOREIGN KEY (`candidate_option_prevision_id`) REFERENCES `election_city_prevision` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EB01E8D15EC54712` FOREIGN KEY (`preparation_prevision_id`) REFERENCES `election_city_prevision` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EB01E8D1781FEED9` FOREIGN KEY (`task_force_manager_id`) REFERENCES `election_city_manager` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EB01E8D18BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EB01E8D1B29FABBC` FOREIGN KEY (`headquarters_manager_id`) REFERENCES `election_city_manager` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EB01E8D1B86B270B` FOREIGN KEY (`national_prevision_id`) REFERENCES `election_city_prevision` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EB01E8D1E449D110` FOREIGN KEY (`first_candidate_id`) REFERENCES `election_city_candidate` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EB01E8D1E4A014FA` FOREIGN KEY (`politic_manager_id`) REFERENCES `election_city_manager` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EB01E8D1EBF42685` FOREIGN KEY (`candidate_prevision_id`) REFERENCES `election_city_prevision` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EB01E8D1F543170A` FOREIGN KEY (`third_option_prevision_id`) REFERENCES `election_city_prevision` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_city_card`
--

LOCK TABLES `election_city_card` WRITE;
/*!40000 ALTER TABLE `election_city_card` DISABLE KEYS */;
/*!40000 ALTER TABLE `election_city_card` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_city_contact`
--

DROP TABLE IF EXISTS `election_city_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_city_contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `city_id` int NOT NULL,
  `function` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `caller` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D04AFB68BAC62AF` (`city_id`),
  CONSTRAINT `FK_D04AFB68BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `election_city_card` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_city_contact`
--

LOCK TABLES `election_city_contact` WRITE;
/*!40000 ALTER TABLE `election_city_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `election_city_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_city_manager`
--

DROP TABLE IF EXISTS `election_city_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_city_manager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_city_manager`
--

LOCK TABLES `election_city_manager` WRITE;
/*!40000 ALTER TABLE `election_city_manager` DISABLE KEYS */;
/*!40000 ALTER TABLE `election_city_manager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_city_partner`
--

DROP TABLE IF EXISTS `election_city_partner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_city_partner` (
  `id` int NOT NULL AUTO_INCREMENT,
  `city_id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `consensus` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_704D77988BAC62AF` (`city_id`),
  CONSTRAINT `FK_704D77988BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `election_city_card` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_city_partner`
--

LOCK TABLES `election_city_partner` WRITE;
/*!40000 ALTER TABLE `election_city_partner` DISABLE KEYS */;
/*!40000 ALTER TABLE `election_city_partner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_city_prevision`
--

DROP TABLE IF EXISTS `election_city_prevision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_city_prevision` (
  `id` int NOT NULL AUTO_INCREMENT,
  `strategy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alliances` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allies` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_city_prevision`
--

LOCK TABLES `election_city_prevision` WRITE;
/*!40000 ALTER TABLE `election_city_prevision` DISABLE KEYS */;
/*!40000 ALTER TABLE `election_city_prevision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_rounds`
--

DROP TABLE IF EXISTS `election_rounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_rounds` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_id` int NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_37C02EA0A708DAFF` (`election_id`),
  CONSTRAINT `FK_37C02EA0A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_rounds`
--

LOCK TABLES `election_rounds` WRITE;
/*!40000 ALTER TABLE `election_rounds` DISABLE KEYS */;
/*!40000 ALTER TABLE `election_rounds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_vote_place`
--

DROP TABLE IF EXISTS `election_vote_place`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_vote_place` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `nb_addresses` int unsigned NOT NULL DEFAULT '0',
  `nb_voters` int unsigned NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_880DE20DD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_880DE20D77153098` (`code`),
  KEY `IDX_880DE20D9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_880DE20D9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_vote_place`
--

LOCK TABLES `election_vote_place` WRITE;
/*!40000 ALTER TABLE `election_vote_place` DISABLE KEYS */;
/*!40000 ALTER TABLE `election_vote_place` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elections`
--

DROP TABLE IF EXISTS `elections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proposal_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `request_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1BD26F335E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elections`
--

LOCK TABLES `elections` WRITE;
/*!40000 ALTER TABLE `elections` DISABLE KEYS */;
/*!40000 ALTER TABLE `elections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_template_zone`
--

DROP TABLE IF EXISTS `email_template_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_template_zone` (
  `email_template_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`email_template_id`,`zone_id`),
  KEY `IDX_8712F9C2131A730F` (`email_template_id`),
  KEY `IDX_8712F9C29F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_8712F9C2131A730F` FOREIGN KEY (`email_template_id`) REFERENCES `email_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_8712F9C29F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_template_zone`
--

LOCK TABLES `email_template_zone` WRITE;
/*!40000 ALTER TABLE `email_template_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_template_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `scopes` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `json_content` longtext COLLATE utf8mb4_unicode_ci,
  `is_statutory` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6023E2A5D17F50A6` (`uuid`),
  KEY `IDX_6023E2A59DF5350C` (`created_by_administrator_id`),
  KEY `IDX_6023E2A5CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_6023E2A585C9D733` (`created_by_adherent_id`),
  KEY `IDX_6023E2A5DF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_6023E2A585C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_6023E2A59DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_6023E2A5CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_6023E2A5DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_templates`
--

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emails` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_class` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4C81E852D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails`
--

LOCK TABLES `emails` WRITE;
/*!40000 ALTER TABLE `emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `epci`
--

DROP TABLE IF EXISTS `epci`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `epci` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `surface` double NOT NULL,
  `department_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `region_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `region_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_insee` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_dep` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_siren` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_arr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_cant` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `population` int unsigned DEFAULT NULL,
  `epci_dep` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `epci_siren` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `insee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fiscal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `epci`
--

LOCK TABLES `epci` WRITE;
/*!40000 ALTER TABLE `epci` DISABLE KEYS */;
/*!40000 ALTER TABLE `epci` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_group_category`
--

DROP TABLE IF EXISTS `event_group_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_group_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ENABLED',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D038E3CD5E237E06` (`name`),
  UNIQUE KEY `UNIQ_D038E3CD989D9B62` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_group_category`
--

LOCK TABLES `event_group_category` WRITE;
/*!40000 ALTER TABLE `event_group_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_group_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_referent_tag`
--

DROP TABLE IF EXISTS `event_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_referent_tag` (
  `event_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`event_id`,`referent_tag_id`),
  KEY `IDX_D3C8F5BE71F7E88B` (`event_id`),
  KEY `IDX_D3C8F5BE9C262DB3` (`referent_tag_id`),
  CONSTRAINT `FK_D3C8F5BE71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D3C8F5BE9C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_referent_tag`
--

LOCK TABLES `event_referent_tag` WRITE;
/*!40000 ALTER TABLE `event_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_user_documents`
--

DROP TABLE IF EXISTS `event_user_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_user_documents` (
  `event_id` int unsigned NOT NULL,
  `user_document_id` int unsigned NOT NULL,
  PRIMARY KEY (`event_id`,`user_document_id`),
  KEY `IDX_7D14491F6A24B1A2` (`user_document_id`),
  KEY `IDX_7D14491F71F7E88B` (`event_id`),
  CONSTRAINT `FK_7D14491F6A24B1A2` FOREIGN KEY (`user_document_id`) REFERENCES `user_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7D14491F71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_user_documents`
--

LOCK TABLES `event_user_documents` WRITE;
/*!40000 ALTER TABLE `event_user_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_user_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_zone`
--

DROP TABLE IF EXISTS `event_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_zone` (
  `base_event_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`base_event_id`,`zone_id`),
  KEY `IDX_BF208CAC3B1C4B73` (`base_event_id`),
  KEY `IDX_BF208CAC9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_BF208CAC3B1C4B73` FOREIGN KEY (`base_event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BF208CAC9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_zone`
--

LOCK TABLES `event_zone` WRITE;
/*!40000 ALTER TABLE `event_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `organizer_id` int unsigned DEFAULT NULL,
  `committee_id` int unsigned DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `canonical_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(130) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int DEFAULT NULL,
  `begin_at` datetime NOT NULL,
  `finish_at` datetime NOT NULL,
  `participants_count` smallint unsigned NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `is_for_legislatives` tinyint(1) DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_zone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visio_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminded` tinyint(1) NOT NULL DEFAULT '0',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `electoral` tinyint(1) NOT NULL DEFAULT '0',
  `dynamic_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `renaissance_event` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5387574A989D9B62` (`slug`),
  UNIQUE KEY `UNIQ_5387574AD17F50A6` (`uuid`),
  KEY `IDX_5387574A12469DE2` (`category_id`),
  KEY `IDX_5387574A3826374D` (`begin_at`),
  KEY `IDX_5387574A7B00651C` (`status`),
  KEY `IDX_5387574A876C4DDA` (`organizer_id`),
  KEY `IDX_5387574AED1A100B` (`committee_id`),
  KEY `IDX_5387574AFE28FD87` (`finish_at`),
  CONSTRAINT `FK_5387574A12469DE2` FOREIGN KEY (`category_id`) REFERENCES `events_categories` (`id`),
  CONSTRAINT `FK_5387574A876C4DDA` FOREIGN KEY (`organizer_id`) REFERENCES `adherents` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `FK_5387574AED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_group_category_id` int unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ENABLED',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EF0AF3E95E237E06` (`name`),
  UNIQUE KEY `UNIQ_EF0AF3E9989D9B62` (`slug`),
  KEY `IDX_EF0AF3E9A267D842` (`event_group_category_id`),
  CONSTRAINT `FK_EF0AF3E9A267D842` FOREIGN KEY (`event_group_category_id`) REFERENCES `event_group_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_categories`
--

LOCK TABLES `events_categories` WRITE;
/*!40000 ALTER TABLE `events_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `events_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_invitations`
--

DROP TABLE IF EXISTS `events_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_invitations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B94D5AADD17F50A6` (`uuid`),
  KEY `IDX_B94D5AAD71F7E88B` (`event_id`),
  CONSTRAINT `FK_B94D5AAD71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_registrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned DEFAULT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `newsletter_subscriber` tinyint(1) NOT NULL,
  `adherent_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EEFA30C0D17F50A6` (`uuid`),
  KEY `event_registration_adherent_uuid_idx` (`adherent_uuid`),
  KEY `event_registration_email_address_idx` (`email_address`),
  KEY `IDX_EEFA30C071F7E88B` (`event_id`),
  CONSTRAINT `FK_EEFA30C071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facebook_profiles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `facebook_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `age_range` json NOT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `access_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_auto_uploaded` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4C9116989BE8FD98` (`facebook_id`),
  UNIQUE KEY `UNIQ_4C911698D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facebook_videos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `facebook_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `twitter_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facebook_videos`
--

LOCK TABLES `facebook_videos` WRITE;
/*!40000 ALTER TABLE `facebook_videos` DISABLE KEYS */;
/*!40000 ALTER TABLE `facebook_videos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_login_attempt`
--

DROP TABLE IF EXISTS `failed_login_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_login_attempt` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `signature` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `at` datetime NOT NULL,
  `extra` json NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1CD95620D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_login_attempt`
--

LOCK TABLES `failed_login_attempt` WRITE;
/*!40000 ALTER TABLE `failed_login_attempt` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_login_attempt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filesystem_file`
--

DROP TABLE IF EXISTS `filesystem_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filesystem_file` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_id` int DEFAULT NULL,
  `updated_by_id` int DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `displayed` tinyint(1) NOT NULL DEFAULT '1',
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` int unsigned DEFAULT NULL,
  `external_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_47F0AE28989D9B62` (`slug`),
  UNIQUE KEY `UNIQ_47F0AE28D17F50A6` (`uuid`),
  KEY `IDX_47F0AE285E237E06` (`name`),
  KEY `IDX_47F0AE28727ACA70` (`parent_id`),
  KEY `IDX_47F0AE28896DBBDE` (`updated_by_id`),
  KEY `IDX_47F0AE288CDE5729` (`type`),
  KEY `IDX_47F0AE28B03A8386` (`created_by_id`),
  CONSTRAINT `FK_47F0AE28727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `filesystem_file` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_47F0AE28896DBBDE` FOREIGN KEY (`updated_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_47F0AE28B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filesystem_file`
--

LOCK TABLES `filesystem_file` WRITE;
/*!40000 ALTER TABLE `filesystem_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `filesystem_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filesystem_file_permission`
--

DROP TABLE IF EXISTS `filesystem_file_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filesystem_file_permission` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int unsigned NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_permission_unique` (`file_id`,`name`),
  KEY `IDX_BD623E4C93CB796C` (`file_id`),
  CONSTRAINT `FK_BD623E4C93CB796C` FOREIGN KEY (`file_id`) REFERENCES `filesystem_file` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filesystem_file_permission`
--

LOCK TABLES `filesystem_file_permission` WRITE;
/*!40000 ALTER TABLE `filesystem_file_permission` DISABLE KEYS */;
/*!40000 ALTER TABLE `filesystem_file_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formation_axes`
--

DROP TABLE IF EXISTS `formation_axes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formation_axes` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `path_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `position` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7E652CB6989D9B62` (`slug`),
  KEY `IDX_7E652CB6D96C566B` (`path_id`),
  KEY `IDX_7E652CB6EA9FDD75` (`media_id`),
  CONSTRAINT `FK_7E652CB6D96C566B` FOREIGN KEY (`path_id`) REFERENCES `formation_paths` (`id`),
  CONSTRAINT `FK_7E652CB6EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formation_axes`
--

LOCK TABLES `formation_axes` WRITE;
/*!40000 ALTER TABLE `formation_axes` DISABLE KEYS */;
/*!40000 ALTER TABLE `formation_axes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formation_files`
--

DROP TABLE IF EXISTS `formation_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formation_files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `formation_file_slug_extension` (`slug`,`extension`),
  KEY `IDX_70BEDE2CAFC2B591` (`module_id`),
  CONSTRAINT `FK_70BEDE2CAFC2B591` FOREIGN KEY (`module_id`) REFERENCES `formation_modules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formation_files`
--

LOCK TABLES `formation_files` WRITE;
/*!40000 ALTER TABLE `formation_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `formation_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formation_modules`
--

DROP TABLE IF EXISTS `formation_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formation_modules` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `axe_id` bigint DEFAULT NULL,
  `media_id` bigint DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `position` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6B4806AC2B36786B` (`title`),
  UNIQUE KEY `UNIQ_6B4806AC989D9B62` (`slug`),
  KEY `IDX_6B4806AC2E30CD41` (`axe_id`),
  KEY `IDX_6B4806ACEA9FDD75` (`media_id`),
  CONSTRAINT `FK_6B4806AC2E30CD41` FOREIGN KEY (`axe_id`) REFERENCES `formation_axes` (`id`),
  CONSTRAINT `FK_6B4806ACEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formation_modules`
--

LOCK TABLES `formation_modules` WRITE;
/*!40000 ALTER TABLE `formation_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `formation_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formation_paths`
--

DROP TABLE IF EXISTS `formation_paths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formation_paths` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FD311864989D9B62` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formation_paths`
--

LOCK TABLES `formation_paths` WRITE;
/*!40000 ALTER TABLE `formation_paths` DISABLE KEYS */;
/*!40000 ALTER TABLE `formation_paths` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `general_meeting_report`
--

DROP TABLE IF EXISTS `general_meeting_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `general_meeting_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `date` datetime NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `visibility` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6BA05833D17F50A6` (`uuid`),
  KEY `IDX_6BA058339DF5350C` (`created_by_administrator_id`),
  KEY `IDX_6BA05833CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_6BA0583385C9D733` (`created_by_adherent_id`),
  KEY `IDX_6BA05833DF6CFDC9` (`updated_by_adherent_id`),
  KEY `IDX_6BA058339F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_6BA0583385C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_6BA058339DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_6BA058339F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_6BA05833CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_6BA05833DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_meeting_report`
--

LOCK TABLES `general_meeting_report` WRITE;
/*!40000 ALTER TABLE `general_meeting_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `general_meeting_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_borough`
--

DROP TABLE IF EXISTS `geo_borough`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_borough` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int unsigned NOT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `postal_code` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `population` int DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1449587477153098` (`code`),
  UNIQUE KEY `UNIQ_1449587480E32C3E` (`geo_data_id`),
  KEY `IDX_144958748BAC62AF` (`city_id`),
  CONSTRAINT `FK_1449587480E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_144958748BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `geo_city` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_borough`
--

LOCK TABLES `geo_borough` WRITE;
/*!40000 ALTER TABLE `geo_borough` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_borough` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_canton`
--

DROP TABLE IF EXISTS `geo_canton`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_canton` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned NOT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F04FC05F77153098` (`code`),
  UNIQUE KEY `UNIQ_F04FC05F80E32C3E` (`geo_data_id`),
  KEY `IDX_F04FC05FAE80F5DF` (`department_id`),
  CONSTRAINT `FK_F04FC05F80E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_F04FC05FAE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `geo_department` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_canton`
--

LOCK TABLES `geo_canton` WRITE;
/*!40000 ALTER TABLE `geo_canton` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_canton` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_city`
--

DROP TABLE IF EXISTS `geo_city`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_city` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned DEFAULT NULL,
  `city_community_id` int unsigned DEFAULT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `replacement_id` int unsigned DEFAULT NULL,
  `postal_code` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `population` int DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_297C2D3477153098` (`code`),
  UNIQUE KEY `UNIQ_297C2D3480E32C3E` (`geo_data_id`),
  KEY `IDX_297C2D346D3B1930` (`city_community_id`),
  KEY `IDX_297C2D349D25CF90` (`replacement_id`),
  KEY `IDX_297C2D34AE80F5DF` (`department_id`),
  CONSTRAINT `FK_297C2D346D3B1930` FOREIGN KEY (`city_community_id`) REFERENCES `geo_city_community` (`id`),
  CONSTRAINT `FK_297C2D3480E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_297C2D349D25CF90` FOREIGN KEY (`replacement_id`) REFERENCES `geo_city` (`id`),
  CONSTRAINT `FK_297C2D34AE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `geo_department` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_city`
--

LOCK TABLES `geo_city` WRITE;
/*!40000 ALTER TABLE `geo_city` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_city` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_city_canton`
--

DROP TABLE IF EXISTS `geo_city_canton`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_city_canton` (
  `city_id` int unsigned NOT NULL,
  `canton_id` int unsigned NOT NULL,
  PRIMARY KEY (`city_id`,`canton_id`),
  KEY `IDX_A4AB64718BAC62AF` (`city_id`),
  KEY `IDX_A4AB64718D070D0B` (`canton_id`),
  CONSTRAINT `FK_A4AB64718BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `geo_city` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A4AB64718D070D0B` FOREIGN KEY (`canton_id`) REFERENCES `geo_canton` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_city_canton`
--

LOCK TABLES `geo_city_canton` WRITE;
/*!40000 ALTER TABLE `geo_city_canton` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_city_canton` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_city_community`
--

DROP TABLE IF EXISTS `geo_city_community`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_city_community` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `geo_data_id` int unsigned DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E5805E0877153098` (`code`),
  UNIQUE KEY `UNIQ_E5805E0880E32C3E` (`geo_data_id`),
  CONSTRAINT `FK_E5805E0880E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_city_community`
--

LOCK TABLES `geo_city_community` WRITE;
/*!40000 ALTER TABLE `geo_city_community` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_city_community` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_city_community_department`
--

DROP TABLE IF EXISTS `geo_city_community_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_city_community_department` (
  `city_community_id` int unsigned NOT NULL,
  `department_id` int unsigned NOT NULL,
  PRIMARY KEY (`city_community_id`,`department_id`),
  KEY `IDX_1E2D6D066D3B1930` (`city_community_id`),
  KEY `IDX_1E2D6D06AE80F5DF` (`department_id`),
  CONSTRAINT `FK_1E2D6D066D3B1930` FOREIGN KEY (`city_community_id`) REFERENCES `geo_city_community` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1E2D6D06AE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `geo_department` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_city_community_department`
--

LOCK TABLES `geo_city_community_department` WRITE;
/*!40000 ALTER TABLE `geo_city_community_department` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_city_community_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_city_district`
--

DROP TABLE IF EXISTS `geo_city_district`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_city_district` (
  `city_id` int unsigned NOT NULL,
  `district_id` int unsigned NOT NULL,
  PRIMARY KEY (`city_id`,`district_id`),
  KEY `IDX_5C4191F8BAC62AF` (`city_id`),
  KEY `IDX_5C4191FB08FA272` (`district_id`),
  CONSTRAINT `FK_5C4191F8BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `geo_city` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5C4191FB08FA272` FOREIGN KEY (`district_id`) REFERENCES `geo_district` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_city_district`
--

LOCK TABLES `geo_city_district` WRITE;
/*!40000 ALTER TABLE `geo_city_district` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_city_district` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_consular_district`
--

DROP TABLE IF EXISTS `geo_consular_district`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_consular_district` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `foreign_district_id` int unsigned DEFAULT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `number` smallint NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BBFC552F77153098` (`code`),
  UNIQUE KEY `UNIQ_BBFC552F80E32C3E` (`geo_data_id`),
  KEY `IDX_BBFC552F72D24D35` (`foreign_district_id`),
  CONSTRAINT `FK_BBFC552F72D24D35` FOREIGN KEY (`foreign_district_id`) REFERENCES `geo_foreign_district` (`id`),
  CONSTRAINT `FK_BBFC552F80E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_consular_district`
--

LOCK TABLES `geo_consular_district` WRITE;
/*!40000 ALTER TABLE `geo_consular_district` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_consular_district` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_country`
--

DROP TABLE IF EXISTS `geo_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_country` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `foreign_district_id` int unsigned DEFAULT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E465446477153098` (`code`),
  UNIQUE KEY `UNIQ_E465446480E32C3E` (`geo_data_id`),
  KEY `IDX_E465446472D24D35` (`foreign_district_id`),
  CONSTRAINT `FK_E465446472D24D35` FOREIGN KEY (`foreign_district_id`) REFERENCES `geo_foreign_district` (`id`),
  CONSTRAINT `FK_E465446480E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_country`
--

LOCK TABLES `geo_country` WRITE;
/*!40000 ALTER TABLE `geo_country` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_custom_zone`
--

DROP TABLE IF EXISTS `geo_custom_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_custom_zone` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `geo_data_id` int unsigned DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_ABE4DB5A77153098` (`code`),
  UNIQUE KEY `UNIQ_ABE4DB5A80E32C3E` (`geo_data_id`),
  CONSTRAINT `FK_ABE4DB5A80E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_custom_zone`
--

LOCK TABLES `geo_custom_zone` WRITE;
/*!40000 ALTER TABLE `geo_custom_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_custom_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_data`
--

DROP TABLE IF EXISTS `geo_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_data` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `geo_shape` geometry NOT NULL COMMENT '(DC2Type:geometry)',
  PRIMARY KEY (`id`),
  SPATIAL KEY `geo_data_geo_shape_idx` (`geo_shape`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_data`
--

LOCK TABLES `geo_data` WRITE;
/*!40000 ALTER TABLE `geo_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_department`
--

DROP TABLE IF EXISTS `geo_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_department` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `region_id` int unsigned NOT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B460660477153098` (`code`),
  UNIQUE KEY `UNIQ_B460660480E32C3E` (`geo_data_id`),
  KEY `IDX_B460660498260155` (`region_id`),
  CONSTRAINT `FK_B460660480E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_B460660498260155` FOREIGN KEY (`region_id`) REFERENCES `geo_region` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_department`
--

LOCK TABLES `geo_department` WRITE;
/*!40000 ALTER TABLE `geo_department` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_district`
--

DROP TABLE IF EXISTS `geo_district`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_district` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned NOT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `number` smallint NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DF78232677153098` (`code`),
  UNIQUE KEY `UNIQ_DF78232680E32C3E` (`geo_data_id`),
  KEY `IDX_DF782326AE80F5DF` (`department_id`),
  CONSTRAINT `FK_DF78232680E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_DF782326AE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `geo_department` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_district`
--

LOCK TABLES `geo_district` WRITE;
/*!40000 ALTER TABLE `geo_district` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_district` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_foreign_district`
--

DROP TABLE IF EXISTS `geo_foreign_district`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_foreign_district` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `custom_zone_id` int unsigned NOT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `number` smallint NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_973BE1F177153098` (`code`),
  UNIQUE KEY `UNIQ_973BE1F180E32C3E` (`geo_data_id`),
  KEY `IDX_973BE1F198755666` (`custom_zone_id`),
  CONSTRAINT `FK_973BE1F180E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_973BE1F198755666` FOREIGN KEY (`custom_zone_id`) REFERENCES `geo_custom_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_foreign_district`
--

LOCK TABLES `geo_foreign_district` WRITE;
/*!40000 ALTER TABLE `geo_foreign_district` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_foreign_district` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_region`
--

DROP TABLE IF EXISTS `geo_region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_region` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int unsigned NOT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A4B3C80877153098` (`code`),
  UNIQUE KEY `UNIQ_A4B3C80880E32C3E` (`geo_data_id`),
  KEY `IDX_A4B3C808F92F3E70` (`country_id`),
  CONSTRAINT `FK_A4B3C80880E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_A4B3C808F92F3E70` FOREIGN KEY (`country_id`) REFERENCES `geo_country` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_region`
--

LOCK TABLES `geo_region` WRITE;
/*!40000 ALTER TABLE `geo_region` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_vote_place`
--

DROP TABLE IF EXISTS `geo_vote_place`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_vote_place` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int unsigned NOT NULL,
  `district_id` int unsigned NOT NULL,
  `canton_id` int unsigned DEFAULT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5C09B68877153098` (`code`),
  UNIQUE KEY `UNIQ_5C09B68880E32C3E` (`geo_data_id`),
  KEY `IDX_5C09B6888BAC62AF` (`city_id`),
  KEY `IDX_5C09B688B08FA272` (`district_id`),
  KEY `IDX_5C09B6888D070D0B` (`canton_id`),
  CONSTRAINT `FK_5C09B68880E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_5C09B6888BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `geo_city` (`id`),
  CONSTRAINT `FK_5C09B6888D070D0B` FOREIGN KEY (`canton_id`) REFERENCES `geo_canton` (`id`),
  CONSTRAINT `FK_5C09B688B08FA272` FOREIGN KEY (`district_id`) REFERENCES `geo_district` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_vote_place`
--

LOCK TABLES `geo_vote_place` WRITE;
/*!40000 ALTER TABLE `geo_vote_place` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_vote_place` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_zone`
--

DROP TABLE IF EXISTS `geo_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_zone` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `geo_data_id` int unsigned DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `postal_code` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `tags` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `geo_zone_code_type_unique` (`code`,`type`),
  UNIQUE KEY `UNIQ_A4CCEF07D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_A4CCEF0780E32C3E` (`geo_data_id`),
  KEY `IDX_A4CCEF078CDE5729` (`type`),
  CONSTRAINT `FK_A4CCEF0780E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_zone`
--

LOCK TABLES `geo_zone` WRITE;
/*!40000 ALTER TABLE `geo_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_zone_parent`
--

DROP TABLE IF EXISTS `geo_zone_parent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_zone_parent` (
  `child_id` int unsigned NOT NULL,
  `parent_id` int unsigned NOT NULL,
  PRIMARY KEY (`child_id`,`parent_id`),
  KEY `IDX_8E49B9D727ACA70` (`parent_id`),
  KEY `IDX_8E49B9DDD62C21B` (`child_id`),
  CONSTRAINT `FK_8E49B9D727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_8E49B9DDD62C21B` FOREIGN KEY (`child_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_zone_parent`
--

LOCK TABLES `geo_zone_parent` WRITE;
/*!40000 ALTER TABLE `geo_zone_parent` DISABLE KEYS */;
/*!40000 ALTER TABLE `geo_zone_parent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `home_blocks`
--

DROP TABLE IF EXISTS `home_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `home_blocks` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `position` smallint NOT NULL,
  `position_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `display_filter` tinyint(1) NOT NULL DEFAULT '1',
  `display_titles` tinyint(1) NOT NULL DEFAULT '0',
  `display_block` tinyint(1) NOT NULL DEFAULT '1',
  `title_cta` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_cta` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bg_color` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_controls` tinyint(1) NOT NULL DEFAULT '0',
  `video_autoplay_loop` tinyint(1) NOT NULL DEFAULT '1',
  `for_renaissance` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3EE9FCC5462CE4F5` (`position`),
  UNIQUE KEY `UNIQ_3EE9FCC54DBB5058` (`position_name`),
  KEY `IDX_3EE9FCC5EA9FDD75` (`media_id`),
  CONSTRAINT `FK_3EE9FCC5EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home_blocks`
--

LOCK TABLES `home_blocks` WRITE;
/*!40000 ALTER TABLE `home_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `home_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `image`
--

DROP TABLE IF EXISTS `image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `image` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C53D045FD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `image`
--

LOCK TABLES `image` WRITE;
/*!40000 ALTER TABLE `image` DISABLE KEYS */;
/*!40000 ALTER TABLE `image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instance_quality`
--

DROP TABLE IF EXISTS `instance_quality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instance_quality` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BB26C6D377153098` (`code`),
  UNIQUE KEY `UNIQ_BB26C6D3D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instance_quality`
--

LOCK TABLES `instance_quality` WRITE;
/*!40000 ALTER TABLE `instance_quality` DISABLE KEYS */;
/*!40000 ALTER TABLE `instance_quality` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interactive_choices`
--

DROP TABLE IF EXISTS `interactive_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interactive_choices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `step` smallint unsigned NOT NULL,
  `content_key` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3C6695A73F7BFD5C` (`content_key`),
  UNIQUE KEY `UNIQ_3C6695A7D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interactive_choices`
--

LOCK TABLES `interactive_choices` WRITE;
/*!40000 ALTER TABLE `interactive_choices` DISABLE KEYS */;
/*!40000 ALTER TABLE `interactive_choices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interactive_invitation_has_choices`
--

DROP TABLE IF EXISTS `interactive_invitation_has_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interactive_invitation_has_choices` (
  `invitation_id` int unsigned NOT NULL,
  `choice_id` int unsigned NOT NULL,
  PRIMARY KEY (`invitation_id`,`choice_id`),
  KEY `IDX_31A811A2998666D1` (`choice_id`),
  KEY `IDX_31A811A2A35D7AF0` (`invitation_id`),
  CONSTRAINT `FK_31A811A2998666D1` FOREIGN KEY (`choice_id`) REFERENCES `interactive_choices` (`id`),
  CONSTRAINT `FK_31A811A2A35D7AF0` FOREIGN KEY (`invitation_id`) REFERENCES `interactive_invitations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interactive_invitations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `friend_age` smallint unsigned NOT NULL,
  `friend_gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `friend_position` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_subject` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_45258689D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interactive_invitations`
--

LOCK TABLES `interactive_invitations` WRITE;
/*!40000 ALTER TABLE `interactive_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `interactive_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internal_api_application`
--

DROP TABLE IF EXISTS `internal_api_application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `internal_api_application` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `application_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hostname` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `scope_required` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D0E72FCDD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internal_api_application`
--

LOCK TABLES `internal_api_application` WRITE;
/*!40000 ALTER TABLE `internal_api_application` DISABLE KEYS */;
/*!40000 ALTER TABLE `internal_api_application` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invalid_email_address`
--

DROP TABLE IF EXISTS `invalid_email_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invalid_email_address` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4792EA85D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invalid_email_address`
--

LOCK TABLES `invalid_email_address` WRITE;
/*!40000 ALTER TABLE `invalid_email_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `invalid_email_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invitations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_232710AED17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `je_marche_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `convinced` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `almost_convinced` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `not_convinced` smallint unsigned DEFAULT NULL,
  `reaction` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `je_marche_reports`
--

LOCK TABLES `je_marche_reports` WRITE;
/*!40000 ALTER TABLE `je_marche_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `je_marche_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_choice`
--

DROP TABLE IF EXISTS `jecoute_choice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_choice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int DEFAULT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_80BD898B1E27F6BF` (`question_id`),
  CONSTRAINT `FK_80BD898B1E27F6BF` FOREIGN KEY (`question_id`) REFERENCES `jecoute_question` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_choice`
--

LOCK TABLES `jecoute_choice` WRITE;
/*!40000 ALTER TABLE `jecoute_choice` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_data_answer`
--

DROP TABLE IF EXISTS `jecoute_data_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_data_answer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `survey_question_id` int DEFAULT NULL,
  `data_survey_id` int unsigned DEFAULT NULL,
  `text_field` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_12FB393E3C5110AB` (`data_survey_id`),
  KEY `IDX_12FB393EA6DF29BA` (`survey_question_id`),
  CONSTRAINT `FK_12FB393E3C5110AB` FOREIGN KEY (`data_survey_id`) REFERENCES `jecoute_data_survey` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_12FB393EA6DF29BA` FOREIGN KEY (`survey_question_id`) REFERENCES `jecoute_survey_question` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_data_answer`
--

LOCK TABLES `jecoute_data_answer` WRITE;
/*!40000 ALTER TABLE `jecoute_data_answer` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_data_answer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_data_answer_selected_choices`
--

DROP TABLE IF EXISTS `jecoute_data_answer_selected_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_data_answer_selected_choices` (
  `data_answer_id` int NOT NULL,
  `choice_id` int NOT NULL,
  PRIMARY KEY (`data_answer_id`,`choice_id`),
  KEY `IDX_10DF117259C0831` (`data_answer_id`),
  KEY `IDX_10DF117998666D1` (`choice_id`),
  CONSTRAINT `FK_10DF117259C0831` FOREIGN KEY (`data_answer_id`) REFERENCES `jecoute_data_answer` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_10DF117998666D1` FOREIGN KEY (`choice_id`) REFERENCES `jecoute_choice` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_data_answer_selected_choices`
--

LOCK TABLES `jecoute_data_answer_selected_choices` WRITE;
/*!40000 ALTER TABLE `jecoute_data_answer_selected_choices` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_data_answer_selected_choices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_data_survey`
--

DROP TABLE IF EXISTS `jecoute_data_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_data_survey` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
  `survey_id` int unsigned NOT NULL,
  `posted_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `author_postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6579E8E7D17F50A6` (`uuid`),
  KEY `IDX_6579E8E7B3FE509D` (`survey_id`),
  KEY `IDX_6579E8E7F675F31B` (`author_id`),
  KEY `IDX_6579E8E7B669800E` (`author_postal_code`),
  CONSTRAINT `FK_6579E8E7B3FE509D` FOREIGN KEY (`survey_id`) REFERENCES `jecoute_survey` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6579E8E7F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_data_survey`
--

LOCK TABLES `jecoute_data_survey` WRITE;
/*!40000 ALTER TABLE `jecoute_data_survey` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_data_survey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_managed_areas`
--

DROP TABLE IF EXISTS `jecoute_managed_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_managed_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned DEFAULT NULL,
  `codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  KEY `IDX_DF8531749F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_DF8531749F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_managed_areas`
--

LOCK TABLES `jecoute_managed_areas` WRITE;
/*!40000 ALTER TABLE `jecoute_managed_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_managed_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_news`
--

DROP TABLE IF EXISTS `jecoute_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_news` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned DEFAULT NULL,
  `created_by_id` int DEFAULT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `external_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `topic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `space` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visibility` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `enriched` tinyint(1) NOT NULL DEFAULT '0',
  `dynamic_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3436209D17F50A6` (`uuid`),
  KEY `IDX_34362099F2C3FAB` (`zone_id`),
  KEY `IDX_3436209B03A8386` (`created_by_id`),
  KEY `IDX_3436209F675F31B` (`author_id`),
  CONSTRAINT `FK_34362099F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_3436209B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_3436209F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_news`
--

LOCK TABLES `jecoute_news` WRITE;
/*!40000 ALTER TABLE `jecoute_news` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_news_user_documents`
--

DROP TABLE IF EXISTS `jecoute_news_user_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_news_user_documents` (
  `jecoute_news_id` int NOT NULL,
  `user_document_id` int unsigned NOT NULL,
  PRIMARY KEY (`jecoute_news_id`,`user_document_id`),
  KEY `IDX_1231D19DD18EE7B3` (`jecoute_news_id`),
  KEY `IDX_1231D19D6A24B1A2` (`user_document_id`),
  CONSTRAINT `FK_1231D19D6A24B1A2` FOREIGN KEY (`user_document_id`) REFERENCES `user_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1231D19DD18EE7B3` FOREIGN KEY (`jecoute_news_id`) REFERENCES `jecoute_news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_news_user_documents`
--

LOCK TABLES `jecoute_news_user_documents` WRITE;
/*!40000 ALTER TABLE `jecoute_news_user_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_news_user_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_question`
--

DROP TABLE IF EXISTS `jecoute_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `discr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_question`
--

LOCK TABLES `jecoute_question` WRITE;
/*!40000 ALTER TABLE `jecoute_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_region`
--

DROP TABLE IF EXISTS `jecoute_region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_region` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `administrator_id` int DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `primary_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4E74226F9F2C3FAB` (`zone_id`),
  UNIQUE KEY `UNIQ_4E74226FD17F50A6` (`uuid`),
  KEY `IDX_4E74226F4B09E92C` (`administrator_id`),
  KEY `IDX_4E74226FF675F31B` (`author_id`),
  CONSTRAINT `FK_4E74226F4B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_4E74226F9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_4E74226FF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_region`
--

LOCK TABLES `jecoute_region` WRITE;
/*!40000 ALTER TABLE `jecoute_region` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_resource_link`
--

DROP TABLE IF EXISTS `jecoute_resource_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_resource_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9368D3ADD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_resource_link`
--

LOCK TABLES `jecoute_resource_link` WRITE;
/*!40000 ALTER TABLE `jecoute_resource_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_resource_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_riposte`
--

DROP TABLE IF EXISTS `jecoute_riposte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_riposte` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_id` int DEFAULT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `with_notification` tinyint(1) NOT NULL DEFAULT '1',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `nb_views` int unsigned NOT NULL DEFAULT '0',
  `nb_detail_views` int unsigned NOT NULL DEFAULT '0',
  `nb_source_views` int unsigned NOT NULL DEFAULT '0',
  `nb_ripostes` int unsigned NOT NULL DEFAULT '0',
  `open_graph` json NOT NULL,
  `dynamic_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_17E1064BD17F50A6` (`uuid`),
  KEY `IDX_17E1064BB03A8386` (`created_by_id`),
  KEY `IDX_17E1064BF675F31B` (`author_id`),
  CONSTRAINT `FK_17E1064BB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_17E1064BF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_riposte`
--

LOCK TABLES `jecoute_riposte` WRITE;
/*!40000 ALTER TABLE `jecoute_riposte` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_riposte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_suggested_question`
--

DROP TABLE IF EXISTS `jecoute_suggested_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_suggested_question` (
  `id` int NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_8280E9DABF396750` FOREIGN KEY (`id`) REFERENCES `jecoute_question` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_suggested_question`
--

LOCK TABLES `jecoute_suggested_question` WRITE;
/*!40000 ALTER TABLE `jecoute_suggested_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_suggested_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_survey`
--

DROP TABLE IF EXISTS `jecoute_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_survey` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `blocked_changes` tinyint(1) DEFAULT '0',
  `updated_by_administrator_id` int DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `dynamic_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EC4948E5D17F50A6` (`uuid`),
  KEY `IDX_EC4948E59F2C3FAB` (`zone_id`),
  KEY `IDX_EC4948E59DF5350C` (`created_by_administrator_id`),
  KEY `IDX_EC4948E5CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_EC4948E585C9D733` (`created_by_adherent_id`),
  KEY `IDX_EC4948E5DF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_EC4948E585C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EC4948E59DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EC4948E59F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_EC4948E5CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EC4948E5DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_survey`
--

LOCK TABLES `jecoute_survey` WRITE;
/*!40000 ALTER TABLE `jecoute_survey` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_survey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jecoute_survey_question`
--

DROP TABLE IF EXISTS `jecoute_survey_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_survey_question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `survey_id` int unsigned DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `position` int NOT NULL,
  `from_suggested_question` int DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A2FBFA81D17F50A6` (`uuid`),
  KEY `IDX_A2FBFA811E27F6BF` (`question_id`),
  KEY `IDX_A2FBFA81B3FE509D` (`survey_id`),
  CONSTRAINT `FK_A2FBFA811E27F6BF` FOREIGN KEY (`question_id`) REFERENCES `jecoute_question` (`id`),
  CONSTRAINT `FK_A2FBFA81B3FE509D` FOREIGN KEY (`survey_id`) REFERENCES `jecoute_survey` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jecoute_survey_question`
--

LOCK TABLES `jecoute_survey_question` WRITE;
/*!40000 ALTER TABLE `jecoute_survey_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `jecoute_survey_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jemarche_data_survey`
--

DROP TABLE IF EXISTS `jemarche_data_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jemarche_data_survey` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_survey_id` int unsigned DEFAULT NULL,
  `device_id` int unsigned DEFAULT NULL,
  `first_name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `email_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `agreed_to_stay_in_contact` tinyint(1) NOT NULL,
  `agreed_to_contact_for_join` tinyint(1) NOT NULL,
  `agreed_to_treat_personal_data` tinyint(1) NOT NULL,
  `postal_code` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `profession` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_range` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender_other` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8DF5D818D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_8DF5D8183C5110AB` (`data_survey_id`),
  KEY `IDX_8DF5D81894A4C7D4` (`device_id`),
  CONSTRAINT `FK_8DF5D8183C5110AB` FOREIGN KEY (`data_survey_id`) REFERENCES `jecoute_data_survey` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8DF5D81894A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jemarche_data_survey`
--

LOCK TABLES `jemarche_data_survey` WRITE;
/*!40000 ALTER TABLE `jemarche_data_survey` DISABLE KEYS */;
/*!40000 ALTER TABLE `jemarche_data_survey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jemengage_deep_link`
--

DROP TABLE IF EXISTS `jemengage_deep_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jemengage_deep_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `social_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `dynamic_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AB0E5282D17F50A6` (`uuid`),
  KEY `IDX_AB0E52829DF5350C` (`created_by_administrator_id`),
  KEY `IDX_AB0E5282CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_AB0E52829DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_AB0E5282CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jemengage_deep_link`
--

LOCK TABLES `jemengage_deep_link` WRITE;
/*!40000 ALTER TABLE `jemengage_deep_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `jemengage_deep_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jemengage_header_blocks`
--

DROP TABLE IF EXISTS `jemengage_header_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jemengage_header_blocks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(130) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slogan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `deadline_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_682302E75E237E06` (`name`),
  UNIQUE KEY `UNIQ_682302E7989D9B62` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jemengage_header_blocks`
--

LOCK TABLES `jemengage_header_blocks` WRITE;
/*!40000 ALTER TABLE `jemengage_header_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `jemengage_header_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jemengage_mobile_app_download`
--

DROP TABLE IF EXISTS `jemengage_mobile_app_download`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jemengage_mobile_app_download` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `zone_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unique_user` bigint NOT NULL,
  `cum_sum` int NOT NULL,
  `downloads_per1000` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jemengage_mobile_app_download`
--

LOCK TABLES `jemengage_mobile_app_download` WRITE;
/*!40000 ALTER TABLE `jemengage_mobile_app_download` DISABLE KEYS */;
/*!40000 ALTER TABLE `jemengage_mobile_app_download` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jemengage_mobile_app_usage`
--

DROP TABLE IF EXISTS `jemengage_mobile_app_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jemengage_mobile_app_usage` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `zone_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unique_user` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jemengage_mobile_app_usage`
--

LOCK TABLES `jemengage_mobile_app_usage` WRITE;
/*!40000 ALTER TABLE `jemengage_mobile_app_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `jemengage_mobile_app_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legislative_candidates`
--

DROP TABLE IF EXISTS `legislative_candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `legislative_candidates` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `district_zone_id` smallint unsigned DEFAULT NULL,
  `media_id` bigint DEFAULT NULL,
  `facebook_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `donation_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `district_number` smallint NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `career` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `geojson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AE55AF9B989D9B62` (`slug`),
  KEY `IDX_AE55AF9B23F5C396` (`district_zone_id`),
  KEY `IDX_AE55AF9BEA9FDD75` (`media_id`),
  CONSTRAINT `FK_AE55AF9B23F5C396` FOREIGN KEY (`district_zone_id`) REFERENCES `legislative_district_zones` (`id`),
  CONSTRAINT `FK_AE55AF9BEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `legislative_district_zones` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `area_code` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rank` smallint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5853B7FAB5501F87` (`area_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legislative_district_zones`
--

LOCK TABLES `legislative_district_zones` WRITE;
/*!40000 ALTER TABLE `legislative_district_zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `legislative_district_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legislative_newsletter_subscription`
--

DROP TABLE IF EXISTS `legislative_newsletter_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `legislative_newsletter_subscription` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `token` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `confirmed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2672FB76D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_2672FB76B08E074E` (`email_address`),
  UNIQUE KEY `UNIQ_2672FB765F37A13B` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legislative_newsletter_subscription`
--

LOCK TABLES `legislative_newsletter_subscription` WRITE;
/*!40000 ALTER TABLE `legislative_newsletter_subscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `legislative_newsletter_subscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legislative_newsletter_subscription_zone`
--

DROP TABLE IF EXISTS `legislative_newsletter_subscription_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `legislative_newsletter_subscription_zone` (
  `legislative_newsletter_subscription_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`legislative_newsletter_subscription_id`,`zone_id`),
  KEY `IDX_4E900BCF7F7EF992` (`legislative_newsletter_subscription_id`),
  KEY `IDX_4E900BCF9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_4E900BCF7F7EF992` FOREIGN KEY (`legislative_newsletter_subscription_id`) REFERENCES `legislative_newsletter_subscription` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4E900BCF9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legislative_newsletter_subscription_zone`
--

LOCK TABLES `legislative_newsletter_subscription_zone` WRITE;
/*!40000 ALTER TABLE `legislative_newsletter_subscription_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `legislative_newsletter_subscription_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `list_total_result`
--

DROP TABLE IF EXISTS `list_total_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `list_total_result` (
  `id` int NOT NULL AUTO_INCREMENT,
  `list_id` int DEFAULT NULL,
  `vote_result_id` int NOT NULL,
  `total` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_A19B071E3DAE168B` (`list_id`),
  KEY `IDX_A19B071E45EB7186` (`vote_result_id`),
  CONSTRAINT `FK_A19B071E3DAE168B` FOREIGN KEY (`list_id`) REFERENCES `vote_result_list` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A19B071E45EB7186` FOREIGN KEY (`vote_result_id`) REFERENCES `vote_result` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `list_total_result`
--

LOCK TABLES `list_total_result` WRITE;
/*!40000 ALTER TABLE `list_total_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `list_total_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `live_links`
--

DROP TABLE IF EXISTS `live_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `live_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position` smallint NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `live_links`
--

LOCK TABLES `live_links` WRITE;
/*!40000 ALTER TABLE `live_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `live_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local_election`
--

DROP TABLE IF EXISTS `local_election`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_election` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `designation_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4F341298D17F50A6` (`uuid`),
  KEY `IDX_4F341298FAC7D83F` (`designation_id`),
  CONSTRAINT `FK_4F341298FAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local_election`
--

LOCK TABLES `local_election` WRITE;
/*!40000 ALTER TABLE `local_election` DISABLE KEYS */;
/*!40000 ALTER TABLE `local_election` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local_election_candidacies_group`
--

DROP TABLE IF EXISTS `local_election_candidacies_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_election_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `faith_statement_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8D478DE8A708DAFF` (`election_id`),
  KEY `IDX_8D478DE89DF5350C` (`created_by_administrator_id`),
  KEY `IDX_8D478DE8CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_8D478DE89DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8D478DE8A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `local_election` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_8D478DE8CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local_election_candidacies_group`
--

LOCK TABLES `local_election_candidacies_group` WRITE;
/*!40000 ALTER TABLE `local_election_candidacies_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `local_election_candidacies_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local_election_candidacy`
--

DROP TABLE IF EXISTS `local_election_candidacy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_election_candidacy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `candidacies_group_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `faith_statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_public_faith_statement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_77220D7DD17F50A6` (`uuid`),
  KEY `IDX_77220D7DA708DAFF` (`election_id`),
  KEY `IDX_77220D7DFC1537C1` (`candidacies_group_id`),
  KEY `IDX_77220D7D25F06C53` (`adherent_id`),
  KEY `IDX_77220D7DE7927C74` (`email`),
  CONSTRAINT `FK_77220D7D25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_77220D7DA708DAFF` FOREIGN KEY (`election_id`) REFERENCES `local_election` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_77220D7DFC1537C1` FOREIGN KEY (`candidacies_group_id`) REFERENCES `local_election_candidacies_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local_election_candidacy`
--

LOCK TABLES `local_election_candidacy` WRITE;
/*!40000 ALTER TABLE `local_election_candidacy` DISABLE KEYS */;
/*!40000 ALTER TABLE `local_election_candidacy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local_election_substitute_candidacy`
--

DROP TABLE IF EXISTS `local_election_substitute_candidacy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_election_substitute_candidacy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `candidacies_group_id` int unsigned DEFAULT NULL,
  `election_id` int unsigned NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext COLLATE utf8mb4_unicode_ci,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `faith_statement` longtext COLLATE utf8mb4_unicode_ci,
  `is_public_faith_statement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BD11975AD17F50A6` (`uuid`),
  KEY `IDX_BD11975A25F06C53` (`adherent_id`),
  KEY `IDX_BD11975AFC1537C1` (`candidacies_group_id`),
  KEY `IDX_BD11975AA708DAFF` (`election_id`),
  KEY `IDX_BD11975AE7927C74` (`email`),
  CONSTRAINT `FK_BD11975A25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_BD11975AA708DAFF` FOREIGN KEY (`election_id`) REFERENCES `local_election` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BD11975AFC1537C1` FOREIGN KEY (`candidacies_group_id`) REFERENCES `local_election_candidacies_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local_election_substitute_candidacy`
--

LOCK TABLES `local_election_substitute_candidacy` WRITE;
/*!40000 ALTER TABLE `local_election_substitute_candidacy` DISABLE KEYS */;
/*!40000 ALTER TABLE `local_election_substitute_candidacy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mailchimp_campaign`
--

DROP TABLE IF EXISTS `mailchimp_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailchimp_campaign` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int unsigned DEFAULT NULL,
  `report_id` int unsigned DEFAULT NULL,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `synchronized` tinyint(1) NOT NULL DEFAULT '0',
  `recipient_count` int DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `static_segment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `mailchimp_list_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CFABD3094BD2A4C0` (`report_id`),
  KEY `IDX_CFABD309537A1329` (`message_id`),
  KEY `IDX_CFABD3099F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_CFABD3094BD2A4C0` FOREIGN KEY (`report_id`) REFERENCES `mailchimp_campaign_report` (`id`),
  CONSTRAINT `FK_CFABD309537A1329` FOREIGN KEY (`message_id`) REFERENCES `adherent_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CFABD3099F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mailchimp_campaign`
--

LOCK TABLES `mailchimp_campaign` WRITE;
/*!40000 ALTER TABLE `mailchimp_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `mailchimp_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mailchimp_campaign_mailchimp_segment`
--

DROP TABLE IF EXISTS `mailchimp_campaign_mailchimp_segment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailchimp_campaign_mailchimp_segment` (
  `mailchimp_campaign_id` int unsigned NOT NULL,
  `mailchimp_segment_id` int NOT NULL,
  PRIMARY KEY (`mailchimp_campaign_id`,`mailchimp_segment_id`),
  KEY `IDX_901CE107828112CC` (`mailchimp_campaign_id`),
  KEY `IDX_901CE107D21E482E` (`mailchimp_segment_id`),
  CONSTRAINT `FK_901CE107828112CC` FOREIGN KEY (`mailchimp_campaign_id`) REFERENCES `mailchimp_campaign` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_901CE107D21E482E` FOREIGN KEY (`mailchimp_segment_id`) REFERENCES `mailchimp_segment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mailchimp_campaign_mailchimp_segment`
--

LOCK TABLES `mailchimp_campaign_mailchimp_segment` WRITE;
/*!40000 ALTER TABLE `mailchimp_campaign_mailchimp_segment` DISABLE KEYS */;
/*!40000 ALTER TABLE `mailchimp_campaign_mailchimp_segment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mailchimp_campaign_report`
--

DROP TABLE IF EXISTS `mailchimp_campaign_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailchimp_campaign_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `open_total` int NOT NULL,
  `open_unique` int NOT NULL,
  `open_rate` int NOT NULL,
  `last_open` datetime DEFAULT NULL,
  `click_total` int NOT NULL,
  `click_unique` int NOT NULL,
  `click_rate` int NOT NULL,
  `last_click` datetime DEFAULT NULL,
  `email_sent` int NOT NULL,
  `unsubscribed` int NOT NULL,
  `unsubscribed_rate` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mailchimp_campaign_report`
--

LOCK TABLES `mailchimp_campaign_report` WRITE;
/*!40000 ALTER TABLE `mailchimp_campaign_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `mailchimp_campaign_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mailchimp_segment`
--

DROP TABLE IF EXISTS `mailchimp_segment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailchimp_segment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `list` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mailchimp_segment`
--

LOCK TABLES `mailchimp_segment` WRITE;
/*!40000 ALTER TABLE `mailchimp_segment` DISABLE KEYS */;
/*!40000 ALTER TABLE `mailchimp_segment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medias`
--

DROP TABLE IF EXISTS `medias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medias` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `width` int NOT NULL,
  `height` int NOT NULL,
  `size` bigint NOT NULL,
  `mime_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `compressed_display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_12D2AF81B548B0F` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medias`
--

LOCK TABLES `medias` WRITE;
/*!40000 ALTER TABLE `medias` DISABLE KEYS */;
/*!40000 ALTER TABLE `medias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `version` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES ('Migrations\\Version20211126020536','2023-01-05 09:53:48',87),('Migrations\\Version20211126130535','2023-01-05 09:53:48',358),('Migrations\\Version20211126151434','2023-01-05 09:53:49',12),('Migrations\\Version20211126172212','2023-01-05 09:53:49',16),('Migrations\\Version20211130010431','2023-01-05 09:53:49',88),('Migrations\\Version20211130023420','2023-01-05 09:53:49',18),('Migrations\\Version20211130163308','2023-01-05 09:53:49',171),('Migrations\\Version20211202125517','2023-01-05 09:53:49',28),('Migrations\\Version20211202151633','2023-01-05 09:53:49',169),('Migrations\\Version20211202191607','2023-01-05 09:53:49',16),('Migrations\\Version20211203111253','2023-01-05 09:53:49',59),('Migrations\\Version20211203112333','2023-01-05 09:53:49',11),('Migrations\\Version20211206110133','2023-01-05 09:53:49',52),('Migrations\\Version20211206115819','2023-01-05 09:53:49',11),('Migrations\\Version20211207152117','2023-01-05 09:53:49',149),('Migrations\\Version20211208111523','2023-01-05 09:53:49',89),('Migrations\\Version20211209114932','2023-01-05 09:53:50',158),('Migrations\\Version20211209142923','2023-01-05 09:53:50',59),('Migrations\\Version20211220162832','2023-01-05 09:53:50',41),('Migrations\\Version20220103181110','2023-01-05 09:53:50',371),('Migrations\\Version20220104015613','2023-01-05 09:53:50',120),('Migrations\\Version20220106143719','2023-01-05 09:53:50',30),('Migrations\\Version20220110122036','2023-01-05 09:53:50',147),('Migrations\\Version20220110135715','2023-01-05 09:53:50',52),('Migrations\\Version20220110165351','2023-01-05 09:53:51',19),('Migrations\\Version20220110185032','2023-01-05 09:53:51',141),('Migrations\\Version20220112153457','2023-01-05 09:53:51',15),('Migrations\\Version20220113001351','2023-01-05 09:53:51',9),('Migrations\\Version20220113175738','2023-01-05 09:53:51',2454),('Migrations\\Version20220114104853','2023-01-05 09:53:53',113),('Migrations\\Version20220114151651','2023-01-05 09:53:53',19),('Migrations\\Version20220117173646','2023-01-05 09:53:53',13),('Migrations\\Version20220119094725','2023-01-05 09:53:53',54),('Migrations\\Version20220119160854','2023-01-05 09:53:53',106),('Migrations\\Version20220120173302','2023-01-05 09:53:53',150),('Migrations\\Version20220124162602','2023-01-05 09:53:54',82),('Migrations\\Version20220126182307','2023-01-05 09:53:54',55),('Migrations\\Version20220204141925','2023-01-05 09:53:54',11),('Migrations\\Version20220204164624','2023-01-05 09:53:54',209),('Migrations\\Version20220207135908','2023-01-05 09:53:54',56),('Migrations\\Version20220211115509','2023-01-05 09:53:54',216),('Migrations\\Version20220217113503','2023-01-05 09:53:54',9),('Migrations\\Version20220217124001','2023-01-05 09:53:54',16),('Migrations\\Version20220218125939','2023-01-05 09:53:54',107),('Migrations\\Version20220221172957','2023-01-05 09:53:54',48),('Migrations\\Version20220222082557','2023-01-05 09:53:54',170),('Migrations\\Version20220223181739','2023-01-05 09:53:55',41),('Migrations\\Version20220228120338','2023-01-05 09:53:55',12),('Migrations\\Version20220301133517','2023-01-05 09:53:55',47),('Migrations\\Version20220301190248','2023-01-05 09:53:55',14),('Migrations\\Version20220304164524','2023-01-05 09:53:55',69),('Migrations\\Version20220307111506','2023-01-05 09:53:55',58),('Migrations\\Version20220308175358','2023-01-05 09:53:55',12),('Migrations\\Version20220309113232','2023-01-05 09:53:55',11),('Migrations\\Version20220309113539','2023-01-05 09:53:55',33),('Migrations\\Version20220310121312','2023-01-05 09:53:55',13),('Migrations\\Version20220311152121','2023-01-05 09:53:55',13),('Migrations\\Version20220314141817','2023-01-05 09:53:55',45),('Migrations\\Version20220314155701','2023-01-05 09:53:55',11),('Migrations\\Version20220315005750','2023-01-05 09:53:55',106),('Migrations\\Version20220315170237','2023-01-05 09:53:55',89),('Migrations\\Version20220318201041','2023-01-05 09:53:55',39),('Migrations\\Version20220323165400','2023-01-05 09:53:55',6),('Migrations\\Version20220324112855','2023-01-05 09:53:55',84),('Migrations\\Version20220325172441','2023-01-05 09:53:55',398),('Migrations\\Version20220401171433','2023-01-05 09:53:56',12),('Migrations\\Version20220406110135','2023-01-05 09:53:56',45),('Migrations\\Version20220407142612','2023-01-05 09:53:56',69),('Migrations\\Version20220408144824','2023-01-05 09:53:56',43),('Migrations\\Version20220412120724','2023-01-05 09:53:56',54),('Migrations\\Version20220412172359','2023-01-05 09:53:56',601),('Migrations\\Version20220413192745','2023-01-05 09:53:57',22),('Migrations\\Version20220414170047','2023-01-05 09:53:57',14),('Migrations\\Version20220414211940','2023-01-05 09:53:57',28),('Migrations\\Version20220419193718','2023-01-05 09:53:57',116),('Migrations\\Version20220421132926','2023-01-05 09:53:57',45),('Migrations\\Version20220426163649','2023-01-05 09:53:57',31),('Migrations\\Version20220428150916','2023-01-05 09:53:57',13),('Migrations\\Version20220429114235','2023-01-05 09:53:57',115),('Migrations\\Version20220504182132','2023-01-05 09:53:57',37),('Migrations\\Version20220510102006','2023-01-05 09:53:57',14),('Migrations\\Version20220517152903','2023-01-05 09:53:57',13),('Migrations\\Version20220519181035','2023-01-05 09:53:57',25),('Migrations\\Version20220524105827','2023-01-05 09:53:57',55),('Migrations\\Version20220601181917','2023-01-05 09:53:57',405),('Migrations\\Version20220608011640','2023-01-05 09:53:58',57),('Migrations\\Version20220613112116','2023-01-05 09:53:58',33),('Migrations\\Version20220614100027','2023-01-05 09:53:58',72),('Migrations\\Version20220616095027','2023-01-05 09:53:58',9),('Migrations\\Version20220616140609','2023-01-05 09:53:58',67),('Migrations\\Version20220616154158','2023-01-05 09:53:58',14),('Migrations\\Version20220616165915','2023-01-05 09:53:58',11),('Migrations\\Version20220617190412','2023-01-05 09:53:58',12),('Migrations\\Version20220622122711','2023-01-05 09:53:58',167),('Migrations\\Version20220705022722','2023-01-05 09:53:58',22),('Migrations\\Version20220706000352','2023-01-05 09:53:58',138),('Migrations\\Version20220721160526','2023-01-05 09:53:58',14),('Migrations\\Version20220816180602','2023-01-05 09:53:58',21),('Migrations\\Version20220913155038','2023-01-05 09:53:58',739),('Migrations\\Version20220914170047','2023-01-05 09:53:59',18),('Migrations\\Version20220917042906','2023-01-05 09:53:59',174),('Migrations\\Version20220917135347','2023-01-05 09:53:59',283),('Migrations\\Version20220917153647','2023-01-05 09:53:59',205),('Migrations\\Version20220921132543','2023-01-05 09:54:00',180),('Migrations\\Version20220921152207','2023-01-05 09:54:00',16),('Migrations\\Version20220922172929','2023-01-05 09:54:00',8),('Migrations\\Version20220923100710','2023-01-05 09:54:00',7),('Migrations\\Version20220923154015','2023-01-05 09:54:00',19),('Migrations\\Version20220929173136','2023-01-05 09:54:00',28),('Migrations\\Version20220930151511','2023-01-05 09:54:00',24),('Migrations\\Version20221003152108','2023-01-05 09:54:00',82),('Migrations\\Version20221004115251','2023-01-05 09:54:00',23),('Migrations\\Version20221004155004','2023-01-05 09:54:00',19),('Migrations\\Version20221005132710','2023-01-05 09:54:00',10),('Migrations\\Version20221006135107','2023-01-05 09:54:00',27),('Migrations\\Version20221006182442','2023-01-05 09:54:00',9),('Migrations\\Version20221007154603','2023-01-05 09:54:00',8),('Migrations\\Version20221010123838','2023-01-05 09:54:00',8),('Migrations\\Version20221017232936','2023-01-05 09:54:00',8),('Migrations\\Version20221020113016','2023-01-05 09:54:00',168),('Migrations\\Version20221021140835','2023-01-05 09:54:00',14),('Migrations\\Version20221026085426','2023-01-05 09:54:00',113),('Migrations\\Version20221026160544','2023-01-05 09:54:00',215),('Migrations\\Version20221031150105','2023-01-05 09:54:01',21),('Migrations\\Version20221101210133','2023-01-05 09:54:01',29),('Migrations\\Version20221106232616','2023-01-05 09:54:01',194),('Migrations\\Version20221109170709','2023-01-05 09:54:01',34),('Migrations\\Version20221110000854','2023-01-05 09:54:01',11),('Migrations\\Version20221115082558','2023-01-05 09:54:01',35),('Migrations\\Version20221122094902','2023-01-05 09:54:01',348),('Migrations\\Version20221202153430','2023-01-05 09:54:01',50),('Migrations\\Version20221202161505','2023-01-05 09:54:01',12),('Migrations\\Version20221207092223','2023-01-05 09:54:01',57),('Migrations\\Version20221209122913','2023-01-05 09:54:02',361),('Migrations\\Version20221209162545','2023-01-05 09:54:02',136),('Migrations\\Version20221213112859','2023-01-05 09:54:02',114),('Migrations\\Version20221214111850','2023-01-05 09:54:02',15),('Migrations\\Version20221215150113','2023-01-05 09:54:02',25),('Migrations\\Version20221216123241','2023-01-05 09:54:02',9),('Migrations\\Version20221222094329','2023-01-05 09:54:02',53),('Migrations\\Version20230103112607','2024-09-25 10:41:30',423),('Migrations\\Version20230112170300','2024-09-25 10:41:30',33),('Migrations\\Version20230113162740','2024-09-25 10:41:30',212),('Migrations\\Version20230113181056','2024-09-25 10:41:31',206),('Migrations\\Version20230117112145','2024-09-25 10:41:31',216),('Migrations\\Version20230117175420','2024-09-25 10:41:31',59),('Migrations\\Version20230123015605','2024-09-25 10:41:31',10),('Migrations\\Version20230124144750','2024-09-25 10:41:31',267),('Migrations\\Version20230124152540','2024-09-25 10:41:31',4),('Migrations\\Version20230201185840','2024-09-25 10:41:31',49),('Migrations\\Version20230207011026','2024-09-25 10:41:31',186),('Migrations\\Version20230207174546','2024-09-25 10:41:32',127),('Migrations\\Version20230208105829','2024-09-25 10:41:32',94),('Migrations\\Version20230209153258','2024-09-25 10:41:32',57),('Migrations\\Version20230214143118','2024-09-25 10:41:32',38),('Migrations\\Version20230215084303','2024-09-25 10:41:32',117),('Migrations\\Version20230217112001','2024-09-25 10:41:32',75),('Migrations\\Version20230217142406','2024-09-25 10:41:32',35),('Migrations\\Version20230217143246','2024-09-25 10:41:32',95),('Migrations\\Version20230224152955','2024-09-25 10:41:32',68),('Migrations\\Version20230228165519','2024-09-25 10:41:32',25),('Migrations\\Version20230301155217','2024-09-25 10:41:32',160),('Migrations\\Version20230306120354','2024-09-25 10:41:33',31),('Migrations\\Version20230307142934','2024-09-25 10:41:33',75),('Migrations\\Version20230308172514','2024-09-25 10:41:33',8),('Migrations\\Version20230309121041','2024-09-25 10:41:33',17),('Migrations\\Version20230316170530','2024-09-25 10:41:33',46),('Migrations\\Version20230322144816','2024-09-25 10:41:33',16),('Migrations\\Version20230322174658','2024-09-25 10:41:33',8),('Migrations\\Version20230323111246','2024-09-25 10:41:33',8),('Migrations\\Version20230324113203','2024-09-25 10:41:33',4),('Migrations\\Version20230327141628','2024-09-25 10:41:33',9),('Migrations\\Version20230329012543','2024-09-25 10:41:33',38),('Migrations\\Version20230330153931','2024-09-25 10:41:33',73),('Migrations\\Version20230330173213','2024-09-25 10:41:33',13),('Migrations\\Version20230331125853','2024-09-25 10:41:33',150),('Migrations\\Version20230405125351','2024-09-25 10:41:33',9),('Migrations\\Version20230418095845','2024-09-25 10:41:33',13),('Migrations\\Version20230419141029','2024-09-25 10:41:33',33),('Migrations\\Version20230419170927','2024-09-25 10:41:33',3),('Migrations\\Version20230426174026','2024-09-25 10:41:33',154),('Migrations\\Version20230427153956','2024-09-25 10:41:33',81),('Migrations\\Version20230427165714','2024-09-25 10:41:33',9),('Migrations\\Version20230502141531','2024-09-25 10:41:33',32),('Migrations\\Version20230504151826','2024-09-25 10:41:33',122),('Migrations\\Version20230504163117','2024-09-25 10:41:33',36),('Migrations\\Version20230510153522','2024-09-25 10:41:34',8),('Migrations\\Version20230511181544','2024-09-25 10:41:34',18),('Migrations\\Version20230515153520','2024-09-25 10:41:34',75),('Migrations\\Version20230523012308','2024-09-25 10:41:34',6),('Migrations\\Version20230523023528','2024-09-25 10:41:34',46),('Migrations\\Version20230525133109','2024-09-25 10:41:34',14),('Migrations\\Version20230525135030','2024-09-25 10:41:34',5),('Migrations\\Version20230602161100','2024-09-25 10:41:34',19),('Migrations\\Version20230606133239','2024-09-25 10:41:34',4),('Migrations\\Version20230607155251','2024-09-25 10:41:34',10),('Migrations\\Version20230614123149','2024-09-25 10:41:34',79),('Migrations\\Version20230616145405','2024-09-25 10:41:34',54),('Migrations\\Version20230620145022','2024-09-25 10:41:34',3),('Migrations\\Version20230621084524','2024-09-25 10:41:34',17),('Migrations\\Version20230623073145','2024-09-25 10:41:34',5),('Migrations\\Version20230623101144','2024-09-25 10:41:34',26),('Migrations\\Version20230623103320','2024-09-25 10:41:34',17),('Migrations\\Version20230623153454','2024-09-25 10:41:34',28),('Migrations\\Version20230623173752','2024-09-25 10:41:34',14),('Migrations\\Version20230627233532','2024-09-25 10:41:34',5),('Migrations\\Version20230628150524','2024-09-25 10:41:34',10),('Migrations\\Version20230705072301','2024-09-25 10:41:34',34),('Migrations\\Version20230713081354','2024-09-25 10:41:34',463),('Migrations\\Version20230713171821','2024-09-25 10:41:35',260),('Migrations\\Version20230718161858','2024-09-25 10:41:35',31),('Migrations\\Version20230718180507','2024-09-25 10:41:35',180),('Migrations\\Version20230720163801','2024-09-25 10:41:35',6),('Migrations\\Version20230724100823','2024-09-25 10:41:35',82),('Migrations\\Version20230727095418','2024-09-25 10:41:35',389),('Migrations\\Version20230831072408','2024-09-25 10:41:35',14),('Migrations\\Version20230904142735','2024-09-25 10:41:35',8),('Migrations\\Version20230906080635','2024-09-25 10:41:35',6),('Migrations\\Version20230907113553','2024-09-25 10:41:35',20),('Migrations\\Version20230908112010','2024-09-25 10:41:36',29),('Migrations\\Version20230914080444','2024-09-25 10:41:36',54),('Migrations\\Version20230926123728','2024-09-25 10:41:36',22),('Migrations\\Version20230927161359','2024-09-25 10:41:36',162),('Migrations\\Version20231003095946','2024-09-25 10:41:36',122),('Migrations\\Version20231004161126','2024-09-25 10:41:36',98),('Migrations\\Version20231005220834','2024-09-25 10:41:36',111),('Migrations\\Version20231006082726','2024-09-25 10:41:36',111),('Migrations\\Version20231010151645','2024-09-25 10:41:36',20),('Migrations\\Version20231012080033','2024-09-25 10:41:36',6),('Migrations\\Version20231016232208','2024-09-25 10:41:36',9),('Migrations\\Version20231018081458','2024-09-25 10:41:36',34),('Migrations\\Version20231023161507','2024-09-25 10:41:36',8),('Migrations\\Version20231102091259','2024-09-25 10:41:36',4),('Migrations\\Version20231103172715','2024-09-25 10:41:36',55),('Migrations\\Version20231106102259','2024-09-25 10:41:36',6),('Migrations\\Version20231117170244','2024-09-25 10:41:36',5),('Migrations\\Version20231130142149','2024-09-25 10:41:36',14),('Migrations\\Version20231207093329','2024-09-25 10:41:36',31),('Migrations\\Version20231208002730','2024-09-25 10:41:36',85),('Migrations\\Version20231214003554','2024-09-25 10:41:37',103),('Migrations\\Version20231217142736','2024-09-25 10:41:37',144);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ministry_list_total_result`
--

DROP TABLE IF EXISTS `ministry_list_total_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ministry_list_total_result` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ministry_vote_result_id` int DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nuance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adherent_count` int DEFAULT NULL,
  `eligible_count` int DEFAULT NULL,
  `total` int NOT NULL DEFAULT '0',
  `position` int DEFAULT NULL,
  `candidate_first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `candidate_last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outgoing_mayor` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_99D1332580711B75` (`ministry_vote_result_id`),
  CONSTRAINT `FK_99D1332580711B75` FOREIGN KEY (`ministry_vote_result_id`) REFERENCES `ministry_vote_result` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ministry_list_total_result`
--

LOCK TABLES `ministry_list_total_result` WRITE;
/*!40000 ALTER TABLE `ministry_list_total_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `ministry_list_total_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ministry_vote_result`
--

DROP TABLE IF EXISTS `ministry_vote_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ministry_vote_result` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_round_id` int NOT NULL,
  `city_id` int unsigned DEFAULT NULL,
  `created_by_id` int unsigned DEFAULT NULL,
  `updated_by_id` int unsigned DEFAULT NULL,
  `registered` int NOT NULL,
  `abstentions` int NOT NULL,
  `participated` int NOT NULL,
  `expressed` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ministry_vote_result_city_round_unique` (`city_id`,`election_round_id`),
  KEY `IDX_B9F11DAE896DBBDE` (`updated_by_id`),
  KEY `IDX_B9F11DAE8BAC62AF` (`city_id`),
  KEY `IDX_B9F11DAEB03A8386` (`created_by_id`),
  KEY `IDX_B9F11DAEFCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_B9F11DAE896DBBDE` FOREIGN KEY (`updated_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_B9F11DAE8BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  CONSTRAINT `FK_B9F11DAEB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_B9F11DAEFCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `election_rounds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ministry_vote_result`
--

LOCK TABLES `ministry_vote_result` WRITE;
/*!40000 ALTER TABLE `ministry_vote_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `ministry_vote_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mooc`
--

DROP TABLE IF EXISTS `mooc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mooc` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `article_image_id` int unsigned DEFAULT NULL,
  `list_image_id` int unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `content` varchar(800) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube_duration` time DEFAULT NULL,
  `share_twitter_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `share_facebook_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `share_email_subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `share_email_body` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9D5D3B55989D9B62` (`slug`),
  UNIQUE KEY `UNIQ_9D5D3B5543C8160D` (`list_image_id`),
  UNIQUE KEY `UNIQ_9D5D3B55684DD106` (`article_image_id`),
  CONSTRAINT `FK_9D5D3B5543C8160D` FOREIGN KEY (`list_image_id`) REFERENCES `image` (`id`),
  CONSTRAINT `FK_9D5D3B55684DD106` FOREIGN KEY (`article_image_id`) REFERENCES `image` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mooc`
--

LOCK TABLES `mooc` WRITE;
/*!40000 ALTER TABLE `mooc` DISABLE KEYS */;
/*!40000 ALTER TABLE `mooc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mooc_attachment_file`
--

DROP TABLE IF EXISTS `mooc_attachment_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mooc_attachment_file` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mooc_attachment_file_slug_extension` (`slug`,`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mooc_attachment_file`
--

LOCK TABLES `mooc_attachment_file` WRITE;
/*!40000 ALTER TABLE `mooc_attachment_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `mooc_attachment_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mooc_attachment_link`
--

DROP TABLE IF EXISTS `mooc_attachment_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mooc_attachment_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mooc_attachment_link`
--

LOCK TABLES `mooc_attachment_link` WRITE;
/*!40000 ALTER TABLE `mooc_attachment_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `mooc_attachment_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mooc_chapter`
--

DROP TABLE IF EXISTS `mooc_chapter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mooc_chapter` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `mooc_id` int unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` datetime NOT NULL,
  `position` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A3EDA0D1989D9B62` (`slug`),
  KEY `IDX_A3EDA0D1255EEB87` (`mooc_id`),
  CONSTRAINT `FK_A3EDA0D1255EEB87` FOREIGN KEY (`mooc_id`) REFERENCES `mooc` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mooc_chapter`
--

LOCK TABLES `mooc_chapter` WRITE;
/*!40000 ALTER TABLE `mooc_chapter` DISABLE KEYS */;
/*!40000 ALTER TABLE `mooc_chapter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mooc_element_attachment_file`
--

DROP TABLE IF EXISTS `mooc_element_attachment_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mooc_element_attachment_file` (
  `base_mooc_element_id` int unsigned NOT NULL,
  `attachment_file_id` int unsigned NOT NULL,
  PRIMARY KEY (`base_mooc_element_id`,`attachment_file_id`),
  KEY `IDX_88759A265B5E2CEA` (`attachment_file_id`),
  KEY `IDX_88759A26B1828C9D` (`base_mooc_element_id`),
  CONSTRAINT `FK_88759A265B5E2CEA` FOREIGN KEY (`attachment_file_id`) REFERENCES `mooc_attachment_file` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_88759A26B1828C9D` FOREIGN KEY (`base_mooc_element_id`) REFERENCES `mooc_elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mooc_element_attachment_file`
--

LOCK TABLES `mooc_element_attachment_file` WRITE;
/*!40000 ALTER TABLE `mooc_element_attachment_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `mooc_element_attachment_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mooc_element_attachment_link`
--

DROP TABLE IF EXISTS `mooc_element_attachment_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mooc_element_attachment_link` (
  `base_mooc_element_id` int unsigned NOT NULL,
  `attachment_link_id` int unsigned NOT NULL,
  PRIMARY KEY (`base_mooc_element_id`,`attachment_link_id`),
  KEY `IDX_324635C7653157F7` (`attachment_link_id`),
  KEY `IDX_324635C7B1828C9D` (`base_mooc_element_id`),
  CONSTRAINT `FK_324635C7653157F7` FOREIGN KEY (`attachment_link_id`) REFERENCES `mooc_attachment_link` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_324635C7B1828C9D` FOREIGN KEY (`base_mooc_element_id`) REFERENCES `mooc_elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mooc_element_attachment_link`
--

LOCK TABLES `mooc_element_attachment_link` WRITE;
/*!40000 ALTER TABLE `mooc_element_attachment_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `mooc_element_attachment_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mooc_elements`
--

DROP TABLE IF EXISTS `mooc_elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mooc_elements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `chapter_id` int unsigned DEFAULT NULL,
  `image_id` int unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `youtube_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `position` int NOT NULL,
  `duration` time DEFAULT NULL,
  `typeform_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `share_twitter_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `share_facebook_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `share_email_subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `share_email_body` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mooc_element_slug` (`slug`,`chapter_id`),
  KEY `IDX_691284C53DA5256D` (`image_id`),
  KEY `IDX_691284C5579F4768` (`chapter_id`),
  CONSTRAINT `FK_691284C53DA5256D` FOREIGN KEY (`image_id`) REFERENCES `image` (`id`),
  CONSTRAINT `FK_691284C5579F4768` FOREIGN KEY (`chapter_id`) REFERENCES `mooc_chapter` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mooc_elements`
--

LOCK TABLES `mooc_elements` WRITE;
/*!40000 ALTER TABLE `mooc_elements` DISABLE KEYS */;
/*!40000 ALTER TABLE `mooc_elements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `my_team`
--

DROP TABLE IF EXISTS `my_team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `my_team` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `owner_id` int unsigned NOT NULL,
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4C78F4BD17F50A6` (`uuid`),
  KEY `IDX_4C78F4B7E3C61F9` (`owner_id`),
  CONSTRAINT `FK_4C78F4B7E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `my_team`
--

LOCK TABLES `my_team` WRITE;
/*!40000 ALTER TABLE `my_team` DISABLE KEYS */;
/*!40000 ALTER TABLE `my_team` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `my_team_delegate_access_committee`
--

DROP TABLE IF EXISTS `my_team_delegate_access_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `my_team_delegate_access_committee` (
  `delegated_access_id` int unsigned NOT NULL,
  `committee_id` int unsigned NOT NULL,
  PRIMARY KEY (`delegated_access_id`,`committee_id`),
  KEY `IDX_C52A163FED1A100B` (`committee_id`),
  KEY `IDX_C52A163FFD98FA7A` (`delegated_access_id`),
  CONSTRAINT `FK_C52A163FED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C52A163FFD98FA7A` FOREIGN KEY (`delegated_access_id`) REFERENCES `my_team_delegated_access` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `my_team_delegate_access_committee`
--

LOCK TABLES `my_team_delegate_access_committee` WRITE;
/*!40000 ALTER TABLE `my_team_delegate_access_committee` DISABLE KEYS */;
/*!40000 ALTER TABLE `my_team_delegate_access_committee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `my_team_delegated_access`
--

DROP TABLE IF EXISTS `my_team_delegated_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `my_team_delegated_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `delegator_id` int unsigned DEFAULT NULL,
  `delegated_id` int unsigned DEFAULT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accesses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `restricted_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `scope_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_421C13B9D17F50A6` (`uuid`),
  KEY `IDX_421C13B98825BEFA` (`delegator_id`),
  KEY `IDX_421C13B9B7E7AE18` (`delegated_id`),
  CONSTRAINT `FK_421C13B98825BEFA` FOREIGN KEY (`delegator_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_421C13B9B7E7AE18` FOREIGN KEY (`delegated_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `my_team_delegated_access`
--

LOCK TABLES `my_team_delegated_access` WRITE;
/*!40000 ALTER TABLE `my_team_delegated_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `my_team_delegated_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `my_team_member`
--

DROP TABLE IF EXISTS `my_team_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `my_team_member` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `team_id` int unsigned NOT NULL,
  `adherent_id` int unsigned NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F46A39E9D17F50A6` (`uuid`),
  UNIQUE KEY `team_member_unique` (`team_id`,`adherent_id`),
  KEY `IDX_F46A39E9296CD8AE` (`team_id`),
  KEY `IDX_F46A39E925F06C53` (`adherent_id`),
  CONSTRAINT `FK_F46A39E925F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F46A39E9296CD8AE` FOREIGN KEY (`team_id`) REFERENCES `my_team` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `my_team_member`
--

LOCK TABLES `my_team_member` WRITE;
/*!40000 ALTER TABLE `my_team_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `my_team_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `national_council_candidacies_group`
--

DROP TABLE IF EXISTS `national_council_candidacies_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_council_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_council_candidacies_group`
--

LOCK TABLES `national_council_candidacies_group` WRITE;
/*!40000 ALTER TABLE `national_council_candidacies_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `national_council_candidacies_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `national_council_candidacy`
--

DROP TABLE IF EXISTS `national_council_candidacy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_council_candidacy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `candidacies_group_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned NOT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `quality` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `faith_statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_public_faith_statement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_31A7A205D17F50A6` (`uuid`),
  KEY `IDX_31A7A20525F06C53` (`adherent_id`),
  KEY `IDX_31A7A205A708DAFF` (`election_id`),
  KEY `IDX_31A7A205FC1537C1` (`candidacies_group_id`),
  CONSTRAINT `FK_31A7A20525F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_31A7A205A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `national_council_election` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_31A7A205FC1537C1` FOREIGN KEY (`candidacies_group_id`) REFERENCES `national_council_candidacies_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_council_candidacy`
--

LOCK TABLES `national_council_candidacy` WRITE;
/*!40000 ALTER TABLE `national_council_candidacy` DISABLE KEYS */;
/*!40000 ALTER TABLE `national_council_candidacy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `national_council_election`
--

DROP TABLE IF EXISTS `national_council_election`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_council_election` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `designation_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F3809347D17F50A6` (`uuid`),
  KEY `IDX_F3809347FAC7D83F` (`designation_id`),
  CONSTRAINT `FK_F3809347FAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_council_election`
--

LOCK TABLES `national_council_election` WRITE;
/*!40000 ALTER TABLE `national_council_election` DISABLE KEYS */;
/*!40000 ALTER TABLE `national_council_election` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_invitations`
--

DROP TABLE IF EXISTS `newsletter_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_invitations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_15C94F61D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_subscriptions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_event` tinyint(1) NOT NULL DEFAULT '0',
  `confirmed_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `token` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B3C13B0BE7927C74` (`email`),
  UNIQUE KEY `UNIQ_B3C13B0BD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_B3C13B0B5F37A13B` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_subscriptions`
--

LOCK TABLES `newsletter_subscriptions` WRITE;
/*!40000 ALTER TABLE `newsletter_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `notification_class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `topic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tokens` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `data` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_access_tokens`
--

DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_access_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int unsigned NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `device_id` int unsigned DEFAULT NULL,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `scopes` json NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CA42527C772E836A` (`identifier`),
  UNIQUE KEY `UNIQ_CA42527CD17F50A6` (`uuid`),
  KEY `IDX_CA42527C19EB6921` (`client_id`),
  KEY `IDX_CA42527C94A4C7D4` (`device_id`),
  KEY `IDX_CA42527CA76ED395` (`user_id`),
  CONSTRAINT `FK_CA42527C19EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`),
  CONSTRAINT `FK_CA42527C94A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CA42527CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_access_tokens`
--

LOCK TABLES `oauth_access_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_auth_codes`
--

DROP TABLE IF EXISTS `oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_auth_codes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int unsigned NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `device_id` int unsigned DEFAULT NULL,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `scopes` json NOT NULL,
  `redirect_uri` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BB493F83772E836A` (`identifier`),
  UNIQUE KEY `UNIQ_BB493F83D17F50A6` (`uuid`),
  KEY `IDX_BB493F8319EB6921` (`client_id`),
  KEY `IDX_BB493F8394A4C7D4` (`device_id`),
  KEY `IDX_BB493F83A76ED395` (`user_id`),
  CONSTRAINT `FK_BB493F8319EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`),
  CONSTRAINT `FK_BB493F8394A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BB493F83A76ED395` FOREIGN KEY (`user_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_auth_codes`
--

LOCK TABLES `oauth_auth_codes` WRITE;
/*!40000 ALTER TABLE `oauth_auth_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_auth_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_clients`
--

DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_clients` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_uris` json NOT NULL,
  `secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `allowed_grant_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `supported_scopes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `ask_user_for_authorization` tinyint(1) NOT NULL DEFAULT '1',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `requested_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_13CE8101D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_clients`
--

LOCK TABLES `oauth_clients` WRITE;
/*!40000 ALTER TABLE `oauth_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_refresh_tokens`
--

DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `access_token_id` int unsigned DEFAULT NULL,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5AB687772E836A` (`identifier`),
  UNIQUE KEY `UNIQ_5AB687D17F50A6` (`uuid`),
  KEY `IDX_5AB6872CCB2688` (`access_token_id`),
  CONSTRAINT `FK_5AB6872CCB2688` FOREIGN KEY (`access_token_id`) REFERENCES `oauth_access_tokens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_refresh_tokens`
--

LOCK TABLES `oauth_refresh_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_refresh_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_articles`
--

DROP TABLE IF EXISTS `order_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `position` smallint NOT NULL,
  `published` tinyint(1) NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `twitter_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5E25D3D9989D9B62` (`slug`),
  KEY `IDX_5E25D3D9EA9FDD75` (`media_id`),
  CONSTRAINT `FK_5E25D3D9EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_section_order_article` (
  `order_article_id` int NOT NULL,
  `order_section_id` int NOT NULL,
  PRIMARY KEY (`order_article_id`,`order_section_id`),
  KEY `IDX_A956D4E46BF91E2F` (`order_section_id`),
  KEY `IDX_A956D4E4C14E7BC9` (`order_article_id`),
  CONSTRAINT `FK_A956D4E46BF91E2F` FOREIGN KEY (`order_section_id`) REFERENCES `order_sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A956D4E4C14E7BC9` FOREIGN KEY (`order_article_id`) REFERENCES `order_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_sections`
--

LOCK TABLES `order_sections` WRITE;
/*!40000 ALTER TABLE `order_sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizational_chart_item`
--

DROP TABLE IF EXISTS `organizational_chart_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizational_chart_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tree_root` int unsigned DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lft` int NOT NULL,
  `lvl` int NOT NULL,
  `rgt` int NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_29C1CBAC727ACA70` (`parent_id`),
  KEY `IDX_29C1CBACA977936C` (`tree_root`),
  CONSTRAINT `FK_29C1CBAC727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `organizational_chart_item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_29C1CBACA977936C` FOREIGN KEY (`tree_root`) REFERENCES `organizational_chart_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizational_chart_item`
--

LOCK TABLES `organizational_chart_item` WRITE;
/*!40000 ALTER TABLE `organizational_chart_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `organizational_chart_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `header_media_id` bigint DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_media` tinyint(1) NOT NULL,
  `twitter_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `layout` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2074E575989D9B62` (`slug`),
  KEY `IDX_2074E5755B42DC0F` (`header_media_id`),
  KEY `IDX_2074E575EA9FDD75` (`media_id`),
  CONSTRAINT `FK_2074E5755B42DC0F` FOREIGN KEY (`header_media_id`) REFERENCES `medias` (`id`),
  CONSTRAINT `FK_2074E575EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_address`
--

DROP TABLE IF EXISTS `pap_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_address` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insee_code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offset_x` int DEFAULT NULL,
  `offset_y` int DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `postal_codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `voters_count` smallint unsigned NOT NULL DEFAULT '0',
  `vote_place_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_47071E11D17F50A6` (`uuid`),
  KEY `IDX_47071E114118D12385E16F6B` (`latitude`,`longitude`),
  KEY `IDX_47071E11D8AD1DD1AFAA2D47` (`offset_x`,`offset_y`),
  KEY `IDX_47071E11F3F90B30` (`vote_place_id`),
  CONSTRAINT `FK_47071E11F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `pap_vote_place` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_address`
--

LOCK TABLES `pap_address` WRITE;
/*!40000 ALTER TABLE `pap_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_address_zone`
--

DROP TABLE IF EXISTS `pap_address_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_address_zone` (
  `address_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`address_id`,`zone_id`),
  KEY `IDX_AAFFE1C5F5B7AF75` (`address_id`),
  KEY `IDX_AAFFE1C59F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_AAFFE1C59F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_AAFFE1C5F5B7AF75` FOREIGN KEY (`address_id`) REFERENCES `pap_address` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_address_zone`
--

LOCK TABLES `pap_address_zone` WRITE;
/*!40000 ALTER TABLE `pap_address_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_address_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_building`
--

DROP TABLE IF EXISTS `pap_building`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_building` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `address_id` int unsigned NOT NULL,
  `current_campaign_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_112ABBE1F5B7AF75` (`address_id`),
  UNIQUE KEY `UNIQ_112ABBE1D17F50A6` (`uuid`),
  KEY `IDX_112ABBE148ED5CAD` (`current_campaign_id`),
  CONSTRAINT `FK_112ABBE148ED5CAD` FOREIGN KEY (`current_campaign_id`) REFERENCES `pap_campaign` (`id`),
  CONSTRAINT `FK_112ABBE1F5B7AF75` FOREIGN KEY (`address_id`) REFERENCES `pap_address` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_building`
--

LOCK TABLES `pap_building` WRITE;
/*!40000 ALTER TABLE `pap_building` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_building` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_building_block`
--

DROP TABLE IF EXISTS `pap_building_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_building_block` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `building_id` int unsigned NOT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `building_block_unique` (`name`,`building_id`),
  UNIQUE KEY `UNIQ_61470C81D17F50A6` (`uuid`),
  KEY `IDX_61470C814D2A7E12` (`building_id`),
  KEY `IDX_61470C8185C9D733` (`created_by_adherent_id`),
  KEY `IDX_61470C81DF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_61470C814D2A7E12` FOREIGN KEY (`building_id`) REFERENCES `pap_building` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_61470C8185C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_61470C81DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_building_block`
--

LOCK TABLES `pap_building_block` WRITE;
/*!40000 ALTER TABLE `pap_building_block` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_building_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_building_block_statistics`
--

DROP TABLE IF EXISTS `pap_building_block_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_building_block_statistics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `building_block_id` int unsigned NOT NULL,
  `campaign_id` int unsigned NOT NULL,
  `status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `closed_by_id` int unsigned DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8B79BF60D17F50A6` (`uuid`),
  KEY `IDX_8B79BF6032618357` (`building_block_id`),
  KEY `IDX_8B79BF60F639F774` (`campaign_id`),
  KEY `IDX_8B79BF60E1FA7797` (`closed_by_id`),
  CONSTRAINT `FK_8B79BF6032618357` FOREIGN KEY (`building_block_id`) REFERENCES `pap_building_block` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_8B79BF60E1FA7797` FOREIGN KEY (`closed_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8B79BF60F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_building_block_statistics`
--

LOCK TABLES `pap_building_block_statistics` WRITE;
/*!40000 ALTER TABLE `pap_building_block_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_building_block_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_building_event`
--

DROP TABLE IF EXISTS `pap_building_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_building_event` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `building_id` int unsigned DEFAULT NULL,
  `campaign_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D9F29104D17F50A6` (`uuid`),
  KEY `IDX_D9F291044D2A7E12` (`building_id`),
  KEY `IDX_D9F29104F639F774` (`campaign_id`),
  KEY `IDX_D9F29104F675F31B` (`author_id`),
  CONSTRAINT `FK_D9F291044D2A7E12` FOREIGN KEY (`building_id`) REFERENCES `pap_building` (`id`),
  CONSTRAINT `FK_D9F29104F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`),
  CONSTRAINT `FK_D9F29104F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_building_event`
--

LOCK TABLES `pap_building_event` WRITE;
/*!40000 ALTER TABLE `pap_building_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_building_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_building_statistics`
--

DROP TABLE IF EXISTS `pap_building_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_building_statistics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `building_id` int unsigned NOT NULL,
  `campaign_id` int unsigned NOT NULL,
  `last_passage_done_by_id` int unsigned DEFAULT NULL,
  `status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_passage` datetime DEFAULT NULL,
  `nb_voters` smallint unsigned NOT NULL DEFAULT '0',
  `nb_visited_doors` smallint unsigned NOT NULL DEFAULT '0',
  `nb_surveys` smallint unsigned NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `closed_by_id` int unsigned DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B6FB4E7BD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_B6FB4E7B4D2A7E12F639F774` (`building_id`,`campaign_id`),
  KEY `IDX_B6FB4E7B4D2A7E12` (`building_id`),
  KEY `IDX_B6FB4E7BDCDF6621` (`last_passage_done_by_id`),
  KEY `IDX_B6FB4E7BF639F774` (`campaign_id`),
  KEY `IDX_B6FB4E7BE1FA7797` (`closed_by_id`),
  KEY `IDX_B6FB4E7B7B00651C` (`status`),
  CONSTRAINT `FK_B6FB4E7B4D2A7E12` FOREIGN KEY (`building_id`) REFERENCES `pap_building` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B6FB4E7BDCDF6621` FOREIGN KEY (`last_passage_done_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_B6FB4E7BE1FA7797` FOREIGN KEY (`closed_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_B6FB4E7BF639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_building_statistics`
--

LOCK TABLES `pap_building_statistics` WRITE;
/*!40000 ALTER TABLE `pap_building_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_building_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_campaign`
--

DROP TABLE IF EXISTS `pap_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_campaign` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` int unsigned NOT NULL,
  `administrator_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `brief` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `goal` int NOT NULL,
  `begin_at` datetime DEFAULT NULL,
  `finish_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `visibility` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nb_addresses` int unsigned NOT NULL DEFAULT '0',
  `nb_voters` int unsigned NOT NULL DEFAULT '0',
  `associated` tinyint(1) NOT NULL DEFAULT '0',
  `dynamic_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EF50C8E8D17F50A6` (`uuid`),
  KEY `IDX_EF50C8E84B09E92C` (`administrator_id`),
  KEY `IDX_EF50C8E8B3FE509D` (`survey_id`),
  KEY `IDX_EF50C8E83826374DFE28FD87` (`begin_at`,`finish_at`),
  KEY `IDX_EF50C8E885C9D733` (`created_by_adherent_id`),
  KEY `IDX_EF50C8E8DF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_EF50C8E84B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EF50C8E885C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EF50C8E8B3FE509D` FOREIGN KEY (`survey_id`) REFERENCES `jecoute_survey` (`id`),
  CONSTRAINT `FK_EF50C8E8DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_campaign`
--

LOCK TABLES `pap_campaign` WRITE;
/*!40000 ALTER TABLE `pap_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_campaign_history`
--

DROP TABLE IF EXISTS `pap_campaign_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_campaign_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `questioner_id` int unsigned DEFAULT NULL,
  `campaign_id` int unsigned NOT NULL,
  `data_survey_id` int unsigned DEFAULT NULL,
  `building_id` int unsigned NOT NULL,
  `status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `building_block` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `floor` smallint unsigned DEFAULT NULL,
  `door` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_range` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profession` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_contact` tinyint(1) DEFAULT NULL,
  `to_join` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `finish_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `voter_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voter_postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `begin_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5A3F26F7D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_5A3F26F73C5110AB` (`data_survey_id`),
  KEY `IDX_5A3F26F74D2A7E12` (`building_id`),
  KEY `IDX_5A3F26F7CC0DE6E1` (`questioner_id`),
  KEY `IDX_5A3F26F7F639F774` (`campaign_id`),
  CONSTRAINT `FK_5A3F26F73C5110AB` FOREIGN KEY (`data_survey_id`) REFERENCES `jecoute_data_survey` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_5A3F26F74D2A7E12` FOREIGN KEY (`building_id`) REFERENCES `pap_building` (`id`),
  CONSTRAINT `FK_5A3F26F7CC0DE6E1` FOREIGN KEY (`questioner_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_5A3F26F7F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_campaign_history`
--

LOCK TABLES `pap_campaign_history` WRITE;
/*!40000 ALTER TABLE `pap_campaign_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_campaign_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_campaign_vote_place`
--

DROP TABLE IF EXISTS `pap_campaign_vote_place`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_campaign_vote_place` (
  `campaign_id` int unsigned NOT NULL,
  `vote_place_id` int unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`vote_place_id`),
  KEY `IDX_9803BB72F639F774` (`campaign_id`),
  KEY `IDX_9803BB72F3F90B30` (`vote_place_id`),
  CONSTRAINT `FK_9803BB72F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `pap_vote_place` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9803BB72F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_campaign_vote_place`
--

LOCK TABLES `pap_campaign_vote_place` WRITE;
/*!40000 ALTER TABLE `pap_campaign_vote_place` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_campaign_vote_place` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_campaign_zone`
--

DROP TABLE IF EXISTS `pap_campaign_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_campaign_zone` (
  `campaign_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`campaign_id`,`zone_id`),
  KEY `IDX_E3C93B78F639F774` (`campaign_id`),
  KEY `IDX_E3C93B789F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_A10CFBE59F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A10CFBE5F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_campaign_zone`
--

LOCK TABLES `pap_campaign_zone` WRITE;
/*!40000 ALTER TABLE `pap_campaign_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_campaign_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_floor`
--

DROP TABLE IF EXISTS `pap_floor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_floor` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `building_block_id` int unsigned NOT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `number` smallint unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `floor_unique` (`number`,`building_block_id`),
  UNIQUE KEY `UNIQ_633C3C64D17F50A6` (`uuid`),
  KEY `IDX_633C3C6432618357` (`building_block_id`),
  KEY `IDX_633C3C6485C9D733` (`created_by_adherent_id`),
  KEY `IDX_633C3C64DF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_633C3C6432618357` FOREIGN KEY (`building_block_id`) REFERENCES `pap_building_block` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_633C3C6485C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_633C3C64DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_floor`
--

LOCK TABLES `pap_floor` WRITE;
/*!40000 ALTER TABLE `pap_floor` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_floor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_floor_statistics`
--

DROP TABLE IF EXISTS `pap_floor_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_floor_statistics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `floor_id` int unsigned NOT NULL,
  `campaign_id` int unsigned NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `closed_by_id` int unsigned DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `visited_doors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `nb_surveys` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_853B68C8D17F50A6` (`uuid`),
  KEY `IDX_853B68C8854679E2` (`floor_id`),
  KEY `IDX_853B68C8F639F774` (`campaign_id`),
  KEY `IDX_853B68C8E1FA7797` (`closed_by_id`),
  CONSTRAINT `FK_853B68C8854679E2` FOREIGN KEY (`floor_id`) REFERENCES `pap_floor` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_853B68C8E1FA7797` FOREIGN KEY (`closed_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_853B68C8F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_floor_statistics`
--

LOCK TABLES `pap_floor_statistics` WRITE;
/*!40000 ALTER TABLE `pap_floor_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_floor_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_vote_place`
--

DROP TABLE IF EXISTS `pap_vote_place`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_vote_place` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_addresses` int unsigned NOT NULL DEFAULT '0',
  `nb_voters` int unsigned NOT NULL DEFAULT '0',
  `zone_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E143383FD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_E143383F77153098` (`code`),
  KEY `IDX_E143383F4118D12385E16F6B` (`latitude`,`longitude`),
  KEY `IDX_E143383F9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_E143383F9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_vote_place`
--

LOCK TABLES `pap_vote_place` WRITE;
/*!40000 ALTER TABLE `pap_vote_place` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_vote_place` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_voter`
--

DROP TABLE IF EXISTS `pap_voter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_voter` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `address_id` int unsigned NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `vote_place` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FBF5A013D17F50A6` (`uuid`),
  KEY `IDX_FBF5A013F5B7AF75` (`address_id`),
  CONSTRAINT `FK_FBF5A013F5B7AF75` FOREIGN KEY (`address_id`) REFERENCES `pap_address` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pap_voter`
--

LOCK TABLES `pap_voter` WRITE;
/*!40000 ALTER TABLE `pap_voter` DISABLE KEYS */;
/*!40000 ALTER TABLE `pap_voter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phoning_campaign`
--

DROP TABLE IF EXISTS `phoning_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phoning_campaign` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int unsigned DEFAULT NULL,
  `audience_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `survey_id` int unsigned NOT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `goal` int NOT NULL,
  `finish_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `brief` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `permanent` tinyint(1) NOT NULL DEFAULT '0',
  `participants_count` int NOT NULL DEFAULT '0',
  `zone_id` int unsigned DEFAULT NULL,
  `visibility` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dynamic_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C3882BA4D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_C3882BA4848CC616` (`audience_id`),
  KEY `IDX_C3882BA4296CD8AE` (`team_id`),
  KEY `IDX_C3882BA485C9D733` (`created_by_adherent_id`),
  KEY `IDX_C3882BA49DF5350C` (`created_by_administrator_id`),
  KEY `IDX_C3882BA4B3FE509D` (`survey_id`),
  KEY `IDX_C3882BA4CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_C3882BA4DF6CFDC9` (`updated_by_adherent_id`),
  KEY `IDX_C3882BA49F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_C3882BA4296CD8AE` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C3882BA4848CC616` FOREIGN KEY (`audience_id`) REFERENCES `audience_snapshot` (`id`),
  CONSTRAINT `FK_C3882BA485C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C3882BA49DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C3882BA49F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_C3882BA4B3FE509D` FOREIGN KEY (`survey_id`) REFERENCES `jecoute_survey` (`id`),
  CONSTRAINT `FK_C3882BA4CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C3882BA4DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phoning_campaign`
--

LOCK TABLES `phoning_campaign` WRITE;
/*!40000 ALTER TABLE `phoning_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `phoning_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phoning_campaign_history`
--

DROP TABLE IF EXISTS `phoning_campaign_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phoning_campaign_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_survey_id` int unsigned DEFAULT NULL,
  `caller_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `campaign_id` int unsigned NOT NULL,
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code_checked` tinyint(1) DEFAULT NULL,
  `begin_at` datetime NOT NULL,
  `finish_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `need_email_renewal` tinyint(1) DEFAULT NULL,
  `need_sms_renewal` tinyint(1) DEFAULT NULL,
  `engagement` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` smallint unsigned DEFAULT NULL,
  `profession` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EC191198D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_EC1911983C5110AB` (`data_survey_id`),
  KEY `IDX_EC19119825F06C53` (`adherent_id`),
  KEY `IDX_EC191198A5626C52` (`caller_id`),
  KEY `IDX_EC191198F639F774` (`campaign_id`),
  CONSTRAINT `FK_EC19119825F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EC1911983C5110AB` FOREIGN KEY (`data_survey_id`) REFERENCES `jecoute_data_survey` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EC191198A5626C52` FOREIGN KEY (`caller_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EC191198F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `phoning_campaign` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phoning_campaign_history`
--

LOCK TABLES `phoning_campaign_history` WRITE;
/*!40000 ALTER TABLE `phoning_campaign_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `phoning_campaign_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `political_committee`
--

DROP TABLE IF EXISTS `political_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `political_committee` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `territorial_council_id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_39FAEE955E237E06` (`name`),
  UNIQUE KEY `UNIQ_39FAEE95AAA61A99` (`territorial_council_id`),
  UNIQUE KEY `UNIQ_39FAEE95D17F50A6` (`uuid`),
  CONSTRAINT `FK_39FAEE95AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `political_committee`
--

LOCK TABLES `political_committee` WRITE;
/*!40000 ALTER TABLE `political_committee` DISABLE KEYS */;
/*!40000 ALTER TABLE `political_committee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `political_committee_feed_item`
--

DROP TABLE IF EXISTS `political_committee_feed_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `political_committee_feed_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `political_committee_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_54369E83D17F50A6` (`uuid`),
  KEY `IDX_54369E83C7A72` (`political_committee_id`),
  KEY `IDX_54369E83F675F31B` (`author_id`),
  CONSTRAINT `FK_54369E83C7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_54369E83F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `political_committee_feed_item`
--

LOCK TABLES `political_committee_feed_item` WRITE;
/*!40000 ALTER TABLE `political_committee_feed_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `political_committee_feed_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `political_committee_membership`
--

DROP TABLE IF EXISTS `political_committee_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `political_committee_membership` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `political_committee_id` int unsigned NOT NULL,
  `joined_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `is_additional` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FD85437B25F06C53` (`adherent_id`),
  UNIQUE KEY `UNIQ_FD85437BD17F50A6` (`uuid`),
  KEY `IDX_FD85437BC7A72` (`political_committee_id`),
  CONSTRAINT `FK_FD85437B25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FD85437BC7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `political_committee_membership`
--

LOCK TABLES `political_committee_membership` WRITE;
/*!40000 ALTER TABLE `political_committee_membership` DISABLE KEYS */;
/*!40000 ALTER TABLE `political_committee_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `political_committee_quality`
--

DROP TABLE IF EXISTS `political_committee_quality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `political_committee_quality` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `political_committee_membership_id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `joined_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_243D6D3A78632915` (`political_committee_membership_id`),
  CONSTRAINT `FK_243D6D3A78632915` FOREIGN KEY (`political_committee_membership_id`) REFERENCES `political_committee_membership` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `political_committee_quality`
--

LOCK TABLES `political_committee_quality` WRITE;
/*!40000 ALTER TABLE `political_committee_quality` DISABLE KEYS */;
/*!40000 ALTER TABLE `political_committee_quality` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poll`
--

DROP TABLE IF EXISTS `poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `poll` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `administrator_id` int DEFAULT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `question` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `finish_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_84BCFA45D17F50A6` (`uuid`),
  KEY `IDX_84BCFA454B09E92C` (`administrator_id`),
  KEY `IDX_84BCFA459F2C3FAB` (`zone_id`),
  KEY `IDX_84BCFA45F675F31B` (`author_id`),
  CONSTRAINT `FK_84BCFA454B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_84BCFA459F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_84BCFA45F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poll`
--

LOCK TABLES `poll` WRITE;
/*!40000 ALTER TABLE `poll` DISABLE KEYS */;
/*!40000 ALTER TABLE `poll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poll_choice`
--

DROP TABLE IF EXISTS `poll_choice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `poll_choice` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` int unsigned NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2DAE19C9D17F50A6` (`uuid`),
  KEY `IDX_2DAE19C93C947C0F` (`poll_id`),
  CONSTRAINT `FK_2DAE19C93C947C0F` FOREIGN KEY (`poll_id`) REFERENCES `poll` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poll_choice`
--

LOCK TABLES `poll_choice` WRITE;
/*!40000 ALTER TABLE `poll_choice` DISABLE KEYS */;
/*!40000 ALTER TABLE `poll_choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poll_vote`
--

DROP TABLE IF EXISTS `poll_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `poll_vote` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `choice_id` int unsigned NOT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `device_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_ED568EBE25F06C53` (`adherent_id`),
  KEY `IDX_ED568EBE94A4C7D4` (`device_id`),
  KEY `IDX_ED568EBE998666D1` (`choice_id`),
  CONSTRAINT `FK_ED568EBE25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_ED568EBE94A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_ED568EBE998666D1` FOREIGN KEY (`choice_id`) REFERENCES `poll_choice` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poll_vote`
--

LOCK TABLES `poll_vote` WRITE;
/*!40000 ALTER TABLE `poll_vote` DISABLE KEYS */;
/*!40000 ALTER TABLE `poll_vote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_managed_areas`
--

DROP TABLE IF EXISTS `procuration_managed_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_managed_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_proxies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_names` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date DEFAULT NULL,
  `vote_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vote_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vote_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vote_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vote_office` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `reliability` smallint NOT NULL,
  `disabled` tinyint(1) NOT NULL,
  `reliability_description` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proxies_count` smallint unsigned NOT NULL DEFAULT '1',
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reachable` tinyint(1) NOT NULL DEFAULT '0',
  `voter_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `backup_other_vote_cities` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `disabled_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9B5E777AD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_proxies`
--

LOCK TABLES `procuration_proxies` WRITE;
/*!40000 ALTER TABLE `procuration_proxies` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_proxies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_proxies_to_election_rounds`
--

DROP TABLE IF EXISTS `procuration_proxies_to_election_rounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_proxies_to_election_rounds` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `procuration_proxy_id` int unsigned NOT NULL,
  `election_round_id` int NOT NULL,
  `french_request_available` tinyint(1) NOT NULL DEFAULT '1',
  `foreign_request_available` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `procuration_proxy_election_round_unique` (`procuration_proxy_id`,`election_round_id`),
  KEY `IDX_D075F5A9E15E419B` (`procuration_proxy_id`),
  KEY `IDX_D075F5A9FCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_D075F5A9E15E419B` FOREIGN KEY (`procuration_proxy_id`) REFERENCES `procuration_proxies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D075F5A9FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `election_rounds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_proxies_to_election_rounds`
--

LOCK TABLES `procuration_proxies_to_election_rounds` WRITE;
/*!40000 ALTER TABLE `procuration_proxies_to_election_rounds` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_proxies_to_election_rounds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_proxy_zone`
--

DROP TABLE IF EXISTS `procuration_proxy_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_proxy_zone` (
  `procuration_proxy_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`procuration_proxy_id`,`zone_id`),
  KEY `IDX_5AE81518E15E419B` (`procuration_proxy_id`),
  KEY `IDX_5AE815189F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_5AE815189F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5AE81518E15E419B` FOREIGN KEY (`procuration_proxy_id`) REFERENCES `procuration_proxies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_proxy_zone`
--

LOCK TABLES `procuration_proxy_zone` WRITE;
/*!40000 ALTER TABLE `procuration_proxy_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_proxy_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_requests`
--

DROP TABLE IF EXISTS `procuration_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `procuration_request_found_by_id` int unsigned DEFAULT NULL,
  `found_proxy_id` int unsigned DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_names` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date DEFAULT NULL,
  `vote_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vote_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vote_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vote_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vote_office` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `processed` tinyint(1) NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `request_from_france` tinyint(1) NOT NULL DEFAULT '1',
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reachable` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `disabled_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminded_at` datetime DEFAULT NULL,
  `voter_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9769FD842F1B6663` (`found_proxy_id`),
  KEY `IDX_9769FD84888FDEEE` (`procuration_request_found_by_id`),
  CONSTRAINT `FK_9769FD842F1B6663` FOREIGN KEY (`found_proxy_id`) REFERENCES `procuration_proxies` (`id`),
  CONSTRAINT `FK_9769FD84888FDEEE` FOREIGN KEY (`procuration_request_found_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_requests`
--

LOCK TABLES `procuration_requests` WRITE;
/*!40000 ALTER TABLE `procuration_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_requests_to_election_rounds`
--

DROP TABLE IF EXISTS `procuration_requests_to_election_rounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_requests_to_election_rounds` (
  `procuration_request_id` int NOT NULL,
  `election_round_id` int NOT NULL,
  PRIMARY KEY (`procuration_request_id`,`election_round_id`),
  KEY `IDX_A47BBD53128D9C53` (`procuration_request_id`),
  KEY `IDX_A47BBD53FCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_A47BBD53128D9C53` FOREIGN KEY (`procuration_request_id`) REFERENCES `procuration_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A47BBD53FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `election_rounds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_requests_to_election_rounds`
--

LOCK TABLES `procuration_requests_to_election_rounds` WRITE;
/*!40000 ALTER TABLE `procuration_requests_to_election_rounds` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_requests_to_election_rounds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmatic_foundation_approach`
--

DROP TABLE IF EXISTS `programmatic_foundation_approach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmatic_foundation_approach` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `position` smallint NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8B785227D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmatic_foundation_approach`
--

LOCK TABLES `programmatic_foundation_approach` WRITE;
/*!40000 ALTER TABLE `programmatic_foundation_approach` DISABLE KEYS */;
/*!40000 ALTER TABLE `programmatic_foundation_approach` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmatic_foundation_measure`
--

DROP TABLE IF EXISTS `programmatic_foundation_measure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmatic_foundation_measure` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sub_approach_id` int unsigned DEFAULT NULL,
  `position` smallint NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_leading` tinyint(1) NOT NULL,
  `is_expanded` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_213A5F1ED17F50A6` (`uuid`),
  KEY `IDX_213A5F1EF0ED738A` (`sub_approach_id`),
  CONSTRAINT `FK_213A5F1EF0ED738A` FOREIGN KEY (`sub_approach_id`) REFERENCES `programmatic_foundation_sub_approach` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmatic_foundation_measure`
--

LOCK TABLES `programmatic_foundation_measure` WRITE;
/*!40000 ALTER TABLE `programmatic_foundation_measure` DISABLE KEYS */;
/*!40000 ALTER TABLE `programmatic_foundation_measure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmatic_foundation_measure_tag`
--

DROP TABLE IF EXISTS `programmatic_foundation_measure_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmatic_foundation_measure_tag` (
  `measure_id` int unsigned NOT NULL,
  `tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`measure_id`,`tag_id`),
  KEY `IDX_F004297F5DA37D00` (`measure_id`),
  KEY `IDX_F004297FBAD26311` (`tag_id`),
  CONSTRAINT `FK_F004297F5DA37D00` FOREIGN KEY (`measure_id`) REFERENCES `programmatic_foundation_measure` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F004297FBAD26311` FOREIGN KEY (`tag_id`) REFERENCES `programmatic_foundation_tag` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmatic_foundation_measure_tag`
--

LOCK TABLES `programmatic_foundation_measure_tag` WRITE;
/*!40000 ALTER TABLE `programmatic_foundation_measure_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `programmatic_foundation_measure_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmatic_foundation_project`
--

DROP TABLE IF EXISTS `programmatic_foundation_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmatic_foundation_project` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `measure_id` int unsigned DEFAULT NULL,
  `position` smallint NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_expanded` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8E8E96D5D17F50A6` (`uuid`),
  KEY `IDX_8E8E96D55DA37D00` (`measure_id`),
  CONSTRAINT `FK_8E8E96D55DA37D00` FOREIGN KEY (`measure_id`) REFERENCES `programmatic_foundation_measure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmatic_foundation_project`
--

LOCK TABLES `programmatic_foundation_project` WRITE;
/*!40000 ALTER TABLE `programmatic_foundation_project` DISABLE KEYS */;
/*!40000 ALTER TABLE `programmatic_foundation_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmatic_foundation_project_tag`
--

DROP TABLE IF EXISTS `programmatic_foundation_project_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmatic_foundation_project_tag` (
  `project_id` int unsigned NOT NULL,
  `tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`project_id`,`tag_id`),
  KEY `IDX_9F63872166D1F9C` (`project_id`),
  KEY `IDX_9F63872BAD26311` (`tag_id`),
  CONSTRAINT `FK_9F63872166D1F9C` FOREIGN KEY (`project_id`) REFERENCES `programmatic_foundation_project` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9F63872BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `programmatic_foundation_tag` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmatic_foundation_project_tag`
--

LOCK TABLES `programmatic_foundation_project_tag` WRITE;
/*!40000 ALTER TABLE `programmatic_foundation_project_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `programmatic_foundation_project_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmatic_foundation_sub_approach`
--

DROP TABLE IF EXISTS `programmatic_foundation_sub_approach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmatic_foundation_sub_approach` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `approach_id` int unsigned DEFAULT NULL,
  `position` smallint NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_expanded` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_735C1D01D17F50A6` (`uuid`),
  KEY `IDX_735C1D0115140614` (`approach_id`),
  CONSTRAINT `FK_735C1D0115140614` FOREIGN KEY (`approach_id`) REFERENCES `programmatic_foundation_approach` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmatic_foundation_sub_approach`
--

LOCK TABLES `programmatic_foundation_sub_approach` WRITE;
/*!40000 ALTER TABLE `programmatic_foundation_sub_approach` DISABLE KEYS */;
/*!40000 ALTER TABLE `programmatic_foundation_sub_approach` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmatic_foundation_tag`
--

DROP TABLE IF EXISTS `programmatic_foundation_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmatic_foundation_tag` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_12127927EA750E8` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmatic_foundation_tag`
--

LOCK TABLES `programmatic_foundation_tag` WRITE;
/*!40000 ALTER TABLE `programmatic_foundation_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `programmatic_foundation_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projection_managed_users`
--

DROP TABLE IF EXISTS `projection_managed_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projection_managed_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `status` smallint NOT NULL,
  `original_id` bigint unsigned NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` smallint DEFAULT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `committees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_committee_member` tinyint(1) NOT NULL,
  `is_committee_host` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `is_committee_supervisor` tinyint(1) NOT NULL,
  `subscribed_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `committee_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `supervisor_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `subscription_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `adherent_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `committee_uuids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `vote_committee_id` int DEFAULT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certified_at` datetime DEFAULT NULL,
  `is_committee_provisional_supervisor` tinyint(1) NOT NULL,
  `adherent_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_membership_donation` datetime DEFAULT NULL,
  `committee` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `committee_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `nationality` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `cotisation_dates` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `campus_registered_at` datetime DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `mandates` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `declared_mandates` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `zones_ids` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_tags` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  KEY `IDX_90A7D656108B7592` (`original_id`),
  KEY `IDX_90A7D6567B00651C` (`status`),
  KEY `IDX_90A7D656AB78BDC2` (`zones_ids`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projection_managed_users`
--

LOCK TABLES `projection_managed_users` WRITE;
/*!40000 ALTER TABLE `projection_managed_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `projection_managed_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projection_managed_users_zone`
--

DROP TABLE IF EXISTS `projection_managed_users_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projection_managed_users_zone` (
  `managed_user_id` bigint unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`managed_user_id`,`zone_id`),
  KEY `IDX_E4D4ADCD9F2C3FAB` (`zone_id`),
  KEY `IDX_E4D4ADCDC679DD78` (`managed_user_id`),
  CONSTRAINT `FK_E4D4ADCD9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E4D4ADCDC679DD78` FOREIGN KEY (`managed_user_id`) REFERENCES `projection_managed_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projection_managed_users_zone`
--

LOCK TABLES `projection_managed_users_zone` WRITE;
/*!40000 ALTER TABLE `projection_managed_users_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `projection_managed_users_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proposal_proposal_theme`
--

DROP TABLE IF EXISTS `proposal_proposal_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proposal_proposal_theme` (
  `proposal_id` int NOT NULL,
  `proposal_theme_id` int NOT NULL,
  PRIMARY KEY (`proposal_id`,`proposal_theme_id`),
  KEY `IDX_6B80CE41B85948AF` (`proposal_theme_id`),
  KEY `IDX_6B80CE41F4792058` (`proposal_id`),
  CONSTRAINT `FK_6B80CE41B85948AF` FOREIGN KEY (`proposal_theme_id`) REFERENCES `proposals_themes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6B80CE41F4792058` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proposals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `position` smallint NOT NULL,
  `published` tinyint(1) NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A5BA3A8F989D9B62` (`slug`),
  KEY `IDX_A5BA3A8FEA9FDD75` (`media_id`),
  CONSTRAINT `FK_A5BA3A8FEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proposals_themes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proposals_themes`
--

LOCK TABLES `proposals_themes` WRITE;
/*!40000 ALTER TABLE `proposals_themes` DISABLE KEYS */;
/*!40000 ALTER TABLE `proposals_themes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `push_token`
--

DROP TABLE IF EXISTS `push_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_token` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `device_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_51BC1381772E836A` (`identifier`),
  UNIQUE KEY `UNIQ_51BC1381D17F50A6` (`uuid`),
  KEY `IDX_51BC138125F06C53` (`adherent_id`),
  KEY `IDX_51BC138194A4C7D4` (`device_id`),
  CONSTRAINT `FK_51BC138125F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_51BC138194A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `push_token`
--

LOCK TABLES `push_token` WRITE;
/*!40000 ALTER TABLE `push_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qr_code`
--

DROP TABLE IF EXISTS `qr_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `qr_code` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `count` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7D8B1FB55E237E06` (`name`),
  UNIQUE KEY `UNIQ_7D8B1FB5D17F50A6` (`uuid`),
  KEY `IDX_7D8B1FB5B03A8386` (`created_by_id`),
  CONSTRAINT `FK_7D8B1FB5B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qr_code`
--

LOCK TABLES `qr_code` WRITE;
/*!40000 ALTER TABLE `qr_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `qr_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redirections`
--

DROP TABLE IF EXISTS `redirections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redirections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `url_from` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_to` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` int NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebook_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geojson` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `area_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DISABLED',
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_media` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FE9AAC6C989D9B62` (`slug`),
  KEY `IDX_FE9AAC6CEA9FDD75` (`media_id`),
  CONSTRAINT `FK_FE9AAC6CEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_area` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `area_code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AB758097B5501F87` (`area_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_areas` (
  `referent_id` smallint unsigned NOT NULL,
  `area_id` smallint unsigned NOT NULL,
  PRIMARY KEY (`referent_id`,`area_id`),
  KEY `IDX_75CEBC6C35E47E35` (`referent_id`),
  KEY `IDX_75CEBC6CBD0F409C` (`area_id`),
  CONSTRAINT `FK_75CEBC6C35E47E35` FOREIGN KEY (`referent_id`) REFERENCES `referent` (`id`),
  CONSTRAINT `FK_75CEBC6CBD0F409C` FOREIGN KEY (`area_id`) REFERENCES `referent_area` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_areas`
--

LOCK TABLES `referent_areas` WRITE;
/*!40000 ALTER TABLE `referent_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_managed_areas`
--

DROP TABLE IF EXISTS `referent_managed_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_managed_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marker_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `marker_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_managed_areas`
--

LOCK TABLES `referent_managed_areas` WRITE;
/*!40000 ALTER TABLE `referent_managed_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_managed_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_managed_areas_tags`
--

DROP TABLE IF EXISTS `referent_managed_areas_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_managed_areas_tags` (
  `referent_managed_area_id` int NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`referent_managed_area_id`,`referent_tag_id`),
  KEY `IDX_8BE84DD56B99CC25` (`referent_managed_area_id`),
  KEY `IDX_8BE84DD59C262DB3` (`referent_tag_id`),
  CONSTRAINT `FK_8BE84DD56B99CC25` FOREIGN KEY (`referent_managed_area_id`) REFERENCES `referent_managed_areas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_8BE84DD59C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_managed_areas_tags`
--

LOCK TABLES `referent_managed_areas_tags` WRITE;
/*!40000 ALTER TABLE `referent_managed_areas_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_managed_areas_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_person_link`
--

DROP TABLE IF EXISTS `referent_person_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_person_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `person_organizational_chart_item_id` int unsigned DEFAULT NULL,
  `referent_id` smallint unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_jecoute_manager` tinyint(1) NOT NULL DEFAULT '0',
  `co_referent` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `restricted_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  KEY `IDX_BC75A60A25F06C53` (`adherent_id`),
  KEY `IDX_BC75A60A35E47E35` (`referent_id`),
  KEY `IDX_BC75A60A810B5A42` (`person_organizational_chart_item_id`),
  CONSTRAINT `FK_BC75A60A25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BC75A60A35E47E35` FOREIGN KEY (`referent_id`) REFERENCES `referent` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BC75A60A810B5A42` FOREIGN KEY (`person_organizational_chart_item_id`) REFERENCES `organizational_chart_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_person_link`
--

LOCK TABLES `referent_person_link` WRITE;
/*!40000 ALTER TABLE `referent_person_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_person_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_person_link_committee`
--

DROP TABLE IF EXISTS `referent_person_link_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_person_link_committee` (
  `referent_person_link_id` int unsigned NOT NULL,
  `committee_id` int unsigned NOT NULL,
  PRIMARY KEY (`referent_person_link_id`,`committee_id`),
  KEY `IDX_1C97B2A5B3E4DE86` (`referent_person_link_id`),
  KEY `IDX_1C97B2A5ED1A100B` (`committee_id`),
  CONSTRAINT `FK_1C97B2A5B3E4DE86` FOREIGN KEY (`referent_person_link_id`) REFERENCES `referent_person_link` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1C97B2A5ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_person_link_committee`
--

LOCK TABLES `referent_person_link_committee` WRITE;
/*!40000 ALTER TABLE `referent_person_link_committee` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_person_link_committee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_space_access_information`
--

DROP TABLE IF EXISTS `referent_space_access_information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_space_access_information` (
  `id` int NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `previous_date` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `last_date` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CD8FDF4825F06C53` (`adherent_id`),
  CONSTRAINT `FK_CD8FDF4825F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_space_access_information`
--

LOCK TABLES `referent_space_access_information` WRITE;
/*!40000 ALTER TABLE `referent_space_access_information` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_space_access_information` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_tags`
--

DROP TABLE IF EXISTS `referent_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_tags` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_135D29D977153098` (`code`),
  UNIQUE KEY `UNIQ_135D29D95E237E06` (`name`),
  KEY `IDX_135D29D99F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_135D29D99F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_tags`
--

LOCK TABLES `referent_tags` WRITE;
/*!40000 ALTER TABLE `referent_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_team_member`
--

DROP TABLE IF EXISTS `referent_team_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_team_member` (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_id` int unsigned NOT NULL,
  `referent_id` int unsigned NOT NULL,
  `limited` tinyint(1) NOT NULL DEFAULT '0',
  `restricted_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6C006717597D3FE` (`member_id`),
  KEY `IDX_6C0067135E47E35` (`referent_id`),
  CONSTRAINT `FK_6C0067135E47E35` FOREIGN KEY (`referent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6C006717597D3FE` FOREIGN KEY (`member_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_team_member`
--

LOCK TABLES `referent_team_member` WRITE;
/*!40000 ALTER TABLE `referent_team_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_team_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_team_member_committee`
--

DROP TABLE IF EXISTS `referent_team_member_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_team_member_committee` (
  `referent_team_member_id` int NOT NULL,
  `committee_id` int unsigned NOT NULL,
  PRIMARY KEY (`referent_team_member_id`,`committee_id`),
  KEY `IDX_EC89860BED1A100B` (`committee_id`),
  KEY `IDX_EC89860BFE4CA267` (`referent_team_member_id`),
  CONSTRAINT `FK_EC89860BED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EC89860BFE4CA267` FOREIGN KEY (`referent_team_member_id`) REFERENCES `referent_team_member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_team_member_committee`
--

LOCK TABLES `referent_team_member_committee` WRITE;
/*!40000 ALTER TABLE `referent_team_member_committee` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_team_member_committee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referent_user_filter_referent_tag`
--

DROP TABLE IF EXISTS `referent_user_filter_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_user_filter_referent_tag` (
  `referent_user_filter_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`referent_user_filter_id`,`referent_tag_id`),
  KEY `IDX_F2BB20FE9C262DB3` (`referent_tag_id`),
  KEY `IDX_F2BB20FEEFAB50C4` (`referent_user_filter_id`),
  CONSTRAINT `FK_F2BB20FE9C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F2BB20FEEFAB50C4` FOREIGN KEY (`referent_user_filter_id`) REFERENCES `adherent_message_filters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referent_user_filter_referent_tag`
--

LOCK TABLES `referent_user_filter_referent_tag` WRITE;
/*!40000 ALTER TABLE `referent_user_filter_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `referent_user_filter_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `region`
--

DROP TABLE IF EXISTS `region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `region` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F62F17677153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `region`
--

LOCK TABLES `region` WRITE;
/*!40000 ALTER TABLE `region` DISABLE KEYS */;
/*!40000 ALTER TABLE `region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rememberme_token`
--

DROP TABLE IF EXISTS `rememberme_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rememberme_token` (
  `series` varchar(88) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(88) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastUsed` datetime NOT NULL,
  `class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`series`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rememberme_token`
--

LOCK TABLES `rememberme_token` WRITE;
/*!40000 ALTER TABLE `rememberme_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `rememberme_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `renaissance_newsletter_subscription`
--

DROP TABLE IF EXISTS `renaissance_newsletter_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `renaissance_newsletter_subscription` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `token` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_46DB1A77E7927C74` (`email`),
  UNIQUE KEY `UNIQ_46DB1A77D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `renaissance_newsletter_subscription`
--

LOCK TABLES `renaissance_newsletter_subscription` WRITE;
/*!40000 ALTER TABLE `renaissance_newsletter_subscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `renaissance_newsletter_subscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
  `committee_id` int unsigned DEFAULT NULL,
  `community_event_id` int unsigned DEFAULT NULL,
  `reasons` json NOT NULL,
  `comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unresolved',
  `created_at` datetime NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F11FA745D17F50A6` (`uuid`),
  KEY `IDX_F11FA74583B12DAC` (`community_event_id`),
  KEY `IDX_F11FA745ED1A100B` (`committee_id`),
  KEY `IDX_F11FA745F675F31B` (`author_id`),
  KEY `report_status_idx` (`status`),
  KEY `report_type_idx` (`type`),
  CONSTRAINT `FK_F11FA74583B12DAC` FOREIGN KEY (`community_event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F11FA745ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F11FA745F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `republican_silence`
--

DROP TABLE IF EXISTS `republican_silence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `republican_silence` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `begin_at` datetime NOT NULL,
  `finish_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `republican_silence`
--

LOCK TABLES `republican_silence` WRITE;
/*!40000 ALTER TABLE `republican_silence` DISABLE KEYS */;
/*!40000 ALTER TABLE `republican_silence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `republican_silence_zone`
--

DROP TABLE IF EXISTS `republican_silence_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `republican_silence_zone` (
  `republican_silence_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`republican_silence_id`,`zone_id`),
  KEY `IDX_9197540D12359909` (`republican_silence_id`),
  KEY `IDX_9197540D9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_9197540D12359909` FOREIGN KEY (`republican_silence_id`) REFERENCES `republican_silence` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9197540D9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `republican_silence_zone`
--

LOCK TABLES `republican_silence_zone` WRITE;
/*!40000 ALTER TABLE `republican_silence_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `republican_silence_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B63E2EC777153098` (`code`),
  UNIQUE KEY `UNIQ_B63E2EC75E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `running_mate_request_application_request_tag`
--

DROP TABLE IF EXISTS `running_mate_request_application_request_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `running_mate_request_application_request_tag` (
  `running_mate_request_id` int unsigned NOT NULL,
  `application_request_tag_id` int NOT NULL,
  PRIMARY KEY (`running_mate_request_id`,`application_request_tag_id`),
  KEY `IDX_9D534FCF9644FEDA` (`application_request_tag_id`),
  KEY `IDX_9D534FCFCEDF4387` (`running_mate_request_id`),
  CONSTRAINT `FK_9D534FCF9644FEDA` FOREIGN KEY (`application_request_tag_id`) REFERENCES `application_request_tag` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9D534FCFCEDF4387` FOREIGN KEY (`running_mate_request_id`) REFERENCES `application_request_running_mate` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `running_mate_request_application_request_tag`
--

LOCK TABLES `running_mate_request_application_request_tag` WRITE;
/*!40000 ALTER TABLE `running_mate_request_application_request_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `running_mate_request_application_request_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `running_mate_request_referent_tag`
--

DROP TABLE IF EXISTS `running_mate_request_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `running_mate_request_referent_tag` (
  `running_mate_request_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`running_mate_request_id`,`referent_tag_id`),
  KEY `IDX_53AB4FAB9C262DB3` (`referent_tag_id`),
  KEY `IDX_53AB4FABCEDF4387` (`running_mate_request_id`),
  CONSTRAINT `FK_53AB4FAB9C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_53AB4FABCEDF4387` FOREIGN KEY (`running_mate_request_id`) REFERENCES `application_request_running_mate` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `running_mate_request_referent_tag`
--

LOCK TABLES `running_mate_request_referent_tag` WRITE;
/*!40000 ALTER TABLE `running_mate_request_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `running_mate_request_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `running_mate_request_theme`
--

DROP TABLE IF EXISTS `running_mate_request_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `running_mate_request_theme` (
  `running_mate_request_id` int unsigned NOT NULL,
  `theme_id` int NOT NULL,
  PRIMARY KEY (`running_mate_request_id`,`theme_id`),
  KEY `IDX_A732622759027487` (`theme_id`),
  KEY `IDX_A7326227CEDF4387` (`running_mate_request_id`),
  CONSTRAINT `FK_A732622759027487` FOREIGN KEY (`theme_id`) REFERENCES `application_request_theme` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A7326227CEDF4387` FOREIGN KEY (`running_mate_request_id`) REFERENCES `application_request_running_mate` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `running_mate_request_theme`
--

LOCK TABLES `running_mate_request_theme` WRITE;
/*!40000 ALTER TABLE `running_mate_request_theme` DISABLE KEYS */;
/*!40000 ALTER TABLE `running_mate_request_theme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_board_members`
--

DROP TABLE IF EXISTS `saved_board_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saved_board_members` (
  `board_member_owner_id` int NOT NULL,
  `board_member_saved_id` int NOT NULL,
  PRIMARY KEY (`board_member_owner_id`,`board_member_saved_id`),
  KEY `IDX_32865A324821D202` (`board_member_saved_id`),
  KEY `IDX_32865A32FDCCD727` (`board_member_owner_id`),
  CONSTRAINT `FK_32865A324821D202` FOREIGN KEY (`board_member_saved_id`) REFERENCES `board_member` (`id`),
  CONSTRAINT `FK_32865A32FDCCD727` FOREIGN KEY (`board_member_owner_id`) REFERENCES `board_member` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_board_members`
--

LOCK TABLES `saved_board_members` WRITE;
/*!40000 ALTER TABLE `saved_board_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_board_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scope`
--

DROP TABLE IF EXISTS `scope`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scope` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `apps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AF55D377153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scope`
--

LOCK TABLES `scope` WRITE;
/*!40000 ALTER TABLE `scope` DISABLE KEYS */;
/*!40000 ALTER TABLE `scope` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `senator_area`
--

DROP TABLE IF EXISTS `senator_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `senator_area` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_tag_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D229BBF7AEC89CE1` (`department_tag_id`),
  CONSTRAINT `FK_D229BBF7AEC89CE1` FOREIGN KEY (`department_tag_id`) REFERENCES `referent_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `senator_area`
--

LOCK TABLES `senator_area` WRITE;
/*!40000 ALTER TABLE `senator_area` DISABLE KEYS */;
/*!40000 ALTER TABLE `senator_area` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `senatorial_candidate_areas`
--

DROP TABLE IF EXISTS `senatorial_candidate_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `senatorial_candidate_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `senatorial_candidate_areas`
--

LOCK TABLES `senatorial_candidate_areas` WRITE;
/*!40000 ALTER TABLE `senatorial_candidate_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `senatorial_candidate_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `senatorial_candidate_areas_tags`
--

DROP TABLE IF EXISTS `senatorial_candidate_areas_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `senatorial_candidate_areas_tags` (
  `senatorial_candidate_area_id` int NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`senatorial_candidate_area_id`,`referent_tag_id`),
  KEY `IDX_F83208FA9C262DB3` (`referent_tag_id`),
  KEY `IDX_F83208FAA7BF84E8` (`senatorial_candidate_area_id`),
  CONSTRAINT `FK_F83208FA9C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`),
  CONSTRAINT `FK_F83208FAA7BF84E8` FOREIGN KEY (`senatorial_candidate_area_id`) REFERENCES `senatorial_candidate_areas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `senatorial_candidate_areas_tags`
--

LOCK TABLES `senatorial_candidate_areas_tags` WRITE;
/*!40000 ALTER TABLE `senatorial_candidate_areas_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `senatorial_candidate_areas_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_campaign`
--

DROP TABLE IF EXISTS `sms_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_campaign` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `audience_id` int unsigned DEFAULT NULL,
  `administrator_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `recipient_count` int DEFAULT NULL,
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `adherent_count` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_79E333DCD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_79E333DC848CC616` (`audience_id`),
  KEY `IDX_79E333DC4B09E92C` (`administrator_id`),
  CONSTRAINT `FK_79E333DC4B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_79E333DC848CC616` FOREIGN KEY (`audience_id`) REFERENCES `audience_snapshot` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_campaign`
--

LOCK TABLES `sms_campaign` WRITE;
/*!40000 ALTER TABLE `sms_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_stop_history`
--

DROP TABLE IF EXISTS `sms_stop_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_stop_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_date` datetime DEFAULT NULL,
  `campaign_external_id` int DEFAULT NULL,
  `receiver` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E761AF89D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_stop_history`
--

LOCK TABLES `sms_stop_history` WRITE;
/*!40000 ALTER TABLE `sms_stop_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_stop_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_share_categories`
--

DROP TABLE IF EXISTS `social_share_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_share_categories` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_shares` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `social_share_category_id` bigint DEFAULT NULL,
  `media_id` bigint DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint NOT NULL DEFAULT '0',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebook_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `published` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8E1413A085040FAD` (`social_share_category_id`),
  KEY `IDX_8E1413A0EA9FDD75` (`media_id`),
  CONSTRAINT `FK_8E1413A085040FAD` FOREIGN KEY (`social_share_category_id`) REFERENCES `social_share_categories` (`id`),
  CONSTRAINT `FK_8E1413A0EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_shares`
--

LOCK TABLES `social_shares` WRITE;
/*!40000 ALTER TABLE `social_shares` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_type`
--

DROP TABLE IF EXISTS `subscription_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_type` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BBE2473777153098` (`code`),
  UNIQUE KEY `UNIQ_BBE247379F75D7B0` (`external_id`),
  KEY `IDX_BBE2473777153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_type`
--

LOCK TABLES `subscription_type` WRITE;
/*!40000 ALTER TABLE `subscription_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscription_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team`
--

DROP TABLE IF EXISTS `team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `visibility` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C4E0A61FD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_C4E0A61F5E237E069F2C3FAB` (`name`,`zone_id`),
  KEY `IDX_C4E0A61F85C9D733` (`created_by_adherent_id`),
  KEY `IDX_C4E0A61F9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_C4E0A61FCF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_C4E0A61FDF6CFDC9` (`updated_by_adherent_id`),
  KEY `IDX_C4E0A61F9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_C4E0A61F85C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C4E0A61F9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C4E0A61F9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_C4E0A61FCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C4E0A61FDF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team`
--

LOCK TABLES `team` WRITE;
/*!40000 ALTER TABLE `team` DISABLE KEYS */;
/*!40000 ALTER TABLE `team` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_member`
--

DROP TABLE IF EXISTS `team_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_member` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int unsigned NOT NULL,
  `adherent_id` int unsigned NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_member_unique` (`team_id`,`adherent_id`),
  UNIQUE KEY `UNIQ_6FFBDA1D17F50A6` (`uuid`),
  KEY `IDX_6FFBDA125F06C53` (`adherent_id`),
  KEY `IDX_6FFBDA1296CD8AE` (`team_id`),
  CONSTRAINT `FK_6FFBDA125F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6FFBDA1296CD8AE` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_member`
--

LOCK TABLES `team_member` WRITE;
/*!40000 ALTER TABLE `team_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_member_history`
--

DROP TABLE IF EXISTS `team_member_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_member_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int unsigned NOT NULL,
  `adherent_id` int unsigned NOT NULL,
  `administrator_id` int DEFAULT NULL,
  `team_manager_id` int unsigned DEFAULT NULL,
  `action` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_1F330628296CD8AE` (`team_id`),
  KEY `team_member_history_adherent_id_idx` (`adherent_id`),
  KEY `team_member_history_administrator_id_idx` (`administrator_id`),
  KEY `team_member_history_date_idx` (`date`),
  KEY `team_member_history_team_manager_id_idx` (`team_manager_id`),
  CONSTRAINT `FK_1F33062825F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1F330628296CD8AE` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1F33062846E746A6` FOREIGN KEY (`team_manager_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_1F3306284B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_member_history`
--

LOCK TABLES `team_member_history` WRITE;
/*!40000 ALTER TABLE `team_member_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_member_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council`
--

DROP TABLE IF EXISTS `territorial_council`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `current_designation_id` int unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codes` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `mailchimp_id` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B6DCA2A5E5ADC14D` (`codes`),
  UNIQUE KEY `UNIQ_B6DCA2A55E237E06` (`name`),
  UNIQUE KEY `UNIQ_B6DCA2A5D17F50A6` (`uuid`),
  KEY `IDX_B6DCA2A5B4D2A5D1` (`current_designation_id`),
  CONSTRAINT `FK_B6DCA2A5B4D2A5D1` FOREIGN KEY (`current_designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council`
--

LOCK TABLES `territorial_council` WRITE;
/*!40000 ALTER TABLE `territorial_council` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_candidacies_group`
--

DROP TABLE IF EXISTS `territorial_council_candidacies_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_candidacies_group`
--

LOCK TABLES `territorial_council_candidacies_group` WRITE;
/*!40000 ALTER TABLE `territorial_council_candidacies_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_candidacies_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_candidacy`
--

DROP TABLE IF EXISTS `territorial_council_candidacy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_candidacy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `membership_id` int unsigned NOT NULL,
  `candidacies_group_id` int unsigned DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `faith_statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_public_faith_statement` tinyint(1) NOT NULL DEFAULT '0',
  `quality` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_39885B6D17F50A6` (`uuid`),
  KEY `IDX_39885B61FB354CD` (`membership_id`),
  KEY `IDX_39885B6A708DAFF` (`election_id`),
  KEY `IDX_39885B6FC1537C1` (`candidacies_group_id`),
  CONSTRAINT `FK_39885B61FB354CD` FOREIGN KEY (`membership_id`) REFERENCES `territorial_council_membership` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_39885B6A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `territorial_council_election` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_39885B6FC1537C1` FOREIGN KEY (`candidacies_group_id`) REFERENCES `territorial_council_candidacies_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_candidacy`
--

LOCK TABLES `territorial_council_candidacy` WRITE;
/*!40000 ALTER TABLE `territorial_council_candidacy` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_candidacy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_candidacy_invitation`
--

DROP TABLE IF EXISTS `territorial_council_candidacy_invitation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_candidacy_invitation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `membership_id` int unsigned NOT NULL,
  `candidacy_id` int unsigned NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `declined_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DA86009AD17F50A6` (`uuid`),
  KEY `IDX_DA86009A1FB354CD` (`membership_id`),
  KEY `IDX_DA86009A59B22434` (`candidacy_id`),
  CONSTRAINT `FK_DA86009A1FB354CD` FOREIGN KEY (`membership_id`) REFERENCES `territorial_council_membership` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DA86009A59B22434` FOREIGN KEY (`candidacy_id`) REFERENCES `territorial_council_candidacy` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_candidacy_invitation`
--

LOCK TABLES `territorial_council_candidacy_invitation` WRITE;
/*!40000 ALTER TABLE `territorial_council_candidacy_invitation` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_candidacy_invitation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_convocation`
--

DROP TABLE IF EXISTS `territorial_council_convocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_convocation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `territorial_council_id` int unsigned DEFAULT NULL,
  `political_committee_id` int unsigned DEFAULT NULL,
  `created_by_id` int unsigned DEFAULT NULL,
  `meeting_start_date` datetime NOT NULL,
  `meeting_end_date` datetime NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meeting_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A9919BF0D17F50A6` (`uuid`),
  KEY `IDX_A9919BF0AAA61A99` (`territorial_council_id`),
  KEY `IDX_A9919BF0B03A8386` (`created_by_id`),
  KEY `IDX_A9919BF0C7A72` (`political_committee_id`),
  CONSTRAINT `FK_A9919BF0AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`),
  CONSTRAINT `FK_A9919BF0B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A9919BF0C7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_convocation`
--

LOCK TABLES `territorial_council_convocation` WRITE;
/*!40000 ALTER TABLE `territorial_council_convocation` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_convocation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_election`
--

DROP TABLE IF EXISTS `territorial_council_election`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_election` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `territorial_council_id` int unsigned DEFAULT NULL,
  `designation_id` int unsigned DEFAULT NULL,
  `election_poll_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `election_mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_start_date` datetime DEFAULT NULL,
  `meeting_end_date` datetime DEFAULT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `meeting_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_14CBC36BD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_14CBC36B8649F5F1` (`election_poll_id`),
  KEY `IDX_14CBC36BAAA61A99` (`territorial_council_id`),
  KEY `IDX_14CBC36BFAC7D83F` (`designation_id`),
  CONSTRAINT `FK_14CBC36B8649F5F1` FOREIGN KEY (`election_poll_id`) REFERENCES `territorial_council_election_poll` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_14CBC36BAAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`),
  CONSTRAINT `FK_14CBC36BFAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_election`
--

LOCK TABLES `territorial_council_election` WRITE;
/*!40000 ALTER TABLE `territorial_council_election` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_election` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_election_poll`
--

DROP TABLE IF EXISTS `territorial_council_election_poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_election_poll` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E0D7231ED17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_election_poll`
--

LOCK TABLES `territorial_council_election_poll` WRITE;
/*!40000 ALTER TABLE `territorial_council_election_poll` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_election_poll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_election_poll_choice`
--

DROP TABLE IF EXISTS `territorial_council_election_poll_choice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_election_poll_choice` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_poll_id` int unsigned NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_63EBCF6BD17F50A6` (`uuid`),
  KEY `IDX_63EBCF6B8649F5F1` (`election_poll_id`),
  CONSTRAINT `FK_63EBCF6B8649F5F1` FOREIGN KEY (`election_poll_id`) REFERENCES `territorial_council_election_poll` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_election_poll_choice`
--

LOCK TABLES `territorial_council_election_poll_choice` WRITE;
/*!40000 ALTER TABLE `territorial_council_election_poll_choice` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_election_poll_choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_election_poll_vote`
--

DROP TABLE IF EXISTS `territorial_council_election_poll_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_election_poll_vote` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `choice_id` int unsigned DEFAULT NULL,
  `membership_id` int unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BCDA0C151FB354CD` (`membership_id`),
  KEY `IDX_BCDA0C15998666D1` (`choice_id`),
  CONSTRAINT `FK_BCDA0C151FB354CD` FOREIGN KEY (`membership_id`) REFERENCES `territorial_council_membership` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BCDA0C15998666D1` FOREIGN KEY (`choice_id`) REFERENCES `territorial_council_election_poll_choice` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_election_poll_vote`
--

LOCK TABLES `territorial_council_election_poll_vote` WRITE;
/*!40000 ALTER TABLE `territorial_council_election_poll_vote` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_election_poll_vote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_feed_item`
--

DROP TABLE IF EXISTS `territorial_council_feed_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_feed_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `territorial_council_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_45241D62D17F50A6` (`uuid`),
  KEY `IDX_45241D62AAA61A99` (`territorial_council_id`),
  KEY `IDX_45241D62F675F31B` (`author_id`),
  CONSTRAINT `FK_45241D62AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_45241D62F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_feed_item`
--

LOCK TABLES `territorial_council_feed_item` WRITE;
/*!40000 ALTER TABLE `territorial_council_feed_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_feed_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_membership`
--

DROP TABLE IF EXISTS `territorial_council_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_membership` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `territorial_council_id` int unsigned NOT NULL,
  `joined_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2A99831625F06C53` (`adherent_id`),
  UNIQUE KEY `UNIQ_2A998316D17F50A6` (`uuid`),
  KEY `IDX_2A998316AAA61A99` (`territorial_council_id`),
  CONSTRAINT `FK_2A99831625F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2A998316AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_membership`
--

LOCK TABLES `territorial_council_membership` WRITE;
/*!40000 ALTER TABLE `territorial_council_membership` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_membership_log`
--

DROP TABLE IF EXISTS `territorial_council_membership_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_membership_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quality_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `actual_territorial_council` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actual_quality_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `found_territorial_councils` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `created_at` datetime NOT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_2F6D242025F06C53` (`adherent_id`),
  CONSTRAINT `FK_2F6D242025F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_membership_log`
--

LOCK TABLES `territorial_council_membership_log` WRITE;
/*!40000 ALTER TABLE `territorial_council_membership_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_membership_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_official_report`
--

DROP TABLE IF EXISTS `territorial_council_official_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_official_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `political_committee_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `created_by_id` int unsigned DEFAULT NULL,
  `updated_by_id` int unsigned DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D80D385D17F50A6` (`uuid`),
  KEY `IDX_8D80D385896DBBDE` (`updated_by_id`),
  KEY `IDX_8D80D385B03A8386` (`created_by_id`),
  KEY `IDX_8D80D385C7A72` (`political_committee_id`),
  KEY `IDX_8D80D385F675F31B` (`author_id`),
  CONSTRAINT `FK_8D80D385896DBBDE` FOREIGN KEY (`updated_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8D80D385B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8D80D385C7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_8D80D385F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_official_report`
--

LOCK TABLES `territorial_council_official_report` WRITE;
/*!40000 ALTER TABLE `territorial_council_official_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_official_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_official_report_document`
--

DROP TABLE IF EXISTS `territorial_council_official_report_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_official_report_document` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_id` int unsigned DEFAULT NULL,
  `report_id` int unsigned DEFAULT NULL,
  `filename` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` smallint unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_78C1161D4BD2A4C0` (`report_id`),
  KEY `IDX_78C1161DB03A8386` (`created_by_id`),
  CONSTRAINT `FK_78C1161D4BD2A4C0` FOREIGN KEY (`report_id`) REFERENCES `territorial_council_official_report` (`id`),
  CONSTRAINT `FK_78C1161DB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_official_report_document`
--

LOCK TABLES `territorial_council_official_report_document` WRITE;
/*!40000 ALTER TABLE `territorial_council_official_report_document` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_official_report_document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_quality`
--

DROP TABLE IF EXISTS `territorial_council_quality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_quality` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `territorial_council_membership_id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `joined_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C018E022E797FAB0` (`territorial_council_membership_id`),
  CONSTRAINT `FK_C018E022E797FAB0` FOREIGN KEY (`territorial_council_membership_id`) REFERENCES `territorial_council_membership` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_quality`
--

LOCK TABLES `territorial_council_quality` WRITE;
/*!40000 ALTER TABLE `territorial_council_quality` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_quality` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_referent_tag`
--

DROP TABLE IF EXISTS `territorial_council_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_referent_tag` (
  `territorial_council_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`territorial_council_id`,`referent_tag_id`),
  KEY `IDX_78DBEB909C262DB3` (`referent_tag_id`),
  KEY `IDX_78DBEB90AAA61A99` (`territorial_council_id`),
  CONSTRAINT `FK_78DBEB909C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_78DBEB90AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_referent_tag`
--

LOCK TABLES `territorial_council_referent_tag` WRITE;
/*!40000 ALTER TABLE `territorial_council_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `territorial_council_zone`
--

DROP TABLE IF EXISTS `territorial_council_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_zone` (
  `territorial_council_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`territorial_council_id`,`zone_id`),
  KEY `IDX_9467B41E9F2C3FAB` (`zone_id`),
  KEY `IDX_9467B41EAAA61A99` (`territorial_council_id`),
  CONSTRAINT `FK_9467B41E9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9467B41EAAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `territorial_council_zone`
--

LOCK TABLES `territorial_council_zone` WRITE;
/*!40000 ALTER TABLE `territorial_council_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `territorial_council_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thematic_community`
--

DROP TABLE IF EXISTS `thematic_community`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thematic_community` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `canonical_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6F22A458D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thematic_community`
--

LOCK TABLES `thematic_community` WRITE;
/*!40000 ALTER TABLE `thematic_community` DISABLE KEYS */;
/*!40000 ALTER TABLE `thematic_community` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thematic_community_contact`
--

DROP TABLE IF EXISTS `thematic_community_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thematic_community_contact` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date DEFAULT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `activity_area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5C0B5CEAD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thematic_community_contact`
--

LOCK TABLES `thematic_community_contact` WRITE;
/*!40000 ALTER TABLE `thematic_community_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `thematic_community_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thematic_community_membership`
--

DROP TABLE IF EXISTS `thematic_community_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thematic_community_membership` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `community_id` int unsigned DEFAULT NULL,
  `contact_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `joined_at` datetime NOT NULL,
  `association` tinyint(1) NOT NULL DEFAULT '0',
  `association_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expert` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `has_job` tinyint(1) NOT NULL DEFAULT '0',
  `job` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_22B6AC05D17F50A6` (`uuid`),
  KEY `IDX_22B6AC0525F06C53` (`adherent_id`),
  KEY `IDX_22B6AC05E7A1254A` (`contact_id`),
  KEY `IDX_22B6AC05FDA7B0BF` (`community_id`),
  CONSTRAINT `FK_22B6AC0525F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_22B6AC05E7A1254A` FOREIGN KEY (`contact_id`) REFERENCES `thematic_community_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_22B6AC05FDA7B0BF` FOREIGN KEY (`community_id`) REFERENCES `thematic_community` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thematic_community_membership`
--

LOCK TABLES `thematic_community_membership` WRITE;
/*!40000 ALTER TABLE `thematic_community_membership` DISABLE KEYS */;
/*!40000 ALTER TABLE `thematic_community_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thematic_community_membership_user_list_definition`
--

DROP TABLE IF EXISTS `thematic_community_membership_user_list_definition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thematic_community_membership_user_list_definition` (
  `thematic_community_membership_id` int unsigned NOT NULL,
  `user_list_definition_id` int unsigned NOT NULL,
  PRIMARY KEY (`thematic_community_membership_id`,`user_list_definition_id`),
  KEY `IDX_58815EB9403AE2A5` (`thematic_community_membership_id`),
  KEY `IDX_58815EB9F74563E3` (`user_list_definition_id`),
  CONSTRAINT `FK_58815EB9403AE2A5` FOREIGN KEY (`thematic_community_membership_id`) REFERENCES `thematic_community_membership` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_58815EB9F74563E3` FOREIGN KEY (`user_list_definition_id`) REFERENCES `user_list_definition` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thematic_community_membership_user_list_definition`
--

LOCK TABLES `thematic_community_membership_user_list_definition` WRITE;
/*!40000 ALTER TABLE `thematic_community_membership_user_list_definition` DISABLE KEYS */;
/*!40000 ALTER TABLE `thematic_community_membership_user_list_definition` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_manifesto_translations`
--

DROP TABLE IF EXISTS `timeline_manifesto_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_manifesto_translations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `translatable_id` bigint DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `timeline_manifesto_translations_unique_translation` (`translatable_id`,`locale`),
  KEY `IDX_F7BD6C172C2AC5D3` (`translatable_id`),
  CONSTRAINT `FK_F7BD6C172C2AC5D3` FOREIGN KEY (`translatable_id`) REFERENCES `timeline_manifestos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_manifesto_translations`
--

LOCK TABLES `timeline_manifesto_translations` WRITE;
/*!40000 ALTER TABLE `timeline_manifesto_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_manifesto_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_manifestos`
--

DROP TABLE IF EXISTS `timeline_manifestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_manifestos` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `display_media` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C6ED4403EA9FDD75` (`media_id`),
  CONSTRAINT `FK_C6ED4403EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_manifestos`
--

LOCK TABLES `timeline_manifestos` WRITE;
/*!40000 ALTER TABLE `timeline_manifestos` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_manifestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_measure_translations`
--

DROP TABLE IF EXISTS `timeline_measure_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_measure_translations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `translatable_id` bigint DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `timeline_measure_translations_unique_translation` (`translatable_id`,`locale`),
  KEY `IDX_5C9EB6072C2AC5D3` (`translatable_id`),
  CONSTRAINT `FK_5C9EB6072C2AC5D3` FOREIGN KEY (`translatable_id`) REFERENCES `timeline_measures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_measure_translations`
--

LOCK TABLES `timeline_measure_translations` WRITE;
/*!40000 ALTER TABLE `timeline_measure_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_measure_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_measures`
--

DROP TABLE IF EXISTS `timeline_measures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_measures` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `manifesto_id` bigint NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `major` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_BA475ED737E924` (`manifesto_id`),
  CONSTRAINT `FK_BA475ED737E924` FOREIGN KEY (`manifesto_id`) REFERENCES `timeline_manifestos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_measures_profiles` (
  `measure_id` bigint NOT NULL,
  `profile_id` bigint NOT NULL,
  PRIMARY KEY (`measure_id`,`profile_id`),
  KEY `IDX_B83D81AE5DA37D00` (`measure_id`),
  KEY `IDX_B83D81AECCFA12B8` (`profile_id`),
  CONSTRAINT `FK_B83D81AE5DA37D00` FOREIGN KEY (`measure_id`) REFERENCES `timeline_measures` (`id`),
  CONSTRAINT `FK_B83D81AECCFA12B8` FOREIGN KEY (`profile_id`) REFERENCES `timeline_profiles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_measures_profiles`
--

LOCK TABLES `timeline_measures_profiles` WRITE;
/*!40000 ALTER TABLE `timeline_measures_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_measures_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_profile_translations`
--

DROP TABLE IF EXISTS `timeline_profile_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_profile_translations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `translatable_id` bigint DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `timeline_profile_translations_unique_translation` (`translatable_id`,`locale`),
  KEY `IDX_41B3A6DA2C2AC5D3` (`translatable_id`),
  CONSTRAINT `FK_41B3A6DA2C2AC5D3` FOREIGN KEY (`translatable_id`) REFERENCES `timeline_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_profile_translations`
--

LOCK TABLES `timeline_profile_translations` WRITE;
/*!40000 ALTER TABLE `timeline_profile_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_profile_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_profiles`
--

DROP TABLE IF EXISTS `timeline_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_profiles` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_profiles`
--

LOCK TABLES `timeline_profiles` WRITE;
/*!40000 ALTER TABLE `timeline_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_theme_translations`
--

DROP TABLE IF EXISTS `timeline_theme_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_theme_translations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `translatable_id` bigint DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `timeline_theme_translations_unique_translation` (`translatable_id`,`locale`),
  KEY `IDX_F81F72932C2AC5D3` (`translatable_id`),
  CONSTRAINT `FK_F81F72932C2AC5D3` FOREIGN KEY (`translatable_id`) REFERENCES `timeline_themes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_theme_translations`
--

LOCK TABLES `timeline_theme_translations` WRITE;
/*!40000 ALTER TABLE `timeline_theme_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_theme_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_themes`
--

DROP TABLE IF EXISTS `timeline_themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_themes` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `display_media` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8ADDB8F6EA9FDD75` (`media_id`),
  CONSTRAINT `FK_8ADDB8F6EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_themes_measures` (
  `theme_id` bigint NOT NULL,
  `measure_id` bigint NOT NULL,
  PRIMARY KEY (`measure_id`,`theme_id`),
  KEY `IDX_EB8A7B0C59027487` (`theme_id`),
  KEY `IDX_EB8A7B0C5DA37D00` (`measure_id`),
  CONSTRAINT `FK_EB8A7B0C59027487` FOREIGN KEY (`theme_id`) REFERENCES `timeline_themes` (`id`),
  CONSTRAINT `FK_EB8A7B0C5DA37D00` FOREIGN KEY (`measure_id`) REFERENCES `timeline_measures` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ton_macron_choices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `step` smallint unsigned NOT NULL,
  `content_key` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6247B0DE3F7BFD5C` (`content_key`),
  UNIQUE KEY `UNIQ_6247B0DED17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ton_macron_friend_invitation_has_choices` (
  `invitation_id` int unsigned NOT NULL,
  `choice_id` int unsigned NOT NULL,
  PRIMARY KEY (`invitation_id`,`choice_id`),
  KEY `IDX_BB3BCAEE998666D1` (`choice_id`),
  KEY `IDX_BB3BCAEEA35D7AF0` (`invitation_id`),
  CONSTRAINT `FK_BB3BCAEE998666D1` FOREIGN KEY (`choice_id`) REFERENCES `ton_macron_choices` (`id`),
  CONSTRAINT `FK_BB3BCAEEA35D7AF0` FOREIGN KEY (`invitation_id`) REFERENCES `ton_macron_friend_invitations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ton_macron_friend_invitations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `friend_first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `friend_age` smallint unsigned NOT NULL,
  `friend_gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `friend_position` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `friend_email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_subject` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_78714946D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ton_macron_friend_invitations`
--

LOCK TABLES `ton_macron_friend_invitations` WRITE;
/*!40000 ALTER TABLE `ton_macron_friend_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ton_macron_friend_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unregistration_referent_tag`
--

DROP TABLE IF EXISTS `unregistration_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unregistration_referent_tag` (
  `unregistration_id` int NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`unregistration_id`,`referent_tag_id`),
  KEY `IDX_59B7AC414D824CA` (`unregistration_id`),
  KEY `IDX_59B7AC49C262DB3` (`referent_tag_id`),
  CONSTRAINT `FK_59B7AC414D824CA` FOREIGN KEY (`unregistration_id`) REFERENCES `unregistrations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_59B7AC49C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unregistration_referent_tag`
--

LOCK TABLES `unregistration_referent_tag` WRITE;
/*!40000 ALTER TABLE `unregistration_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `unregistration_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unregistrations`
--

DROP TABLE IF EXISTS `unregistrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unregistrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `excluded_by_id` int DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reasons` json DEFAULT NULL COMMENT '(DC2Type:json)',
  `comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `registered_at` datetime NOT NULL,
  `unregistered_at` datetime NOT NULL,
  `is_adherent` tinyint(1) NOT NULL DEFAULT '0',
  `adherent_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `is_renaissance` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_F9E4AA0C5B30B80B` (`excluded_by_id`),
  CONSTRAINT `FK_F9E4AA0C5B30B80B` FOREIGN KEY (`excluded_by_id`) REFERENCES `administrators` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unregistrations`
--

LOCK TABLES `unregistrations` WRITE;
/*!40000 ALTER TABLE `unregistrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `unregistrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_authorizations`
--

DROP TABLE IF EXISTS `user_authorizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_authorizations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `scopes` json NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_40448230D17F50A6` (`uuid`),
  UNIQUE KEY `user_authorizations_unique` (`user_id`,`client_id`),
  KEY `IDX_4044823019EB6921` (`client_id`),
  KEY `IDX_40448230A76ED395` (`user_id`),
  CONSTRAINT `FK_4044823019EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `FK_40448230A76ED395` FOREIGN KEY (`user_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_authorizations`
--

LOCK TABLES `user_authorizations` WRITE;
/*!40000 ALTER TABLE `user_authorizations` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_authorizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_documents`
--

DROP TABLE IF EXISTS `user_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `original_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A250FF6CD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_documents`
--

LOCK TABLES `user_documents` WRITE;
/*!40000 ALTER TABLE `user_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_list_definition`
--

DROP TABLE IF EXISTS `user_list_definition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_list_definition` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_list_definition_type_code_unique` (`type`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_list_definition`
--

LOCK TABLES `user_list_definition` WRITE;
/*!40000 ALTER TABLE `user_list_definition` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_list_definition` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `volunteer_request_application_request_tag`
--

DROP TABLE IF EXISTS `volunteer_request_application_request_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `volunteer_request_application_request_tag` (
  `volunteer_request_id` int unsigned NOT NULL,
  `application_request_tag_id` int NOT NULL,
  PRIMARY KEY (`volunteer_request_id`,`application_request_tag_id`),
  KEY `IDX_6F3FA2699644FEDA` (`application_request_tag_id`),
  KEY `IDX_6F3FA269B8D6887` (`volunteer_request_id`),
  CONSTRAINT `FK_6F3FA2699644FEDA` FOREIGN KEY (`application_request_tag_id`) REFERENCES `application_request_tag` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6F3FA269B8D6887` FOREIGN KEY (`volunteer_request_id`) REFERENCES `application_request_volunteer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `volunteer_request_application_request_tag`
--

LOCK TABLES `volunteer_request_application_request_tag` WRITE;
/*!40000 ALTER TABLE `volunteer_request_application_request_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `volunteer_request_application_request_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `volunteer_request_referent_tag`
--

DROP TABLE IF EXISTS `volunteer_request_referent_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `volunteer_request_referent_tag` (
  `volunteer_request_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`volunteer_request_id`,`referent_tag_id`),
  KEY `IDX_DA2917429C262DB3` (`referent_tag_id`),
  KEY `IDX_DA291742B8D6887` (`volunteer_request_id`),
  CONSTRAINT `FK_DA2917429C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DA291742B8D6887` FOREIGN KEY (`volunteer_request_id`) REFERENCES `application_request_volunteer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `volunteer_request_referent_tag`
--

LOCK TABLES `volunteer_request_referent_tag` WRITE;
/*!40000 ALTER TABLE `volunteer_request_referent_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `volunteer_request_referent_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `volunteer_request_technical_skill`
--

DROP TABLE IF EXISTS `volunteer_request_technical_skill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `volunteer_request_technical_skill` (
  `volunteer_request_id` int unsigned NOT NULL,
  `technical_skill_id` int NOT NULL,
  PRIMARY KEY (`volunteer_request_id`,`technical_skill_id`),
  KEY `IDX_7F8C5C1EB8D6887` (`volunteer_request_id`),
  KEY `IDX_7F8C5C1EE98F0EFD` (`technical_skill_id`),
  CONSTRAINT `FK_7F8C5C1EB8D6887` FOREIGN KEY (`volunteer_request_id`) REFERENCES `application_request_volunteer` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7F8C5C1EE98F0EFD` FOREIGN KEY (`technical_skill_id`) REFERENCES `application_request_technical_skill` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `volunteer_request_technical_skill`
--

LOCK TABLES `volunteer_request_technical_skill` WRITE;
/*!40000 ALTER TABLE `volunteer_request_technical_skill` DISABLE KEYS */;
/*!40000 ALTER TABLE `volunteer_request_technical_skill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `volunteer_request_theme`
--

DROP TABLE IF EXISTS `volunteer_request_theme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `volunteer_request_theme` (
  `volunteer_request_id` int unsigned NOT NULL,
  `theme_id` int NOT NULL,
  PRIMARY KEY (`volunteer_request_id`,`theme_id`),
  KEY `IDX_5427AF5359027487` (`theme_id`),
  KEY `IDX_5427AF53B8D6887` (`volunteer_request_id`),
  CONSTRAINT `FK_5427AF5359027487` FOREIGN KEY (`theme_id`) REFERENCES `application_request_theme` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5427AF53B8D6887` FOREIGN KEY (`volunteer_request_id`) REFERENCES `application_request_volunteer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `volunteer_request_theme`
--

LOCK TABLES `volunteer_request_theme` WRITE;
/*!40000 ALTER TABLE `volunteer_request_theme` DISABLE KEYS */;
/*!40000 ALTER TABLE `volunteer_request_theme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote_result`
--

DROP TABLE IF EXISTS `vote_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vote_result` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vote_place_id` int unsigned DEFAULT NULL,
  `election_round_id` int NOT NULL,
  `created_by_id` int unsigned DEFAULT NULL,
  `updated_by_id` int unsigned DEFAULT NULL,
  `city_id` int unsigned DEFAULT NULL,
  `registered` int NOT NULL,
  `abstentions` int NOT NULL,
  `participated` int NOT NULL,
  `expressed` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `city_vote_result_city_round_unique` (`city_id`,`election_round_id`),
  UNIQUE KEY `vote_place_result_city_round_unique` (`vote_place_id`,`election_round_id`),
  KEY `IDX_1F8DB349896DBBDE` (`updated_by_id`),
  KEY `IDX_1F8DB3498BAC62AF` (`city_id`),
  KEY `IDX_1F8DB349B03A8386` (`created_by_id`),
  KEY `IDX_1F8DB349F3F90B30` (`vote_place_id`),
  KEY `IDX_1F8DB349FCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_1F8DB349896DBBDE` FOREIGN KEY (`updated_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_1F8DB3498BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1F8DB349B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_1F8DB349F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `election_vote_place` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1F8DB349FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `election_rounds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote_result`
--

LOCK TABLES `vote_result` WRITE;
/*!40000 ALTER TABLE `vote_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote_result_list`
--

DROP TABLE IF EXISTS `vote_result_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vote_result_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `list_collection_id` int DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nuance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adherent_count` int DEFAULT NULL,
  `eligible_count` int DEFAULT NULL,
  `position` int DEFAULT NULL,
  `candidate_first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `candidate_last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outgoing_mayor` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_677ED502DB567AF4` (`list_collection_id`),
  CONSTRAINT `FK_677ED502DB567AF4` FOREIGN KEY (`list_collection_id`) REFERENCES `vote_result_list_collection` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote_result_list`
--

LOCK TABLES `vote_result_list` WRITE;
/*!40000 ALTER TABLE `vote_result_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote_result_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote_result_list_collection`
--

DROP TABLE IF EXISTS `vote_result_list_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vote_result_list_collection` (
  `id` int NOT NULL AUTO_INCREMENT,
  `city_id` int unsigned DEFAULT NULL,
  `election_round_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9C1DD9638BAC62AF` (`city_id`),
  KEY `IDX_9C1DD963FCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_9C1DD9638BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  CONSTRAINT `FK_9C1DD963FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `election_rounds` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote_result_list_collection`
--

LOCK TABLES `vote_result_list_collection` WRITE;
/*!40000 ALTER TABLE `vote_result_list_collection` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote_result_list_collection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_candidate`
--

DROP TABLE IF EXISTS `voting_platform_candidate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_candidate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `candidate_group_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `faith_statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `additionally_elected` tinyint(1) NOT NULL DEFAULT '0',
  `position` smallint unsigned DEFAULT NULL,
  `is_substitute` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3F426D6DD17F50A6` (`uuid`),
  KEY `IDX_3F426D6D25F06C53` (`adherent_id`),
  KEY `IDX_3F426D6D5F0A9B94` (`candidate_group_id`),
  CONSTRAINT `FK_3F426D6D25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_3F426D6D5F0A9B94` FOREIGN KEY (`candidate_group_id`) REFERENCES `voting_platform_candidate_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_candidate`
--

LOCK TABLES `voting_platform_candidate` WRITE;
/*!40000 ALTER TABLE `voting_platform_candidate` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_candidate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_candidate_group`
--

DROP TABLE IF EXISTS `voting_platform_candidate_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_candidate_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_pool_id` int DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `elected` tinyint(1) NOT NULL DEFAULT '0',
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2C1A353AD17F50A6` (`uuid`),
  KEY `IDX_2C1A353AC1E98F21` (`election_pool_id`),
  CONSTRAINT `FK_2C1A353AC1E98F21` FOREIGN KEY (`election_pool_id`) REFERENCES `voting_platform_election_pool` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_candidate_group`
--

LOCK TABLES `voting_platform_candidate_group` WRITE;
/*!40000 ALTER TABLE `voting_platform_candidate_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_candidate_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_candidate_group_result`
--

DROP TABLE IF EXISTS `voting_platform_candidate_group_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_candidate_group_result` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `candidate_group_id` int unsigned DEFAULT NULL,
  `election_pool_result_id` int unsigned DEFAULT NULL,
  `total` int unsigned NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `total_mentions` json DEFAULT NULL,
  `majority_mention` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7249D537D17F50A6` (`uuid`),
  KEY `IDX_7249D5375F0A9B94` (`candidate_group_id`),
  KEY `IDX_7249D537B5BA5CC5` (`election_pool_result_id`),
  CONSTRAINT `FK_7249D5375F0A9B94` FOREIGN KEY (`candidate_group_id`) REFERENCES `voting_platform_candidate_group` (`id`),
  CONSTRAINT `FK_7249D537B5BA5CC5` FOREIGN KEY (`election_pool_result_id`) REFERENCES `voting_platform_election_pool_result` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_candidate_group_result`
--

LOCK TABLES `voting_platform_candidate_group_result` WRITE;
/*!40000 ALTER TABLE `voting_platform_candidate_group_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_candidate_group_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_election`
--

DROP TABLE IF EXISTS `voting_platform_election`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `designation_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `closed_at` datetime DEFAULT NULL,
  `second_round_end_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `additional_places` smallint unsigned DEFAULT NULL,
  `additional_places_gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notifications_sent` smallint NOT NULL DEFAULT '0',
  `canceled_at` datetime DEFAULT NULL,
  `cancel_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4E144C94D17F50A6` (`uuid`),
  KEY `IDX_4E144C94FAC7D83F` (`designation_id`),
  CONSTRAINT `FK_4E144C94FAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_election`
--

LOCK TABLES `voting_platform_election` WRITE;
/*!40000 ALTER TABLE `voting_platform_election` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_election` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_election_entity`
--

DROP TABLE IF EXISTS `voting_platform_election_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_entity` (
  `id` int NOT NULL AUTO_INCREMENT,
  `committee_id` int unsigned DEFAULT NULL,
  `election_id` int unsigned DEFAULT NULL,
  `territorial_council_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7AAD259FA708DAFF` (`election_id`),
  KEY `IDX_7AAD259FAAA61A99` (`territorial_council_id`),
  KEY `IDX_7AAD259FED1A100B` (`committee_id`),
  CONSTRAINT `FK_7AAD259FA708DAFF` FOREIGN KEY (`election_id`) REFERENCES `voting_platform_election` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7AAD259FAAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_7AAD259FED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_election_entity`
--

LOCK TABLES `voting_platform_election_entity` WRITE;
/*!40000 ALTER TABLE `voting_platform_election_entity` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_election_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_election_pool`
--

DROP TABLE IF EXISTS `voting_platform_election_pool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_pool` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned DEFAULT NULL,
  `code` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7225D6EFA708DAFF` (`election_id`),
  CONSTRAINT `FK_7225D6EFA708DAFF` FOREIGN KEY (`election_id`) REFERENCES `voting_platform_election` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_election_pool`
--

LOCK TABLES `voting_platform_election_pool` WRITE;
/*!40000 ALTER TABLE `voting_platform_election_pool` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_election_pool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_election_pool_result`
--

DROP TABLE IF EXISTS `voting_platform_election_pool_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_pool_result` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_pool_id` int DEFAULT NULL,
  `election_round_result_id` int unsigned DEFAULT NULL,
  `is_elected` tinyint(1) NOT NULL DEFAULT '0',
  `expressed` int unsigned NOT NULL DEFAULT '0',
  `blank` int unsigned NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_13C1C73FD17F50A6` (`uuid`),
  KEY `IDX_13C1C73F8FFC0F0B` (`election_round_result_id`),
  KEY `IDX_13C1C73FC1E98F21` (`election_pool_id`),
  CONSTRAINT `FK_13C1C73F8FFC0F0B` FOREIGN KEY (`election_round_result_id`) REFERENCES `voting_platform_election_round_result` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_13C1C73FC1E98F21` FOREIGN KEY (`election_pool_id`) REFERENCES `voting_platform_election_pool` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_election_pool_result`
--

LOCK TABLES `voting_platform_election_pool_result` WRITE;
/*!40000 ALTER TABLE `voting_platform_election_pool_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_election_pool_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_election_result`
--

DROP TABLE IF EXISTS `voting_platform_election_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_result` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned DEFAULT NULL,
  `participated` int unsigned NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_67EFA0E4D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_67EFA0E4A708DAFF` (`election_id`),
  CONSTRAINT `FK_67EFA0E4A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `voting_platform_election` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_election_result`
--

LOCK TABLES `voting_platform_election_result` WRITE;
/*!40000 ALTER TABLE `voting_platform_election_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_election_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_election_round`
--

DROP TABLE IF EXISTS `voting_platform_election_round`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_round` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F15D87B7D17F50A6` (`uuid`),
  KEY `IDX_F15D87B7A708DAFF` (`election_id`),
  CONSTRAINT `FK_F15D87B7A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `voting_platform_election` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_election_round`
--

LOCK TABLES `voting_platform_election_round` WRITE;
/*!40000 ALTER TABLE `voting_platform_election_round` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_election_round` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_election_round_election_pool`
--

DROP TABLE IF EXISTS `voting_platform_election_round_election_pool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_round_election_pool` (
  `election_round_id` int NOT NULL,
  `election_pool_id` int NOT NULL,
  PRIMARY KEY (`election_round_id`,`election_pool_id`),
  KEY `IDX_E6665F19C1E98F21` (`election_pool_id`),
  KEY `IDX_E6665F19FCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_E6665F19C1E98F21` FOREIGN KEY (`election_pool_id`) REFERENCES `voting_platform_election_pool` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E6665F19FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `voting_platform_election_round` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_election_round_election_pool`
--

LOCK TABLES `voting_platform_election_round_election_pool` WRITE;
/*!40000 ALTER TABLE `voting_platform_election_round_election_pool` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_election_round_election_pool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_election_round_result`
--

DROP TABLE IF EXISTS `voting_platform_election_round_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_round_result` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_round_id` int DEFAULT NULL,
  `election_result_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F2670966D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_F2670966FCBF5E32` (`election_round_id`),
  KEY `IDX_F267096619FCFB29` (`election_result_id`),
  CONSTRAINT `FK_F267096619FCFB29` FOREIGN KEY (`election_result_id`) REFERENCES `voting_platform_election_result` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F2670966FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `voting_platform_election_round` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_election_round_result`
--

LOCK TABLES `voting_platform_election_round_result` WRITE;
/*!40000 ALTER TABLE `voting_platform_election_round_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_election_round_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_vote`
--

DROP TABLE IF EXISTS `voting_platform_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_vote` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voter_id` int DEFAULT NULL,
  `election_round_id` int DEFAULT NULL,
  `voted_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`voter_id`,`election_round_id`),
  KEY `IDX_DCBB2B7BEBB4B8AD` (`voter_id`),
  KEY `IDX_DCBB2B7BFCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_DCBB2B7BEBB4B8AD` FOREIGN KEY (`voter_id`) REFERENCES `voting_platform_voter` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DCBB2B7BFCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `voting_platform_election_round` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_vote`
--

LOCK TABLES `voting_platform_vote` WRITE;
/*!40000 ALTER TABLE `voting_platform_vote` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_vote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_vote_choice`
--

DROP TABLE IF EXISTS `voting_platform_vote_choice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_vote_choice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vote_result_id` int DEFAULT NULL,
  `candidate_group_id` int unsigned DEFAULT NULL,
  `election_pool_id` int DEFAULT NULL,
  `is_blank` tinyint(1) NOT NULL DEFAULT '0',
  `mention` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B009F31145EB7186` (`vote_result_id`),
  KEY `IDX_B009F3115F0A9B94` (`candidate_group_id`),
  KEY `IDX_B009F311C1E98F21` (`election_pool_id`),
  CONSTRAINT `FK_B009F31145EB7186` FOREIGN KEY (`vote_result_id`) REFERENCES `voting_platform_vote_result` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B009F3115F0A9B94` FOREIGN KEY (`candidate_group_id`) REFERENCES `voting_platform_candidate_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B009F311C1E98F21` FOREIGN KEY (`election_pool_id`) REFERENCES `voting_platform_election_pool` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_vote_choice`
--

LOCK TABLES `voting_platform_vote_choice` WRITE;
/*!40000 ALTER TABLE `voting_platform_vote_choice` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_vote_choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_vote_result`
--

DROP TABLE IF EXISTS `voting_platform_vote_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_vote_result` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_round_id` int DEFAULT NULL,
  `voter_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `voted_at` datetime NOT NULL,
  `zone_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`voter_key`,`election_round_id`),
  KEY `IDX_62C86890FCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_62C86890FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `voting_platform_election_round` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_vote_result`
--

LOCK TABLES `voting_platform_vote_result` WRITE;
/*!40000 ALTER TABLE `voting_platform_vote_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_vote_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_voter`
--

DROP TABLE IF EXISTS `voting_platform_voter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_voter` (
  `id` int NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `is_ghost` tinyint(1) NOT NULL DEFAULT '0',
  `is_poll_voter` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AB02EC0225F06C53` (`adherent_id`),
  CONSTRAINT `FK_AB02EC0225F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_voter`
--

LOCK TABLES `voting_platform_voter` WRITE;
/*!40000 ALTER TABLE `voting_platform_voter` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_voter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_voters_list`
--

DROP TABLE IF EXISTS `voting_platform_voters_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_voters_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3C73500DA708DAFF` (`election_id`),
  CONSTRAINT `FK_3C73500DA708DAFF` FOREIGN KEY (`election_id`) REFERENCES `voting_platform_election` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_voters_list`
--

LOCK TABLES `voting_platform_voters_list` WRITE;
/*!40000 ALTER TABLE `voting_platform_voters_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_voters_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_platform_voters_list_voter`
--

DROP TABLE IF EXISTS `voting_platform_voters_list_voter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_voters_list_voter` (
  `voters_list_id` int NOT NULL,
  `voter_id` int NOT NULL,
  PRIMARY KEY (`voters_list_id`,`voter_id`),
  KEY `IDX_7CC26956EBB4B8AD` (`voter_id`),
  KEY `IDX_7CC26956FB0C8C84` (`voters_list_id`),
  CONSTRAINT `FK_7CC26956EBB4B8AD` FOREIGN KEY (`voter_id`) REFERENCES `voting_platform_voter` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7CC26956FB0C8C84` FOREIGN KEY (`voters_list_id`) REFERENCES `voting_platform_voters_list` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_platform_voters_list_voter`
--

LOCK TABLES `voting_platform_voters_list_voter` WRITE;
/*!40000 ALTER TABLE `voting_platform_voters_list_voter` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_platform_voters_list_voter` ENABLE KEYS */;
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
