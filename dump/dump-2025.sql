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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_activation_code` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `expired_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
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
-- Table structure for table `adherent_adherent_static_label`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_adherent_static_label` (
  `adherent_id` int unsigned NOT NULL,
  `adherent_static_label_id` int unsigned NOT NULL,
  PRIMARY KEY (`adherent_id`,`adherent_static_label_id`),
  KEY `IDX_64905F4225F06C53` (`adherent_id`),
  KEY `IDX_64905F42ED149D10` (`adherent_static_label_id`),
  CONSTRAINT `FK_64905F4225F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64905F42ED149D10` FOREIGN KEY (`adherent_static_label_id`) REFERENCES `adherent_static_label` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_adherent_static_label`
--

LOCK TABLES `adherent_adherent_static_label` WRITE;
/*!40000 ALTER TABLE `adherent_adherent_static_label` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_adherent_static_label` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_certification_histories`
--

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
-- Table structure for table `adherent_declared_mandate_history`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_declared_mandate_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `added_mandates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `removed_mandates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `administrator_id` int DEFAULT NULL,
  `notified_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `telegram_notified_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
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
-- Table structure for table `adherent_email_subscription_histories`
--

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
-- Table structure for table `adherent_formation`
--

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
  `content_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `visibility` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
-- Table structure for table `adherent_mandate`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_mandate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `committee_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `begin_at` datetime NOT NULL,
  `finish_at` datetime DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quality` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provisional` tinyint(1) NOT NULL DEFAULT '0',
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `mandate_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delegation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9C0C3D60D17F50A6` (`uuid`),
  KEY `IDX_9C0C3D6025F06C53` (`adherent_id`),
  KEY `IDX_9C0C3D60ED1A100B` (`committee_id`),
  KEY `IDX_9C0C3D609DF5350C` (`created_by_administrator_id`),
  KEY `IDX_9C0C3D60CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_9C0C3D6085C9D733` (`created_by_adherent_id`),
  KEY `IDX_9C0C3D60DF6CFDC9` (`updated_by_adherent_id`),
  KEY `IDX_9C0C3D609F2C3FAB` (`zone_id`),
  KEY `IDX_9C0C3D608CDE5729` (`type`),
  CONSTRAINT `FK_9C0C3D6025F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9C0C3D6085C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9C0C3D609DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9C0C3D609F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE SET NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_message_filters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `committee_id` int unsigned DEFAULT NULL,
  `adherent_segment_id` int unsigned DEFAULT NULL,
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
  `postal_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mandate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `political_function` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_certified` tinyint(1) DEFAULT NULL,
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `audience_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_committee_member` tinyint(1) DEFAULT NULL,
  `last_membership_since` date DEFAULT NULL,
  `last_membership_before` date DEFAULT NULL,
  `mandate_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_campus_registered` tinyint(1) DEFAULT NULL,
  `donator_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `declared_mandate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adherent_tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `elect_tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `static_tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_membership_since` date DEFAULT NULL,
  `first_membership_before` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_28CA9F949F2C3FAB` (`zone_id`),
  KEY `IDX_28CA9F94DB296AAD` (`segment_id`),
  KEY `IDX_28CA9F94ED1A100B` (`committee_id`),
  KEY `IDX_28CA9F94FAF04979` (`adherent_segment_id`),
  CONSTRAINT `FK_28CA9F949F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_28CA9F94DB296AAD` FOREIGN KEY (`segment_id`) REFERENCES `audience_segment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_28CA9F94ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE SET NULL,
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
-- Table structure for table `adherent_message_reach`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_message_reach` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int unsigned NOT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_39E44CAD25F06C53537A13295F8A7F73` (`adherent_id`,`message_id`,`source`),
  KEY `IDX_39E44CAD537A1329` (`message_id`),
  KEY `IDX_39E44CAD25F06C53` (`adherent_id`),
  KEY `IDX_39E44CAD537A13295F8A7F73` (`message_id`,`source`),
  CONSTRAINT `FK_39E44CAD25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_39E44CAD537A1329` FOREIGN KEY (`message_id`) REFERENCES `adherent_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_message_reach`
--

LOCK TABLES `adherent_message_reach` WRITE;
/*!40000 ALTER TABLE `adherent_message_reach` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_message_reach` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_messages`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
  `filter_id` int unsigned DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `instance_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `recipient_count` int DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cadre',
  `json_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sender_id` int unsigned DEFAULT NULL,
  `sender_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_instance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_theme` json DEFAULT NULL,
  `is_statutory` tinyint(1) NOT NULL DEFAULT '0',
  `team_owner_id` int unsigned DEFAULT NULL,
  `sender_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_instance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_theme` json DEFAULT NULL,
  `instance_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D187C183D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_D187C183D395B25E` (`filter_id`),
  KEY `IDX_D187C183F675F31B` (`author_id`),
  KEY `IDX_D187C183F624B39D` (`sender_id`),
  KEY `IDX_D187C1837B00651C` (`status`),
  KEY `IDX_D187C1835F8A7F73` (`source`),
  KEY `IDX_D187C183C3144BB` (`instance_scope`),
  KEY `IDX_D187C183C67EBD87` (`team_owner_id`),
  CONSTRAINT `FK_D187C183C67EBD87` FOREIGN KEY (`team_owner_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_D187C183D395B25E` FOREIGN KEY (`filter_id`) REFERENCES `adherent_message_filters` (`id`),
  CONSTRAINT `FK_D187C183F624B39D` FOREIGN KEY (`sender_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_D187C183F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
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
-- Table structure for table `adherent_request`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_request` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `email_hash` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `account_created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BEE6BD11D17F50A6` (`uuid`),
  KEY `IDX_BEE6BD1125F06C53` (`adherent_id`),
  CONSTRAINT `FK_BEE6BD1125F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
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
-- Table structure for table `adherent_request_reminder`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_request_reminder` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_request_id` int unsigned DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D80F7E4CD17F50A6` (`uuid`),
  KEY `IDX_D80F7E4C63A79B71` (`adherent_request_id`),
  CONSTRAINT `FK_D80F7E4C63A79B71` FOREIGN KEY (`adherent_request_id`) REFERENCES `adherent_request` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_request_reminder`
--

LOCK TABLES `adherent_request_reminder` WRITE;
/*!40000 ALTER TABLE `adherent_request_reminder` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_request_reminder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_reset_password_tokens`
--

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
-- Table structure for table `adherent_static_label`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_static_label` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int unsigned NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F204E90277153098` (`code`),
  UNIQUE KEY `UNIQ_F204E902EA750E8` (`label`),
  KEY `IDX_F204E90212469DE2` (`category_id`),
  CONSTRAINT `FK_F204E90212469DE2` FOREIGN KEY (`category_id`) REFERENCES `adherent_static_label_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_static_label`
--

LOCK TABLES `adherent_static_label` WRITE;
/*!40000 ALTER TABLE `adherent_static_label` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_static_label` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_static_label_category`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_static_label_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sync` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_36F0D66C77153098` (`code`),
  UNIQUE KEY `UNIQ_36F0D66CEA750E8` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adherent_static_label_category`
--

LOCK TABLES `adherent_static_label_category` WRITE;
/*!40000 ALTER TABLE `adherent_static_label_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `adherent_static_label_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adherent_subscription_type`
--

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
-- Table structure for table `adherent_zone`
--

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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_zone_based_role` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `jecoute_managed_area_id` int DEFAULT NULL,
  `candidate_managed_area_id` int unsigned DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `birthdate` date DEFAULT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
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
  `certified_at` datetime DEFAULT NULL,
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_area` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membership_reminded_at` datetime DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vote_inspector` tinyint(1) NOT NULL DEFAULT '0',
  `national_role` tinyint(1) NOT NULL DEFAULT '0',
  `mailchimp_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phoning_manager_role` tinyint(1) NOT NULL DEFAULT '0',
  `pap_national_manager_role` tinyint(1) NOT NULL DEFAULT '0',
  `phone_verified_at` datetime DEFAULT NULL,
  `pap_user_role` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_membership_donation` datetime DEFAULT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `email_status_comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_contribution_id` int unsigned DEFAULT NULL,
  `contribution_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contributed_at` datetime DEFAULT NULL,
  `exempt_from_cotisation` tinyint(1) NOT NULL DEFAULT '0',
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `v2` tinyint(1) NOT NULL DEFAULT '0',
  `finished_adhesion_steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `accept_member_card` tinyint(1) NOT NULL DEFAULT '1',
  `address_additional_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_mailchimp_failed_sync_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `party_membership` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'exclusive',
  `resubscribe_email_sent_at` datetime DEFAULT NULL,
  `public_id` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_membership_donation` datetime DEFAULT NULL,
  `forced_membership` tinyint(1) NOT NULL DEFAULT '0',
  `unsubscribe_requested_at` datetime DEFAULT NULL,
  `sandbox_mode` tinyint(1) NOT NULL DEFAULT '0',
  `resubscribe_email_started_at` datetime DEFAULT NULL,
  `resubscribe_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `meeting_scanner` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_562C7DA3B08E074E` (`email_address`),
  UNIQUE KEY `UNIQ_562C7DA3D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_562C7DA3A188FE64` (`nickname`),
  UNIQUE KEY `UNIQ_562C7DA394E3BB99` (`jecoute_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA37657F304` (`candidate_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA314E51F8D` (`last_contribution_id`),
  UNIQUE KEY `UNIQ_562C7DA3B5B48B91` (`public_id`),
  KEY `IDX_562C7DA3EA9FDD75` (`media_id`),
  KEY `IDX_562C7DA39DF5350C` (`created_by_administrator_id`),
  KEY `IDX_562C7DA3CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_562C7DA36FBC9426` (`tags`(512)),
  KEY `IDX_562C7DA37B00651C` (`status`),
  KEY `IDX_562C7DA317BD45F1` (`mailchimp_status`),
  CONSTRAINT `FK_562C7DA314E51F8D` FOREIGN KEY (`last_contribution_id`) REFERENCES `contribution` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA37657F304` FOREIGN KEY (`candidate_managed_area_id`) REFERENCES `candidate_managed_area` (`id`),
  CONSTRAINT `FK_562C7DA394E3BB99` FOREIGN KEY (`jecoute_managed_area_id`) REFERENCES `jecoute_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA39DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA3CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA3EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
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
-- Table structure for table `administrator_action_history`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrator_action_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `administrator_id` int NOT NULL,
  `TYPE` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `data` json DEFAULT NULL,
  `telegram_notified_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_5A263AE84B09E92C` (`administrator_id`),
  CONSTRAINT `FK_5A263AE84B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrator_action_history`
--

LOCK TABLES `administrator_action_history` WRITE;
/*!40000 ALTER TABLE `administrator_action_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `administrator_action_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `administrator_export_history`
--

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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrator_role` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `group_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrator_role_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `administrator_id` int NOT NULL,
  `author_id` int DEFAULT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `administrator_zone`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrator_zone` (
  `administrator_id` int NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`administrator_id`,`zone_id`),
  KEY `IDX_2961ACEA4B09E92C` (`administrator_id`),
  KEY `IDX_2961ACEA9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_2961ACEA4B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2961ACEA9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrator_zone`
--

LOCK TABLES `administrator_zone` WRITE;
/*!40000 ALTER TABLE `administrator_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `administrator_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `administrators`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrators` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_authenticator_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
-- Table structure for table `agora`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agora` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `president_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `max_members_count` int unsigned NOT NULL DEFAULT '50',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `canonical_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A0B6A0FDD17F50A6` (`uuid`),
  KEY `IDX_A0B6A0FDB40A33C7` (`president_id`),
  KEY `IDX_A0B6A0FD9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_A0B6A0FDCF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_A0B6A0FD9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A0B6A0FDB40A33C7` FOREIGN KEY (`president_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A0B6A0FDCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agora`
--

LOCK TABLES `agora` WRITE;
/*!40000 ALTER TABLE `agora` DISABLE KEYS */;
/*!40000 ALTER TABLE `agora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agora_general_secretaries`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agora_general_secretaries` (
  `agora_id` int unsigned NOT NULL,
  `adherent_id` int unsigned NOT NULL,
  PRIMARY KEY (`agora_id`,`adherent_id`),
  KEY `IDX_18E675D157588F43` (`agora_id`),
  KEY `IDX_18E675D125F06C53` (`adherent_id`),
  CONSTRAINT `FK_18E675D125F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_18E675D157588F43` FOREIGN KEY (`agora_id`) REFERENCES `agora` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agora_general_secretaries`
--

LOCK TABLES `agora_general_secretaries` WRITE;
/*!40000 ALTER TABLE `agora_general_secretaries` DISABLE KEYS */;
/*!40000 ALTER TABLE `agora_general_secretaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agora_membership`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agora_membership` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `agora_id` int unsigned NOT NULL,
  `adherent_id` int unsigned NOT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9F885CDCD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_9F885CDC57588F4325F06C53` (`agora_id`,`adherent_id`),
  KEY `IDX_9F885CDC57588F43` (`agora_id`),
  KEY `IDX_9F885CDC25F06C53` (`adherent_id`),
  KEY `IDX_9F885CDC9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_9F885CDCCF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_9F885CDC25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9F885CDC57588F43` FOREIGN KEY (`agora_id`) REFERENCES `agora` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9F885CDC9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_9F885CDCCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agora_membership`
--

LOCK TABLES `agora_membership` WRITE;
/*!40000 ALTER TABLE `agora_membership` DISABLE KEYS */;
/*!40000 ALTER TABLE `agora_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `algolia_je_mengage_timeline_feed`
--

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
-- Table structure for table `app_alert`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_alert` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cta_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `share_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `begin_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `with_magic_link` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C12ECB0CD17F50A6` (`uuid`),
  KEY `IDX_C12ECB0C9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_C12ECB0CCF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_C12ECB0C9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C12ECB0CCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_alert`
--

LOCK TABLES `app_alert` WRITE;
/*!40000 ALTER TABLE `app_alert` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_alert` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_hit`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_hit` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `referrer_id` int unsigned DEFAULT NULL,
  `app_session_id` int unsigned DEFAULT NULL,
  `event_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `referrer_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_session_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `open_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `user_agent` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `app_system` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_date` datetime NOT NULL,
  `raw` json DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fingerprint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_74A09586D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_74A09586FC0B754A` (`fingerprint`),
  KEY `IDX_74A0958625F06C53` (`adherent_id`),
  KEY `IDX_74A09586372447A3` (`app_session_id`),
  KEY `IDX_74A09586798C22DB` (`referrer_id`),
  KEY `IDX_74A0958693151B82` (`event_type`),
  KEY `IDX_74A095865F8A7F73` (`source`),
  KEY `IDX_74A0958693151B825F8A7F73` (`event_type`,`source`),
  KEY `IDX_74A0958611CB6B3A` (`object_type`),
  KEY `IDX_74A09586232D562B` (`object_id`),
  KEY `IDX_74A09586F4C89FFA` (`source_group`),
  KEY `IDX_74A0958693151B82F4C89FFA` (`event_type`,`source_group`),
  CONSTRAINT `FK_74A0958625F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_74A09586372447A3` FOREIGN KEY (`app_session_id`) REFERENCES `app_session` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_74A09586798C22DB` FOREIGN KEY (`referrer_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_hit`
--

LOCK TABLES `app_hit` WRITE;
/*!40000 ALTER TABLE `app_hit` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_hit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_session`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_session` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity_date` datetime DEFAULT NULL,
  `user_agent` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `app_version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `app_system` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unsubscribed_at` datetime DEFAULT NULL,
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3D195599D17F50A6` (`uuid`),
  KEY `IDX_3D19559925F06C53` (`adherent_id`),
  KEY `IDX_3D19559919EB6921` (`client_id`),
  KEY `IDX_3D1955997B00651C` (`status`),
  KEY `IDX_3D1955992C4E7C0B` (`app_system`),
  CONSTRAINT `FK_3D19559919EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`),
  CONSTRAINT `FK_3D19559925F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_session`
--

LOCK TABLES `app_session` WRITE;
/*!40000 ALTER TABLE `app_session` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_session_push_token_link`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_session_push_token_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `app_session_id` int unsigned DEFAULT NULL,
  `push_token_id` int unsigned DEFAULT NULL,
  `last_activity_date` datetime DEFAULT NULL,
  `unsubscribed_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E592CD44D17F50A6` (`uuid`),
  KEY `IDX_E592CD44372447A3` (`app_session_id`),
  KEY `IDX_E592CD44258E0AE3` (`push_token_id`),
  CONSTRAINT `FK_E592CD44258E0AE3` FOREIGN KEY (`push_token_id`) REFERENCES `push_token` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E592CD44372447A3` FOREIGN KEY (`app_session_id`) REFERENCES `app_session` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_session_push_token_link`
--

LOCK TABLES `app_session_push_token_link` WRITE;
/*!40000 ALTER TABLE `app_session_push_token_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_session_push_token_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audience`
--

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
-- Table structure for table `besoindeurope_inscription_requests`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `besoindeurope_inscription_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_96473AF5D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `besoindeurope_inscription_requests`
--

LOCK TABLES `besoindeurope_inscription_requests` WRITE;
/*!40000 ALTER TABLE `besoindeurope_inscription_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `besoindeurope_inscription_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campus_registration`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campus_registration` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `event_maker_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `campus_event_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_maker_order_uid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `registered_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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
-- Table structure for table `chatbot`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chatbot` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `assistant_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `telegram_bot_api_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_bot_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7DC4B00477153098` (`code`),
  UNIQUE KEY `UNIQ_7DC4B004D17F50A6` (`uuid`),
  KEY `IDX_7DC4B0049DF5350C` (`created_by_administrator_id`),
  KEY `IDX_7DC4B004CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_7DC4B0049DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_7DC4B004CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot`
--

LOCK TABLES `chatbot` WRITE;
/*!40000 ALTER TABLE `chatbot` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatbot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatbot_message`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chatbot_message` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `thread_id` int unsigned NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EDF1E884D17F50A6` (`uuid`),
  KEY `IDX_EDF1E884E2904019` (`thread_id`),
  CONSTRAINT `FK_EDF1E884E2904019` FOREIGN KEY (`thread_id`) REFERENCES `chatbot_thread` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_message`
--

LOCK TABLES `chatbot_message` WRITE;
/*!40000 ALTER TABLE `chatbot_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatbot_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatbot_run`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chatbot_run` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `thread_id` int unsigned NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D603CBB6D17F50A6` (`uuid`),
  KEY `IDX_D603CBB6E2904019` (`thread_id`),
  CONSTRAINT `FK_D603CBB6E2904019` FOREIGN KEY (`thread_id`) REFERENCES `chatbot_thread` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_run`
--

LOCK TABLES `chatbot_run` WRITE;
/*!40000 ALTER TABLE `chatbot_run` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatbot_run` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatbot_thread`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chatbot_thread` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `chatbot_id` int unsigned NOT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `current_run_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_chat_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A356AA3CD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_A356AA3C832A24AA` (`current_run_id`),
  KEY `IDX_A356AA3C1984C580` (`chatbot_id`),
  KEY `IDX_A356AA3C25F06C53` (`adherent_id`),
  CONSTRAINT `FK_A356AA3C1984C580` FOREIGN KEY (`chatbot_id`) REFERENCES `chatbot` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A356AA3C25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A356AA3C832A24AA` FOREIGN KEY (`current_run_id`) REFERENCES `chatbot_run` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_thread`
--

LOCK TABLES `chatbot_thread` WRITE;
/*!40000 ALTER TABLE `chatbot_thread` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatbot_thread` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cms_block`
--

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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `command_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `committee_candidacies_group`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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
-- Table structure for table `committee_zone`
--

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
  `address_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `sympathizers_count` smallint unsigned NOT NULL DEFAULT '0',
  `animator_id` int unsigned DEFAULT NULL,
  `members_em_count` smallint unsigned NOT NULL DEFAULT '0',
  `address_additional_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adherents_count` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A36198C6D17F50A6` (`uuid`),
  KEY `IDX_A36198C67B00651C` (`status`),
  KEY `IDX_A36198C6B4D2A5D1` (`current_designation_id`),
  KEY `IDX_A36198C685C9D733` (`created_by_adherent_id`),
  KEY `IDX_A36198C6DF6CFDC9` (`updated_by_adherent_id`),
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
  `trigger` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_has_joined_committee` (`adherent_id`,`committee_id`),
  UNIQUE KEY `UNIQ_E7A6490ED17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_E7A6490E25F06C53` (`adherent_id`),
  UNIQUE KEY `adherent_votes_in_committee` (`adherent_id`,`enable_vote`),
  KEY `committees_memberships_role_idx` (`privilege`),
  KEY `IDX_E7A6490EED1A100B` (`committee_id`),
  CONSTRAINT `FK_E7A6490E25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
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
-- Table structure for table `consultation`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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
  `address_additional_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contribution` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `gocardless_customer_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_bank_account_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_bank_account_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `gocardless_mandate_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_mandate_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_subscription_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_subscription_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contribution_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `ohme_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime DEFAULT NULL,
  `method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contribution_revenue_declaration` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `amount` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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
-- Table structure for table `department_site`
--

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
  `denomination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'élection',
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
  `election_entity_identifier` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `is_canceled` tinyint(1) NOT NULL DEFAULT '0',
  `wording_regulation_page_id` int unsigned DEFAULT NULL,
  `alert_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alert_cta_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alert_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `target_year` smallint DEFAULT NULL,
  `alert_begin_at` datetime DEFAULT NULL,
  `enable_vote_questions_preview` tinyint(1) NOT NULL DEFAULT '1',
  `account_creation_deadline` datetime DEFAULT NULL,
  `result_display_blank` tinyint(1) NOT NULL DEFAULT '0',
  `membership_deadline` datetime DEFAULT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_candidacy_pool` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `designation_id` int unsigned DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_candidacy_pool_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `candidacy_pool_id` int unsigned NOT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_candidacy_pool_candidacy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `candidacy_pool_id` int unsigned NOT NULL,
  `candidacies_group_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_substitute` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `faith_statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_public_faith_statement` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation_poll_question` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` int unsigned NOT NULL,
  `content` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `position` int NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_separator` tinyint(1) NOT NULL DEFAULT '0',
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
-- Table structure for table `designation_zone`
--

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
-- Table structure for table `document`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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
  `visibility` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `re_adhesion` tinyint(1) NOT NULL DEFAULT '0',
  `address_additional_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `contribution_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_contribution` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `gocardless_customer_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `gocardless_bank_account_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_mandate_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_subscription_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_bank_account_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `gocardless_mandate_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gocardless_subscription_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `political_affiliation` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `la_remsupport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `on_going` tinyint(1) NOT NULL DEFAULT '1',
  `number` smallint NOT NULL DEFAULT '1',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `ohme_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime DEFAULT NULL,
  `method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_revenue_declaration` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int unsigned NOT NULL,
  `amount` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
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
-- Table structure for table `elected_representative_zone`
--

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
-- Table structure for table `election_rounds`
--

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
-- Table structure for table `elections`
--

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
  `scopes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `json_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_statutory` tinyint(1) NOT NULL DEFAULT '0',
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_editable` tinyint(1) NOT NULL DEFAULT '1',
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
  `use_template_endpoint` tinyint(1) NOT NULL DEFAULT '1',
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
-- Table structure for table `event_group_category`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_group_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ENABLED',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alert` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
-- Table structure for table `event_inscription_zone`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_inscription_zone` (
  `event_inscription_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`event_inscription_id`,`zone_id`),
  KEY `IDX_A74D587C82E8EFE0` (`event_inscription_id`),
  KEY `IDX_A74D587C9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_A74D587C82E8EFE0` FOREIGN KEY (`event_inscription_id`) REFERENCES `national_event_inscription` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A74D587C9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_inscription_zone`
--

LOCK TABLES `event_inscription_zone` WRITE;
/*!40000 ALTER TABLE `event_inscription_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_inscription_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_zone`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_zone` (
  `event_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`event_id`,`zone_id`),
  KEY `IDX_BF208CAC9F2C3FAB` (`zone_id`),
  KEY `IDX_BF208CAC71F7E88B` (`event_id`),
  CONSTRAINT `FK_BF208CAC71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
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
  `address_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `time_zone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visio_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminded` tinyint(1) NOT NULL DEFAULT '0',
  `electoral` tinyint(1) NOT NULL DEFAULT '0',
  `address_additional_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visibility` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `live_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_instance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_size` bigint DEFAULT NULL,
  `image_mime_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_width` int DEFAULT NULL,
  `image_height` int DEFAULT NULL,
  `national` tinyint(1) NOT NULL DEFAULT '0',
  `push_sent_at` datetime DEFAULT NULL,
  `send_invitation_email` tinyint(1) NOT NULL DEFAULT '1',
  `json_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `email_reminded` tinyint(1) NOT NULL DEFAULT '0',
  `agora_id` int unsigned DEFAULT NULL,
  `author_theme` json DEFAULT NULL,
  `adherents_up_to_date_count` smallint unsigned NOT NULL DEFAULT '0',
  `adherents_not_up_to_date_count` smallint unsigned NOT NULL DEFAULT '0',
  `sympathizers_count` smallint unsigned NOT NULL DEFAULT '0',
  `members_em_count` smallint unsigned NOT NULL DEFAULT '0',
  `citizens_count` smallint unsigned NOT NULL DEFAULT '0',
  `instance_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5387574A989D9B62` (`slug`),
  UNIQUE KEY `UNIQ_5387574AD17F50A6` (`uuid`),
  KEY `IDX_5387574A12469DE2` (`category_id`),
  KEY `IDX_5387574A3826374D` (`begin_at`),
  KEY `IDX_5387574A7B00651C` (`status`),
  KEY `IDX_5387574AED1A100B` (`committee_id`),
  KEY `IDX_5387574AFE28FD87` (`finish_at`),
  KEY `IDX_5387574AF675F31B` (`author_id`),
  KEY `IDX_5387574A57588F43` (`agora_id`),
  CONSTRAINT `FK_5387574A12469DE2` FOREIGN KEY (`category_id`) REFERENCES `events_categories` (`id`),
  CONSTRAINT `FK_5387574A57588F43` FOREIGN KEY (`agora_id`) REFERENCES `agora` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5387574AED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5387574AF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_group_category_id` int unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ENABLED',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alert` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events_registrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned DEFAULT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `newsletter_subscriber` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmed',
  `confirmed_at` datetime DEFAULT NULL,
  `referrer_id` int unsigned DEFAULT NULL,
  `referrer_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EEFA30C0D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_EEFA30C025F06C5371F7E88B` (`adherent_id`,`event_id`),
  KEY `IDX_EEFA30C0B08E074E` (`email_address`),
  KEY `IDX_EEFA30C071F7E88B` (`event_id`),
  KEY `IDX_EEFA30C025F06C53` (`adherent_id`),
  KEY `IDX_EEFA30C0798C22DB` (`referrer_id`),
  CONSTRAINT `FK_EEFA30C025F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_EEFA30C071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EEFA30C0798C22DB` FOREIGN KEY (`referrer_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
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
-- Table structure for table `general_convention`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `general_convention` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_zone_id` int unsigned NOT NULL,
  `committee_id` int unsigned DEFAULT NULL,
  `district_zone_id` int unsigned DEFAULT NULL,
  `reporter_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `organizer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reported_at` datetime NOT NULL,
  `meeting_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `members_count` smallint unsigned NOT NULL DEFAULT '0',
  `participant_quality` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `general_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `party_definition_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `unique_party_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `progress_since2016` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `party_objectives` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `governance` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `communication` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `militant_training` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `member_journey` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mobilization` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `talent_detection` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `election_preparation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `relationship_with_supporters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `work_with_partners` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `additional_comments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F66947EFD17F50A6` (`uuid`),
  KEY `IDX_F66947EF2285D748` (`department_zone_id`),
  KEY `IDX_F66947EF23F5C396` (`district_zone_id`),
  KEY `IDX_F66947EFE1CFE6F5` (`reporter_id`),
  KEY `IDX_F66947EF9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_F66947EFCF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_F66947EFED1A100B` (`committee_id`),
  CONSTRAINT `FK_F66947EF2285D748` FOREIGN KEY (`department_zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_F66947EF23F5C396` FOREIGN KEY (`district_zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_F66947EF9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_F66947EFCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_F66947EFE1CFE6F5` FOREIGN KEY (`reporter_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_F66947EFED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_convention`
--

LOCK TABLES `general_convention` WRITE;
/*!40000 ALTER TABLE `general_convention` DISABLE KEYS */;
/*!40000 ALTER TABLE `general_convention` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `general_meeting_report`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `general_meeting_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `created_by_adherent_id` int unsigned DEFAULT NULL,
  `updated_by_adherent_id` int unsigned DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date` datetime NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `visibility` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_vote_place` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int unsigned NOT NULL,
  `district_id` int unsigned NOT NULL,
  `canton_id` int unsigned DEFAULT NULL,
  `geo_data_id` int unsigned DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
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
-- Table structure for table `hub_item`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hub_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `position` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_195C97A2D17F50A6` (`uuid`),
  KEY `IDX_195C97A29DF5350C` (`created_by_administrator_id`),
  KEY `IDX_195C97A2CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_195C97A29DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_195C97A2CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hub_item`
--

LOCK TABLES `hub_item` WRITE;
/*!40000 ALTER TABLE `hub_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `hub_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `image`
--

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
-- Table structure for table `internal_api_application`
--

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
-- Table structure for table `jecoute_choice`
--

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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_news` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `external_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `topic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `visibility` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_instance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `committee_id` int unsigned DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `author_theme` json DEFAULT NULL,
  `instance_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3436209D17F50A6` (`uuid`),
  KEY `IDX_34362099F2C3FAB` (`zone_id`),
  KEY `IDX_3436209F675F31B` (`author_id`),
  KEY `IDX_3436209ED1A100B` (`committee_id`),
  KEY `IDX_34362099DF5350C` (`created_by_administrator_id`),
  KEY `IDX_3436209CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_34362099DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_34362099F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_3436209CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_3436209ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`),
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_news_user_documents` (
  `jecoute_news_id` int unsigned NOT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_17E1064BD17F50A6` (`uuid`),
  KEY `IDX_17E1064BB03A8386` (`created_by_id`),
  KEY `IDX_17E1064BF675F31B` (`author_id`),
  CONSTRAINT `FK_17E1064BB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_17E1064BF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
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
-- Table structure for table `jemengage_header_blocks`
--

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
-- Table structure for table `legislative_district_zones`
--

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
-- Table structure for table `live_stream`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `live_stream` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `begin_at` datetime NOT NULL,
  `finish_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_93BF08C8D17F50A6` (`uuid`),
  KEY `IDX_93BF08C89DF5350C` (`created_by_administrator_id`),
  KEY `IDX_93BF08C8CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_93BF08C89DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_93BF08C8CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `live_stream`
--

LOCK TABLES `live_stream` WRITE;
/*!40000 ALTER TABLE `live_stream` DISABLE KEYS */;
/*!40000 ALTER TABLE `live_stream` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local_election`
--

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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_election_substitute_candidacy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `candidacies_group_id` int unsigned DEFAULT NULL,
  `election_id` int unsigned NOT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `faith_statement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_public_faith_statement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailchimp_campaign_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `open_total` int NOT NULL,
  `open_unique` int NOT NULL,
  `open_rate` double NOT NULL,
  `last_open` datetime DEFAULT NULL,
  `click_total` int NOT NULL,
  `click_unique` int NOT NULL,
  `click_rate` double NOT NULL,
  `last_click` datetime DEFAULT NULL,
  `email_sent` int NOT NULL,
  `unsubscribed` int NOT NULL,
  `unsubscribed_rate` double NOT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailchimp_segment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `list` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` int DEFAULT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
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
INSERT INTO `migrations` VALUES ('Migrations\\Version20211126020536','2023-01-05 09:53:48',87),('Migrations\\Version20211126130535','2023-01-05 09:53:48',358),('Migrations\\Version20211126151434','2023-01-05 09:53:49',12),('Migrations\\Version20211126172212','2023-01-05 09:53:49',16),('Migrations\\Version20211130010431','2023-01-05 09:53:49',88),('Migrations\\Version20211130023420','2023-01-05 09:53:49',18),('Migrations\\Version20211130163308','2023-01-05 09:53:49',171),('Migrations\\Version20211202125517','2023-01-05 09:53:49',28),('Migrations\\Version20211202151633','2023-01-05 09:53:49',169),('Migrations\\Version20211202191607','2023-01-05 09:53:49',16),('Migrations\\Version20211203111253','2023-01-05 09:53:49',59),('Migrations\\Version20211203112333','2023-01-05 09:53:49',11),('Migrations\\Version20211206110133','2023-01-05 09:53:49',52),('Migrations\\Version20211206115819','2023-01-05 09:53:49',11),('Migrations\\Version20211207152117','2023-01-05 09:53:49',149),('Migrations\\Version20211208111523','2023-01-05 09:53:49',89),('Migrations\\Version20211209114932','2023-01-05 09:53:50',158),('Migrations\\Version20211209142923','2023-01-05 09:53:50',59),('Migrations\\Version20211220162832','2023-01-05 09:53:50',41),('Migrations\\Version20220103181110','2023-01-05 09:53:50',371),('Migrations\\Version20220104015613','2023-01-05 09:53:50',120),('Migrations\\Version20220106143719','2023-01-05 09:53:50',30),('Migrations\\Version20220110122036','2023-01-05 09:53:50',147),('Migrations\\Version20220110135715','2023-01-05 09:53:50',52),('Migrations\\Version20220110165351','2023-01-05 09:53:51',19),('Migrations\\Version20220110185032','2023-01-05 09:53:51',141),('Migrations\\Version20220112153457','2023-01-05 09:53:51',15),('Migrations\\Version20220113001351','2023-01-05 09:53:51',9),('Migrations\\Version20220113175738','2023-01-05 09:53:51',2454),('Migrations\\Version20220114104853','2023-01-05 09:53:53',113),('Migrations\\Version20220114151651','2023-01-05 09:53:53',19),('Migrations\\Version20220117173646','2023-01-05 09:53:53',13),('Migrations\\Version20220119094725','2023-01-05 09:53:53',54),('Migrations\\Version20220119160854','2023-01-05 09:53:53',106),('Migrations\\Version20220120173302','2023-01-05 09:53:53',150),('Migrations\\Version20220124162602','2023-01-05 09:53:54',82),('Migrations\\Version20220126182307','2023-01-05 09:53:54',55),('Migrations\\Version20220204141925','2023-01-05 09:53:54',11),('Migrations\\Version20220204164624','2023-01-05 09:53:54',209),('Migrations\\Version20220207135908','2023-01-05 09:53:54',56),('Migrations\\Version20220211115509','2023-01-05 09:53:54',216),('Migrations\\Version20220217113503','2023-01-05 09:53:54',9),('Migrations\\Version20220217124001','2023-01-05 09:53:54',16),('Migrations\\Version20220218125939','2023-01-05 09:53:54',107),('Migrations\\Version20220221172957','2023-01-05 09:53:54',48),('Migrations\\Version20220222082557','2023-01-05 09:53:54',170),('Migrations\\Version20220223181739','2023-01-05 09:53:55',41),('Migrations\\Version20220228120338','2023-01-05 09:53:55',12),('Migrations\\Version20220301133517','2023-01-05 09:53:55',47),('Migrations\\Version20220301190248','2023-01-05 09:53:55',14),('Migrations\\Version20220304164524','2023-01-05 09:53:55',69),('Migrations\\Version20220307111506','2023-01-05 09:53:55',58),('Migrations\\Version20220308175358','2023-01-05 09:53:55',12),('Migrations\\Version20220309113232','2023-01-05 09:53:55',11),('Migrations\\Version20220309113539','2023-01-05 09:53:55',33),('Migrations\\Version20220310121312','2023-01-05 09:53:55',13),('Migrations\\Version20220311152121','2023-01-05 09:53:55',13),('Migrations\\Version20220314141817','2023-01-05 09:53:55',45),('Migrations\\Version20220314155701','2023-01-05 09:53:55',11),('Migrations\\Version20220315005750','2023-01-05 09:53:55',106),('Migrations\\Version20220315170237','2023-01-05 09:53:55',89),('Migrations\\Version20220318201041','2023-01-05 09:53:55',39),('Migrations\\Version20220323165400','2023-01-05 09:53:55',6),('Migrations\\Version20220324112855','2023-01-05 09:53:55',84),('Migrations\\Version20220325172441','2023-01-05 09:53:55',398),('Migrations\\Version20220401171433','2023-01-05 09:53:56',12),('Migrations\\Version20220406110135','2023-01-05 09:53:56',45),('Migrations\\Version20220407142612','2023-01-05 09:53:56',69),('Migrations\\Version20220408144824','2023-01-05 09:53:56',43),('Migrations\\Version20220412120724','2023-01-05 09:53:56',54),('Migrations\\Version20220412172359','2023-01-05 09:53:56',601),('Migrations\\Version20220413192745','2023-01-05 09:53:57',22),('Migrations\\Version20220414170047','2023-01-05 09:53:57',14),('Migrations\\Version20220414211940','2023-01-05 09:53:57',28),('Migrations\\Version20220419193718','2023-01-05 09:53:57',116),('Migrations\\Version20220421132926','2023-01-05 09:53:57',45),('Migrations\\Version20220426163649','2023-01-05 09:53:57',31),('Migrations\\Version20220428150916','2023-01-05 09:53:57',13),('Migrations\\Version20220429114235','2023-01-05 09:53:57',115),('Migrations\\Version20220504182132','2023-01-05 09:53:57',37),('Migrations\\Version20220510102006','2023-01-05 09:53:57',14),('Migrations\\Version20220517152903','2023-01-05 09:53:57',13),('Migrations\\Version20220519181035','2023-01-05 09:53:57',25),('Migrations\\Version20220524105827','2023-01-05 09:53:57',55),('Migrations\\Version20220601181917','2023-01-05 09:53:57',405),('Migrations\\Version20220608011640','2023-01-05 09:53:58',57),('Migrations\\Version20220613112116','2023-01-05 09:53:58',33),('Migrations\\Version20220614100027','2023-01-05 09:53:58',72),('Migrations\\Version20220616095027','2023-01-05 09:53:58',9),('Migrations\\Version20220616140609','2023-01-05 09:53:58',67),('Migrations\\Version20220616154158','2023-01-05 09:53:58',14),('Migrations\\Version20220616165915','2023-01-05 09:53:58',11),('Migrations\\Version20220617190412','2023-01-05 09:53:58',12),('Migrations\\Version20220622122711','2023-01-05 09:53:58',167),('Migrations\\Version20220705022722','2023-01-05 09:53:58',22),('Migrations\\Version20220706000352','2023-01-05 09:53:58',138),('Migrations\\Version20220721160526','2023-01-05 09:53:58',14),('Migrations\\Version20220816180602','2023-01-05 09:53:58',21),('Migrations\\Version20220913155038','2023-01-05 09:53:58',739),('Migrations\\Version20220914170047','2023-01-05 09:53:59',18),('Migrations\\Version20220917042906','2023-01-05 09:53:59',174),('Migrations\\Version20220917135347','2023-01-05 09:53:59',283),('Migrations\\Version20220917153647','2023-01-05 09:53:59',205),('Migrations\\Version20220921132543','2023-01-05 09:54:00',180),('Migrations\\Version20220921152207','2023-01-05 09:54:00',16),('Migrations\\Version20220922172929','2023-01-05 09:54:00',8),('Migrations\\Version20220923100710','2023-01-05 09:54:00',7),('Migrations\\Version20220923154015','2023-01-05 09:54:00',19),('Migrations\\Version20220929173136','2023-01-05 09:54:00',28),('Migrations\\Version20220930151511','2023-01-05 09:54:00',24),('Migrations\\Version20221003152108','2023-01-05 09:54:00',82),('Migrations\\Version20221004115251','2023-01-05 09:54:00',23),('Migrations\\Version20221004155004','2023-01-05 09:54:00',19),('Migrations\\Version20221005132710','2023-01-05 09:54:00',10),('Migrations\\Version20221006135107','2023-01-05 09:54:00',27),('Migrations\\Version20221006182442','2023-01-05 09:54:00',9),('Migrations\\Version20221007154603','2023-01-05 09:54:00',8),('Migrations\\Version20221010123838','2023-01-05 09:54:00',8),('Migrations\\Version20221017232936','2023-01-05 09:54:00',8),('Migrations\\Version20221020113016','2023-01-05 09:54:00',168),('Migrations\\Version20221021140835','2023-01-05 09:54:00',14),('Migrations\\Version20221026085426','2023-01-05 09:54:00',113),('Migrations\\Version20221026160544','2023-01-05 09:54:00',215),('Migrations\\Version20221031150105','2023-01-05 09:54:01',21),('Migrations\\Version20221101210133','2023-01-05 09:54:01',29),('Migrations\\Version20221106232616','2023-01-05 09:54:01',194),('Migrations\\Version20221109170709','2023-01-05 09:54:01',34),('Migrations\\Version20221110000854','2023-01-05 09:54:01',11),('Migrations\\Version20221115082558','2023-01-05 09:54:01',35),('Migrations\\Version20221122094902','2023-01-05 09:54:01',348),('Migrations\\Version20221202153430','2023-01-05 09:54:01',50),('Migrations\\Version20221202161505','2023-01-05 09:54:01',12),('Migrations\\Version20221207092223','2023-01-05 09:54:01',57),('Migrations\\Version20221209122913','2023-01-05 09:54:02',361),('Migrations\\Version20221209162545','2023-01-05 09:54:02',136),('Migrations\\Version20221213112859','2023-01-05 09:54:02',114),('Migrations\\Version20221214111850','2023-01-05 09:54:02',15),('Migrations\\Version20221215150113','2023-01-05 09:54:02',25),('Migrations\\Version20221216123241','2023-01-05 09:54:02',9),('Migrations\\Version20221222094329','2023-01-05 09:54:02',53),('Migrations\\Version20230103112607','2024-09-25 10:41:30',423),('Migrations\\Version20230112170300','2024-09-25 10:41:30',33),('Migrations\\Version20230113162740','2024-09-25 10:41:30',212),('Migrations\\Version20230113181056','2024-09-25 10:41:31',206),('Migrations\\Version20230117112145','2024-09-25 10:41:31',216),('Migrations\\Version20230117175420','2024-09-25 10:41:31',59),('Migrations\\Version20230123015605','2024-09-25 10:41:31',10),('Migrations\\Version20230124144750','2024-09-25 10:41:31',267),('Migrations\\Version20230124152540','2024-09-25 10:41:31',4),('Migrations\\Version20230201185840','2024-09-25 10:41:31',49),('Migrations\\Version20230207011026','2024-09-25 10:41:31',186),('Migrations\\Version20230207174546','2024-09-25 10:41:32',127),('Migrations\\Version20230208105829','2024-09-25 10:41:32',94),('Migrations\\Version20230209153258','2024-09-25 10:41:32',57),('Migrations\\Version20230214143118','2024-09-25 10:41:32',38),('Migrations\\Version20230215084303','2024-09-25 10:41:32',117),('Migrations\\Version20230217112001','2024-09-25 10:41:32',75),('Migrations\\Version20230217142406','2024-09-25 10:41:32',35),('Migrations\\Version20230217143246','2024-09-25 10:41:32',95),('Migrations\\Version20230224152955','2024-09-25 10:41:32',68),('Migrations\\Version20230228165519','2024-09-25 10:41:32',25),('Migrations\\Version20230301155217','2024-09-25 10:41:32',160),('Migrations\\Version20230306120354','2024-09-25 10:41:33',31),('Migrations\\Version20230307142934','2024-09-25 10:41:33',75),('Migrations\\Version20230308172514','2024-09-25 10:41:33',8),('Migrations\\Version20230309121041','2024-09-25 10:41:33',17),('Migrations\\Version20230316170530','2024-09-25 10:41:33',46),('Migrations\\Version20230322144816','2024-09-25 10:41:33',16),('Migrations\\Version20230322174658','2024-09-25 10:41:33',8),('Migrations\\Version20230323111246','2024-09-25 10:41:33',8),('Migrations\\Version20230324113203','2024-09-25 10:41:33',4),('Migrations\\Version20230327141628','2024-09-25 10:41:33',9),('Migrations\\Version20230329012543','2024-09-25 10:41:33',38),('Migrations\\Version20230330153931','2024-09-25 10:41:33',73),('Migrations\\Version20230330173213','2024-09-25 10:41:33',13),('Migrations\\Version20230331125853','2024-09-25 10:41:33',150),('Migrations\\Version20230405125351','2024-09-25 10:41:33',9),('Migrations\\Version20230418095845','2024-09-25 10:41:33',13),('Migrations\\Version20230419141029','2024-09-25 10:41:33',33),('Migrations\\Version20230419170927','2024-09-25 10:41:33',3),('Migrations\\Version20230426174026','2024-09-25 10:41:33',154),('Migrations\\Version20230427153956','2024-09-25 10:41:33',81),('Migrations\\Version20230427165714','2024-09-25 10:41:33',9),('Migrations\\Version20230502141531','2024-09-25 10:41:33',32),('Migrations\\Version20230504151826','2024-09-25 10:41:33',122),('Migrations\\Version20230504163117','2024-09-25 10:41:33',36),('Migrations\\Version20230510153522','2024-09-25 10:41:34',8),('Migrations\\Version20230511181544','2024-09-25 10:41:34',18),('Migrations\\Version20230515153520','2024-09-25 10:41:34',75),('Migrations\\Version20230523012308','2024-09-25 10:41:34',6),('Migrations\\Version20230523023528','2024-09-25 10:41:34',46),('Migrations\\Version20230525133109','2024-09-25 10:41:34',14),('Migrations\\Version20230525135030','2024-09-25 10:41:34',5),('Migrations\\Version20230602161100','2024-09-25 10:41:34',19),('Migrations\\Version20230606133239','2024-09-25 10:41:34',4),('Migrations\\Version20230607155251','2024-09-25 10:41:34',10),('Migrations\\Version20230614123149','2024-09-25 10:41:34',79),('Migrations\\Version20230616145405','2024-09-25 10:41:34',54),('Migrations\\Version20230620145022','2024-09-25 10:41:34',3),('Migrations\\Version20230621084524','2024-09-25 10:41:34',17),('Migrations\\Version20230623073145','2024-09-25 10:41:34',5),('Migrations\\Version20230623101144','2024-09-25 10:41:34',26),('Migrations\\Version20230623103320','2024-09-25 10:41:34',17),('Migrations\\Version20230623153454','2024-09-25 10:41:34',28),('Migrations\\Version20230623173752','2024-09-25 10:41:34',14),('Migrations\\Version20230627233532','2024-09-25 10:41:34',5),('Migrations\\Version20230628150524','2024-09-25 10:41:34',10),('Migrations\\Version20230705072301','2024-09-25 10:41:34',34),('Migrations\\Version20230713081354','2024-09-25 10:41:34',463),('Migrations\\Version20230713171821','2024-09-25 10:41:35',260),('Migrations\\Version20230718161858','2024-09-25 10:41:35',31),('Migrations\\Version20230718180507','2024-09-25 10:41:35',180),('Migrations\\Version20230720163801','2024-09-25 10:41:35',6),('Migrations\\Version20230724100823','2024-09-25 10:41:35',82),('Migrations\\Version20230727095418','2024-09-25 10:41:35',389),('Migrations\\Version20230831072408','2024-09-25 10:41:35',14),('Migrations\\Version20230904142735','2024-09-25 10:41:35',8),('Migrations\\Version20230906080635','2024-09-25 10:41:35',6),('Migrations\\Version20230907113553','2024-09-25 10:41:35',20),('Migrations\\Version20230908112010','2024-09-25 10:41:36',29),('Migrations\\Version20230914080444','2024-09-25 10:41:36',54),('Migrations\\Version20230926123728','2024-09-25 10:41:36',22),('Migrations\\Version20230927161359','2024-09-25 10:41:36',162),('Migrations\\Version20231003095946','2024-09-25 10:41:36',122),('Migrations\\Version20231004161126','2024-09-25 10:41:36',98),('Migrations\\Version20231005220834','2024-09-25 10:41:36',111),('Migrations\\Version20231006082726','2024-09-25 10:41:36',111),('Migrations\\Version20231010151645','2024-09-25 10:41:36',20),('Migrations\\Version20231012080033','2024-09-25 10:41:36',6),('Migrations\\Version20231016232208','2024-09-25 10:41:36',9),('Migrations\\Version20231018081458','2024-09-25 10:41:36',34),('Migrations\\Version20231023161507','2024-09-25 10:41:36',8),('Migrations\\Version20231102091259','2024-09-25 10:41:36',4),('Migrations\\Version20231103172715','2024-09-25 10:41:36',55),('Migrations\\Version20231106102259','2024-09-25 10:41:36',6),('Migrations\\Version20231117170244','2024-09-25 10:41:36',5),('Migrations\\Version20231130142149','2024-09-25 10:41:36',14),('Migrations\\Version20231207093329','2024-09-25 10:41:36',31),('Migrations\\Version20231208002730','2024-09-25 10:41:36',85),('Migrations\\Version20231214003554','2024-09-25 10:41:37',103),('Migrations\\Version20231217142736','2024-09-25 10:41:37',144),('Migrations\\Version20240111110813','2025-01-29 14:54:08',123),('Migrations\\Version20240115220546','2025-01-29 14:54:08',308),('Migrations\\Version20240116132005','2025-01-29 14:54:08',137),('Migrations\\Version20240118042542','2025-01-29 14:54:09',29),('Migrations\\Version20240123220055','2025-01-29 14:54:09',11),('Migrations\\Version20240126174201','2025-01-29 14:54:09',122),('Migrations\\Version20240203171047','2025-01-29 14:54:09',110),('Migrations\\Version20240207165446','2025-01-29 14:54:09',22),('Migrations\\Version20240216181147','2025-01-29 14:54:09',37),('Migrations\\Version20240220182326','2025-01-29 14:54:09',13),('Migrations\\Version20240221091851','2025-01-29 14:54:09',42),('Migrations\\Version20240221143404','2025-01-29 14:54:09',81),('Migrations\\Version20240221145728','2025-01-29 14:54:09',97),('Migrations\\Version20240223132047','2025-01-29 14:54:09',24),('Migrations\\Version20240304021611','2025-01-29 14:54:09',100),('Migrations\\Version20240306174317','2025-01-29 14:54:09',25),('Migrations\\Version20240307093902','2025-01-29 14:54:09',11),('Migrations\\Version20240307095411','2025-01-29 14:54:09',344),('Migrations\\Version20240308114452','2025-01-29 14:54:10',35),('Migrations\\Version20240311084338','2025-01-29 14:54:10',27),('Migrations\\Version20240314121122','2025-01-29 14:54:10',215),('Migrations\\Version20240319113037','2025-01-29 14:54:10',11),('Migrations\\Version20240319133416','2025-01-29 14:54:10',15),('Migrations\\Version20240319155519','2025-01-29 14:54:10',51),('Migrations\\Version20240321162127','2025-01-29 14:54:10',102),('Migrations\\Version20240322150932','2025-01-29 14:54:10',62),('Migrations\\Version20240325145823','2025-01-29 14:54:10',23),('Migrations\\Version20240327081758','2025-01-29 14:54:10',31),('Migrations\\Version20240329140615','2025-01-29 14:54:10',78),('Migrations\\Version20240329144236','2025-01-29 14:54:10',20),('Migrations\\Version20240329204549','2025-01-29 14:54:10',319),('Migrations\\Version20240402164724','2025-01-29 14:54:11',25),('Migrations\\Version20240403223008','2025-01-29 14:54:11',6),('Migrations\\Version20240409130540','2025-01-29 14:54:11',11),('Migrations\\Version20240410063924','2025-01-29 14:54:11',43),('Migrations\\Version20240416154418','2025-01-29 14:54:11',119),('Migrations\\Version20240417135955','2025-01-29 14:54:11',22),('Migrations\\Version20240424115627','2025-01-29 14:54:11',25),('Migrations\\Version20240506153531','2025-01-29 14:54:11',15),('Migrations\\Version20240510130946','2025-01-29 14:54:11',16),('Migrations\\Version20240514084526','2025-01-29 14:54:11',57),('Migrations\\Version20240517080738','2025-01-29 14:54:11',48),('Migrations\\Version20240517142406','2025-01-29 14:54:11',14),('Migrations\\Version20240523163640','2025-01-29 14:54:11',96),('Migrations\\Version20240527140711','2025-01-29 14:54:11',154),('Migrations\\Version20240527144138','2025-01-29 14:54:11',14),('Migrations\\Version20240529124938','2025-01-29 14:54:11',16),('Migrations\\Version20240603164507','2025-01-29 14:54:11',15),('Migrations\\Version20240605204832','2025-01-29 14:54:11',122),('Migrations\\Version20240611152139','2025-01-29 14:54:12',36),('Migrations\\Version20240611175556','2025-01-29 14:54:12',144),('Migrations\\Version20240614123828','2025-01-29 14:54:12',177),('Migrations\\Version20240615094405','2025-01-29 14:54:12',76),('Migrations\\Version20240618131552','2025-01-29 14:54:12',42),('Migrations\\Version20240618143502','2025-01-29 14:54:12',38),('Migrations\\Version20240618152952','2025-01-29 14:54:12',127),('Migrations\\Version20240619114342','2025-01-29 14:54:12',35),('Migrations\\Version20240621152825','2025-01-29 14:54:12',96),('Migrations\\Version20240622141935','2025-01-29 14:54:12',335),('Migrations\\Version20240623120631','2025-01-29 14:54:13',18),('Migrations\\Version20240624143855','2025-01-29 14:54:13',66),('Migrations\\Version20240625165041','2025-01-29 14:54:13',25),('Migrations\\Version20240628155502','2025-01-29 14:54:13',31),('Migrations\\Version20240702163555','2025-01-29 14:54:13',22),('Migrations\\Version20240708161136','2025-01-29 14:54:13',392),('Migrations\\Version20240709094154','2025-01-29 14:54:13',26),('Migrations\\Version20240710155639','2025-01-29 14:54:13',43),('Migrations\\Version20240711165324','2025-01-29 14:54:13',25),('Migrations\\Version20240711214827','2025-01-29 14:54:13',33),('Migrations\\Version20240715130054','2025-01-29 14:54:13',45),('Migrations\\Version20240716171532','2025-01-29 14:54:13',7),('Migrations\\Version20240718150142','2025-01-29 14:54:13',12),('Migrations\\Version20240718152519','2025-01-29 14:54:13',3),('Migrations\\Version20240719125151','2025-01-29 14:54:13',690),('Migrations\\Version20240724074801','2025-01-29 14:54:14',8),('Migrations\\Version20240724080325','2025-01-29 14:54:14',3),('Migrations\\Version20240724084413','2025-01-29 14:54:14',12),('Migrations\\Version20240724111618','2025-01-29 14:54:14',63),('Migrations\\Version20240724122946','2025-01-29 14:54:14',13),('Migrations\\Version20240726172241','2025-01-29 14:54:14',1219),('Migrations\\Version20240802121942','2025-01-29 14:54:15',108),('Migrations\\Version20240827135834','2025-01-29 14:54:16',15),('Migrations\\Version20240829173637','2025-01-29 14:54:16',74),('Migrations\\Version20240905154629','2025-01-29 14:54:16',58),('Migrations\\Version20240906131000','2025-01-29 14:54:16',25),('Migrations\\Version20240906151212','2025-01-29 14:54:16',85),('Migrations\\Version20240909120040','2025-01-29 14:54:16',22),('Migrations\\Version20240911151018','2025-01-29 14:54:16',8),('Migrations\\Version20240918194959','2025-01-29 14:54:16',213),('Migrations\\Version20240924075613','2025-01-29 14:54:16',36),('Migrations\\Version20240924123109','2025-01-29 14:54:16',25),('Migrations\\Version20240924133206','2025-01-29 14:54:16',1),('Migrations\\Version20240925100251','2025-01-29 14:54:16',13),('Migrations\\Version20240925134021','2025-01-29 14:54:16',16),('Migrations\\Version20240926163535','2025-01-29 14:54:16',16),('Migrations\\Version20241002124458','2025-01-29 14:54:16',48),('Migrations\\Version20241002132246','2025-01-29 14:54:16',16),('Migrations\\Version20241003080535','2025-01-29 14:54:16',28),('Migrations\\Version20241008144514','2025-01-29 14:54:16',23),('Migrations\\Version20241009112808','2025-01-29 14:54:16',55),('Migrations\\Version20241011115333','2025-01-29 14:54:16',12),('Migrations\\Version20241016164551','2025-01-29 14:54:16',25),('Migrations\\Version20241017170655','2025-01-29 14:54:16',9),('Migrations\\Version20241018074940','2025-01-29 14:54:16',102),('Migrations\\Version20241018120624','2025-01-29 14:54:16',19),('Migrations\\Version20241108095136','2025-01-29 14:54:16',30),('Migrations\\Version20241108150457','2025-01-29 14:54:17',3),('Migrations\\Version20241116225647','2025-01-29 14:54:17',4),('Migrations\\Version20241119103423','2025-01-29 14:54:17',11),('Migrations\\Version20241119155704','2025-01-29 14:54:17',25),('Migrations\\Version20241122093716','2025-01-29 14:54:17',12),('Migrations\\Version20241127160637','2025-01-29 14:54:17',14),('Migrations\\Version20241128231215','2025-01-29 14:54:17',271),('Migrations\\Version20241128232852','2025-01-29 14:54:17',134),('Migrations\\Version20241204170446','2025-01-29 14:54:17',21),('Migrations\\Version20241209093727','2025-01-29 14:54:17',161),('Migrations\\Version20241217101253','2025-01-29 14:54:17',57),('Migrations\\Version20241217133344','2025-01-29 14:54:17',15),('Migrations\\Version20241219151508','2025-01-29 14:54:17',129),('Migrations\\Version20241219172627','2025-01-29 14:54:17',131),('Migrations\\Version20241231142707','2025-01-29 14:54:18',95),('Migrations\\Version20250102173536','2026-01-02 17:36:51',118),('Migrations\\Version20250108110304','2026-01-02 17:36:51',195),('Migrations\\Version20250108154409','2026-01-02 17:36:51',284),('Migrations\\Version20250109162538','2026-01-02 17:36:51',39),('Migrations\\Version20250114160957','2026-01-02 17:36:52',23),('Migrations\\Version20250114174700','2026-01-02 17:36:52',28),('Migrations\\Version20250117171230','2026-01-02 17:36:52',146),('Migrations\\Version20250120134757','2026-01-02 17:36:52',56),('Migrations\\Version20250124163712','2026-01-02 17:36:52',14),('Migrations\\Version20250128150801','2026-01-02 17:36:52',6),('Migrations\\Version20250129125624','2026-01-02 17:36:52',3),('Migrations\\Version20250129170122','2026-01-02 17:36:52',8),('Migrations\\Version20250131111843','2026-01-02 17:36:52',152),('Migrations\\Version20250131151354','2026-01-02 17:36:52',11),('Migrations\\Version20250201142049','2026-01-02 17:36:52',76),('Migrations\\Version20250203133315','2026-01-02 17:36:52',23),('Migrations\\Version20250203144259','2026-01-02 17:36:52',40),('Migrations\\Version20250206095029','2026-01-02 17:36:52',45),('Migrations\\Version20250210161256','2026-01-02 17:36:52',24),('Migrations\\Version20250211155729','2026-01-02 17:36:52',13),('Migrations\\Version20250213155841','2026-01-02 17:36:52',41),('Migrations\\Version20250217081741','2026-01-02 17:36:52',22),('Migrations\\Version20250217214657','2026-01-02 17:36:52',16),('Migrations\\Version20250218130105','2026-01-02 17:36:52',21),('Migrations\\Version20250220094247','2026-01-02 17:36:52',43),('Migrations\\Version20250220095159','2026-01-02 17:36:52',13),('Migrations\\Version20250220104643','2026-01-02 17:36:52',12),('Migrations\\Version20250220105051','2026-01-02 17:36:52',12),('Migrations\\Version20250220155540','2026-01-02 17:36:52',5),('Migrations\\Version20250221150858','2026-01-02 17:36:52',13),('Migrations\\Version20250227133157','2026-01-02 17:36:52',36),('Migrations\\Version20250304125045','2026-01-02 17:36:52',17),('Migrations\\Version20250306133133','2026-01-02 17:36:52',9),('Migrations\\Version20250307142630','2026-01-02 17:36:52',26),('Migrations\\Version20250313145558','2026-01-02 17:36:53',13),('Migrations\\Version20250314091155','2026-01-02 17:36:53',12),('Migrations\\Version20250314101422','2026-01-02 17:36:53',106),('Migrations\\Version20250317152336','2026-01-02 17:36:53',15),('Migrations\\Version20250318155112','2026-01-02 17:36:53',101),('Migrations\\Version20250319104842','2026-01-02 17:36:53',35),('Migrations\\Version20250326133754','2026-01-02 17:36:53',13),('Migrations\\Version20250402145616','2026-01-02 17:36:53',37),('Migrations\\Version20250404070221','2026-01-02 17:36:53',11),('Migrations\\Version20250404122129','2026-01-02 17:36:53',14),('Migrations\\Version20250404164852','2026-01-02 17:36:53',15),('Migrations\\Version20250407114219','2026-01-02 17:36:53',11),('Migrations\\Version20250407134718','2026-01-02 17:36:53',14),('Migrations\\Version20250410083459','2026-01-02 17:36:53',97),('Migrations\\Version20250411102157','2026-01-02 17:36:53',25),('Migrations\\Version20250411105857','2026-01-02 17:36:53',15),('Migrations\\Version20250411134834','2026-01-02 17:36:53',11),('Migrations\\Version20250414131242','2026-01-02 17:36:53',113),('Migrations\\Version20250415094308','2026-01-02 17:36:53',32),('Migrations\\Version20250416162120','2026-01-02 17:36:53',10),('Migrations\\Version20250418122712','2026-01-02 17:36:53',16),('Migrations\\Version20250422114538','2026-01-02 17:36:53',15),('Migrations\\Version20250422123124','2026-01-02 17:36:53',14),('Migrations\\Version20250422125416','2026-01-02 17:36:53',19),('Migrations\\Version20250423143331','2026-01-02 17:36:53',3),('Migrations\\Version20250423143830','2026-01-02 17:36:53',99),('Migrations\\Version20250429104126','2026-01-02 17:36:53',106),('Migrations\\Version20250430123214','2026-01-02 17:36:54',24),('Migrations\\Version20250430141243','2026-01-02 17:36:54',93),('Migrations\\Version20250506132144','2026-01-02 17:36:54',83),('Migrations\\Version20250512135725','2026-01-02 17:36:54',16),('Migrations\\Version20250519121503','2026-01-02 17:36:54',20),('Migrations\\Version20250519124858','2026-01-02 17:36:54',12),('Migrations\\Version20250520114901','2026-01-02 17:36:54',47),('Migrations\\Version20250521150439','2026-01-02 17:36:54',14),('Migrations\\Version20250523120644','2026-01-02 17:36:54',25),('Migrations\\Version20250527141715','2026-01-02 17:36:54',23),('Migrations\\Version20250603092125','2026-01-02 17:36:54',96),('Migrations\\Version20250603155747','2026-01-02 17:36:54',14),('Migrations\\Version20250610074255','2026-01-02 17:36:54',34),('Migrations\\Version20250611083310','2026-01-02 17:36:54',44),('Migrations\\Version20250613144613','2026-01-02 17:36:54',116),('Migrations\\Version20250616084932','2026-01-02 17:36:54',14),('Migrations\\Version20250616222016','2026-01-02 17:36:54',14),('Migrations\\Version20250617143908','2026-01-02 17:36:54',19),('Migrations\\Version20250618130849','2026-01-02 17:36:54',50),('Migrations\\Version20250619124131','2026-01-02 17:36:54',63),('Migrations\\Version20250623080633','2026-01-02 17:36:54',11),('Migrations\\Version20250623111722','2026-01-02 17:36:54',12),('Migrations\\Version20250624125305','2026-01-02 17:36:54',17),('Migrations\\Version20250625085743','2026-01-02 17:36:54',28),('Migrations\\Version20250627081559','2026-01-02 17:36:54',76),('Migrations\\Version20250701102058','2026-01-02 17:36:54',9),('Migrations\\Version20250701152300','2026-01-02 17:36:54',68),('Migrations\\Version20250702081141','2026-01-02 17:36:55',18),('Migrations\\Version20250703081536','2026-01-02 17:36:55',88),('Migrations\\Version20250703153724','2026-01-02 17:36:55',73),('Migrations\\Version20250703170232','2026-01-02 17:36:55',34),('Migrations\\Version20250704121611','2026-01-02 17:36:55',18),('Migrations\\Version20250708083237','2026-01-02 17:36:55',23),('Migrations\\Version20250708085624','2026-01-02 17:36:55',43),('Migrations\\Version20250710200648','2026-01-02 17:36:55',46),('Migrations\\Version20250715143041','2026-01-02 17:36:55',17),('Migrations\\Version20250716220725','2026-01-02 17:36:55',16),('Migrations\\Version20250718132735','2026-01-02 17:36:55',34),('Migrations\\Version20250721093908','2026-01-02 17:36:55',20),('Migrations\\Version20250721162032','2026-01-02 17:36:55',24),('Migrations\\Version20250723100955','2026-01-02 17:36:55',35),('Migrations\\Version20250730144505','2026-01-02 17:36:55',43),('Migrations\\Version20250822094428','2026-01-02 17:36:55',63),('Migrations\\Version20250825155758','2026-01-02 17:36:55',21),('Migrations\\Version20250826090334','2026-01-02 17:36:55',20),('Migrations\\Version20250826144950','2026-01-02 17:36:55',79),('Migrations\\Version20250828213838','2026-01-02 17:36:55',75),('Migrations\\Version20250902083106','2026-01-02 17:36:55',10),('Migrations\\Version20250903135117','2026-01-02 17:36:55',105),('Migrations\\Version20250911151519','2026-01-02 17:36:55',25),('Migrations\\Version20250911222728','2026-01-02 17:36:56',108),('Migrations\\Version20250912095153','2026-01-02 17:36:56',60),('Migrations\\Version20250912144715','2026-01-02 17:36:56',34),('Migrations\\Version20250912150338','2026-01-02 17:36:56',21),('Migrations\\Version20250912152038','2026-01-02 17:36:56',53),('Migrations\\Version20250912181307','2026-01-02 17:36:56',24),('Migrations\\Version20250917072949','2026-01-02 17:36:56',23),('Migrations\\Version20250919143710','2026-01-02 17:36:56',18),('Migrations\\Version20250920073436','2026-01-02 17:36:56',13),('Migrations\\Version20250924075627','2026-01-02 17:36:56',25),('Migrations\\Version20250930092405','2026-01-02 17:36:56',28),('Migrations\\Version20250930173255','2026-01-02 17:36:56',13),('Migrations\\Version20251013092944','2026-01-02 17:36:56',14),('Migrations\\Version20251014091711','2026-01-02 17:36:56',3),('Migrations\\Version20251015130941','2026-01-02 17:36:56',59),('Migrations\\Version20251016165537','2026-01-02 17:36:56',24),('Migrations\\Version20251020083347','2026-01-02 17:36:56',89),('Migrations\\Version20251021144834','2026-01-02 17:36:56',6),('Migrations\\Version20251022232142','2026-01-02 17:36:56',85),('Migrations\\Version20251030171255','2026-01-02 17:36:56',58),('Migrations\\Version20251103205053','2026-01-02 17:36:56',120),('Migrations\\Version20251104140908','2026-01-02 17:36:56',15),('Migrations\\Version20251107170113','2026-01-02 17:36:56',5),('Migrations\\Version20251120101147','2026-01-02 17:36:56',114),('Migrations\\Version20251125003333','2026-01-02 17:36:57',221),('Migrations\\Version20251126182523','2026-01-02 17:36:57',20),('Migrations\\Version20251127142258','2026-01-02 17:36:57',44),('Migrations\\Version20251127162906','2026-01-02 17:36:57',12),('Migrations\\Version20251128234343','2026-01-02 17:36:57',10),('Migrations\\Version20251129103559','2026-01-02 17:36:57',13),('Migrations\\Version20251205174729','2026-01-02 17:36:57',2),('Migrations\\Version20251208175641','2026-01-02 17:36:57',32),('Migrations\\Version20251224111850','2026-01-02 17:36:57',93),('Migrations\\Version20251231114250','2026-01-02 17:36:57',11);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mooc`
--

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
-- Table structure for table `moodle_user`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moodle_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `moodle_id` int unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5EB3C2D25F06C53` (`adherent_id`),
  CONSTRAINT `FK_5EB3C2D25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moodle_user`
--

LOCK TABLES `moodle_user` WRITE;
/*!40000 ALTER TABLE `moodle_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `moodle_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moodle_user_job`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moodle_user_job` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `moodle_id` int unsigned NOT NULL,
  `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_47A4CBDA76ED395` (`user_id`),
  CONSTRAINT `FK_47A4CBDA76ED395` FOREIGN KEY (`user_id`) REFERENCES `moodle_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moodle_user_job`
--

LOCK TABLES `moodle_user_job` WRITE;
/*!40000 ALTER TABLE `moodle_user_job` DISABLE KEYS */;
/*!40000 ALTER TABLE `moodle_user_job` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `my_team`
--

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
-- Table structure for table `my_team_delegated_access`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `my_team_delegated_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `delegator_id` int unsigned DEFAULT NULL,
  `delegated_id` int unsigned NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accesses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `scope_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `role_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_421C13B9D17F50A6` (`uuid`),
  KEY `IDX_421C13B98825BEFA` (`delegator_id`),
  KEY `IDX_421C13B9B7E7AE18` (`delegated_id`),
  CONSTRAINT `FK_421C13B98825BEFA` FOREIGN KEY (`delegator_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_421C13B9B7E7AE18` FOREIGN KEY (`delegated_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
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
-- Table structure for table `national_event`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_event` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `ticket_start_date` datetime NOT NULL,
  `ticket_end_date` datetime NOT NULL,
  `text_intro` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `text_help` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `text_confirmation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `canonical_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `text_ticket_email` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_ticket_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_ticket_email` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `og_image_id` int unsigned DEFAULT NULL,
  `og_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alert_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alert_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alert_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `inscription_edit_deadline` datetime DEFAULT NULL,
  `logo_image_id` int unsigned DEFAULT NULL,
  `into_image_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `package_config` json DEFAULT NULL,
  `mailchimp_sync` tinyint(1) NOT NULL DEFAULT '0',
  `default_access` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_bracelet` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_bracelet_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alert_logo_image_id` int unsigned DEFAULT NULL,
  `connection_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `discount_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_help` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AD037664D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_AD0376646EFCB8B8` (`og_image_id`),
  UNIQUE KEY `UNIQ_AD0376646D947EBB` (`logo_image_id`),
  UNIQUE KEY `UNIQ_AD037664DC0A230D` (`into_image_id`),
  UNIQUE KEY `UNIQ_AD037664DD86B734` (`alert_logo_image_id`),
  KEY `IDX_AD0376649DF5350C` (`created_by_administrator_id`),
  KEY `IDX_AD037664CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_AD0376646D947EBB` FOREIGN KEY (`logo_image_id`) REFERENCES `uploadable_file` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_AD0376646EFCB8B8` FOREIGN KEY (`og_image_id`) REFERENCES `uploadable_file` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_AD0376649DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_AD037664CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_AD037664DC0A230D` FOREIGN KEY (`into_image_id`) REFERENCES `uploadable_file` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_AD037664DD86B734` FOREIGN KEY (`alert_logo_image_id`) REFERENCES `uploadable_file` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_event`
--

LOCK TABLES `national_event` WRITE;
/*!40000 ALTER TABLE `national_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `national_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `national_event_inscription`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_event_inscription` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `join_newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `client_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_check` json DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `ticket_sent_at` datetime DEFAULT NULL,
  `ticket_custom_detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ticket_qrcode_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qualities` json DEFAULT NULL,
  `birth_place` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accessibility` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transport_needs` tinyint(1) NOT NULL DEFAULT '0',
  `volunteer` tinyint(1) NOT NULL DEFAULT '0',
  `referrer_id` int unsigned DEFAULT NULL,
  `referrer_code` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `children` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_responsibility_waived` tinyint(1) NOT NULL DEFAULT '0',
  `confirmed_at` datetime DEFAULT NULL,
  `push_sent_at` datetime DEFAULT NULL,
  `first_ticket_scanned_at` datetime DEFAULT NULL,
  `is_jam` tinyint(1) NOT NULL DEFAULT '0',
  `visit_day` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` smallint unsigned DEFAULT NULL,
  `with_discount` tinyint(1) DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `payment_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duplicate_inscription_for_status_id` int unsigned DEFAULT NULL,
  `confirmation_sent_at` datetime DEFAULT NULL,
  `accommodation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roommate_identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_id` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validation_comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `validation_started_at` datetime DEFAULT NULL,
  `validation_finished_at` datetime DEFAULT NULL,
  `last_ticket_scanned_at` datetime DEFAULT NULL,
  `ticket_bracelet_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ticket_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `transport_detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `accommodation_detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `custom_detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ticket_bracelet` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_plan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_departure_time` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_donation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C3325557D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_C3325557E3A4459E` (`ticket_uuid`),
  UNIQUE KEY `UNIQ_C3325557B5B48B91` (`public_id`),
  KEY `IDX_C332555771F7E88B` (`event_id`),
  KEY `IDX_C332555725F06C53` (`adherent_id`),
  KEY `IDX_C3325557798C22DB` (`referrer_id`),
  KEY `IDX_C3325557CC613791` (`duplicate_inscription_for_status_id`),
  CONSTRAINT `FK_C332555725F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C332555771F7E88B` FOREIGN KEY (`event_id`) REFERENCES `national_event` (`id`),
  CONSTRAINT `FK_C3325557798C22DB` FOREIGN KEY (`referrer_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C3325557CC613791` FOREIGN KEY (`duplicate_inscription_for_status_id`) REFERENCES `national_event_inscription` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_event_inscription`
--

LOCK TABLES `national_event_inscription` WRITE;
/*!40000 ALTER TABLE `national_event_inscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `national_event_inscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `national_event_inscription_payment`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_event_inscription_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `inscription_id` int unsigned DEFAULT NULL,
  `payload` json NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `amount` int unsigned NOT NULL DEFAULT '0',
  `transport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `with_discount` tinyint(1) DEFAULT NULL,
  `replacement_id` int unsigned DEFAULT NULL,
  `accommodation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_refund` tinyint(1) NOT NULL DEFAULT '0',
  `visit_day` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expired_checked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D0696D12D17F50A6` (`uuid`),
  KEY `IDX_D0696D125DAC5993` (`inscription_id`),
  KEY `IDX_D0696D129D25CF90` (`replacement_id`),
  CONSTRAINT `FK_D0696D125DAC5993` FOREIGN KEY (`inscription_id`) REFERENCES `national_event_inscription` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D0696D129D25CF90` FOREIGN KEY (`replacement_id`) REFERENCES `national_event_inscription_payment` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_event_inscription_payment`
--

LOCK TABLES `national_event_inscription_payment` WRITE;
/*!40000 ALTER TABLE `national_event_inscription_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `national_event_inscription_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `national_event_inscription_payment_status`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_event_inscription_payment_status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `payload` json NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `payment_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_746EBF594C3A3BB` (`payment_id`),
  CONSTRAINT `FK_746EBF594C3A3BB` FOREIGN KEY (`payment_id`) REFERENCES `national_event_inscription_payment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_event_inscription_payment_status`
--

LOCK TABLES `national_event_inscription_payment_status` WRITE;
/*!40000 ALTER TABLE `national_event_inscription_payment_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `national_event_inscription_payment_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `national_event_inscription_reminder`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_event_inscription_reminder` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `inscription_id` int unsigned DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CD82035C5DAC5993` (`inscription_id`),
  CONSTRAINT `FK_CD82035C5DAC5993` FOREIGN KEY (`inscription_id`) REFERENCES `national_event_inscription` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_event_inscription_reminder`
--

LOCK TABLES `national_event_inscription_reminder` WRITE;
/*!40000 ALTER TABLE `national_event_inscription_reminder` DISABLE KEYS */;
/*!40000 ALTER TABLE `national_event_inscription_reminder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `national_event_inscription_scan`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_event_inscription_scan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `inscription_id` int unsigned DEFAULT NULL,
  `scanned_by_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `inscription_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_284E9A8DD17F50A6` (`uuid`),
  KEY `IDX_284E9A8D5DAC5993` (`inscription_id`),
  KEY `IDX_284E9A8DEBBC642F` (`scanned_by_id`),
  CONSTRAINT `FK_284E9A8D5DAC5993` FOREIGN KEY (`inscription_id`) REFERENCES `national_event_inscription` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_284E9A8DEBBC642F` FOREIGN KEY (`scanned_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `national_event_inscription_scan`
--

LOCK TABLES `national_event_inscription_scan` WRITE;
/*!40000 ALTER TABLE `national_event_inscription_scan` DISABLE KEYS */;
/*!40000 ALTER TABLE `national_event_inscription_scan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_subscriptions`
--

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
  `scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `notification_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BF5476CAD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_BF5476CAB7E9E9E` (`notification_key`)
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
  `app_session_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CA42527C772E836A` (`identifier`),
  UNIQUE KEY `UNIQ_CA42527CD17F50A6` (`uuid`),
  KEY `IDX_CA42527C19EB6921` (`client_id`),
  KEY `IDX_CA42527C94A4C7D4` (`device_id`),
  KEY `IDX_CA42527CA76ED395` (`user_id`),
  KEY `IDX_CA42527C372447A3` (`app_session_id`),
  CONSTRAINT `FK_CA42527C19EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`),
  CONSTRAINT `FK_CA42527C372447A3` FOREIGN KEY (`app_session_id`) REFERENCES `app_session` (`id`) ON DELETE CASCADE,
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
  `session_enabled` tinyint(1) NOT NULL DEFAULT '1',
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
-- Table structure for table `ohme_contact`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ohme_contact` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `ohme_identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `civility` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address_street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_street2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_post_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ohme_created_at` datetime DEFAULT NULL,
  `ohme_updated_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `payment_count` int DEFAULT NULL,
  `last_payment_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A4E1D16CF82D7740` (`ohme_identifier`),
  UNIQUE KEY `UNIQ_A4E1D16CD17F50A6` (`uuid`),
  KEY `IDX_A4E1D16C25F06C53` (`adherent_id`),
  KEY `IDX_A4E1D16C9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_A4E1D16CCF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_A4E1D16CF82D7740` (`ohme_identifier`),
  CONSTRAINT `FK_A4E1D16C25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A4E1D16C9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A4E1D16CCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ohme_contact`
--

LOCK TABLES `ohme_contact` WRITE;
/*!40000 ALTER TABLE `ohme_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `ohme_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pap_address`
--

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
  `priority` smallint unsigned DEFAULT NULL,
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
  `status_detail` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `close_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `programs` smallint unsigned DEFAULT NULL,
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
  `nb_distributed_programs` smallint unsigned NOT NULL DEFAULT '0',
  `status_detail` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `status_detail` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
-- Table structure for table `petition_signature`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `petition_signature` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `civility` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `petition_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `petition_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `validated_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `reminded_at` datetime DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_347C2710D17F50A6` (`uuid`),
  KEY `IDX_347C271025F06C53` (`adherent_id`),
  CONSTRAINT `FK_347C271025F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `petition_signature`
--

LOCK TABLES `petition_signature` WRITE;
/*!40000 ALTER TABLE `petition_signature` DISABLE KEYS */;
/*!40000 ALTER TABLE `petition_signature` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phoning_campaign`
--

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
-- Table structure for table `poll`
--

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
-- Table structure for table `procuration_v2_elections`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_elections` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_confirmation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_legal` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proxy_title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proxy_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proxy_confirmation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proxy_legal` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B8544E75E237E06` (`name`),
  UNIQUE KEY `UNIQ_B8544E7989D9B62` (`slug`),
  UNIQUE KEY `UNIQ_B8544E7D17F50A6` (`uuid`),
  KEY `IDX_B8544E79DF5350C` (`created_by_administrator_id`),
  KEY `IDX_B8544E7CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_B8544E79DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_B8544E7CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_elections`
--

LOCK TABLES `procuration_v2_elections` WRITE;
/*!40000 ALTER TABLE `procuration_v2_elections` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_elections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_initial_requests`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_initial_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `utm_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reminded_at` datetime DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4BF11906D17F50A6` (`uuid`),
  KEY `IDX_4BF1190625F06C53` (`adherent_id`),
  CONSTRAINT `FK_4BF1190625F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_initial_requests`
--

LOCK TABLES `procuration_v2_initial_requests` WRITE;
/*!40000 ALTER TABLE `procuration_v2_initial_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_initial_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_matching_history`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_matching_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int unsigned NOT NULL,
  `proxy_id` int unsigned NOT NULL,
  `matcher_id` int unsigned DEFAULT NULL,
  `admin_matcher_id` int DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `email_copy` tinyint(1) NOT NULL DEFAULT '0',
  `round_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4B792213427EB8A5` (`request_id`),
  KEY `IDX_4B792213DB26A4E` (`proxy_id`),
  KEY `IDX_4B792213F38CBA7C` (`matcher_id`),
  KEY `IDX_4B7922133BB21CF9` (`admin_matcher_id`),
  KEY `IDX_4B792213A6005CA0` (`round_id`),
  CONSTRAINT `FK_4B7922133BB21CF9` FOREIGN KEY (`admin_matcher_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_4B792213427EB8A5` FOREIGN KEY (`request_id`) REFERENCES `procuration_v2_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4B792213A6005CA0` FOREIGN KEY (`round_id`) REFERENCES `procuration_v2_rounds` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4B792213DB26A4E` FOREIGN KEY (`proxy_id`) REFERENCES `procuration_v2_proxies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4B792213F38CBA7C` FOREIGN KEY (`matcher_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_matching_history`
--

LOCK TABLES `procuration_v2_matching_history` WRITE;
/*!40000 ALTER TABLE `procuration_v2_matching_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_matching_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_proxies`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_proxies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `vote_zone_id` int unsigned NOT NULL,
  `vote_place_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `elector_number` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slots` smallint unsigned NOT NULL DEFAULT '1',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_names` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `distant_vote_place` tinyint(1) NOT NULL DEFAULT '0',
  `client_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_additional_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_vote_place` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `join_newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `status_detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accept_vote_nearby` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4D04EBA4D17F50A6` (`uuid`),
  KEY `IDX_4D04EBA49DF5350C` (`created_by_administrator_id`),
  KEY `IDX_4D04EBA4CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_4D04EBA4149E6033` (`vote_zone_id`),
  KEY `IDX_4D04EBA4F3F90B30` (`vote_place_id`),
  KEY `IDX_4D04EBA425F06C53` (`adherent_id`),
  KEY `IDX_4D04EBA47B00651C` (`status`),
  KEY `IDX_4D04EBA48B8E8428` (`created_at`),
  CONSTRAINT `FK_4D04EBA4149E6033` FOREIGN KEY (`vote_zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_4D04EBA425F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_4D04EBA49DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_4D04EBA4CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_4D04EBA4F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_proxies`
--

LOCK TABLES `procuration_v2_proxies` WRITE;
/*!40000 ALTER TABLE `procuration_v2_proxies` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_proxies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_proxy_action`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_proxy_action` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `proxy_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `author_administrator_id` int DEFAULT NULL,
  `context` json DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `author_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B35088FD17F50A6` (`uuid`),
  KEY `IDX_B35088FDB26A4E` (`proxy_id`),
  KEY `IDX_B35088FF675F31B` (`author_id`),
  KEY `IDX_B35088F9301170` (`author_administrator_id`),
  CONSTRAINT `FK_B35088F9301170` FOREIGN KEY (`author_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_B35088FDB26A4E` FOREIGN KEY (`proxy_id`) REFERENCES `procuration_v2_proxies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B35088FF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_proxy_action`
--

LOCK TABLES `procuration_v2_proxy_action` WRITE;
/*!40000 ALTER TABLE `procuration_v2_proxy_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_proxy_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_proxy_slot`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_proxy_slot` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `proxy_id` int unsigned NOT NULL,
  `round_id` int unsigned NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `manual` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_87509068D17F50A6` (`uuid`),
  KEY `IDX_875090689DF5350C` (`created_by_administrator_id`),
  KEY `IDX_87509068CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_87509068DB26A4E` (`proxy_id`),
  KEY `IDX_87509068A6005CA0` (`round_id`),
  KEY `IDX_8750906810DBBEC4` (`manual`),
  CONSTRAINT `FK_875090689DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_87509068A6005CA0` FOREIGN KEY (`round_id`) REFERENCES `procuration_v2_rounds` (`id`),
  CONSTRAINT `FK_87509068CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_87509068DB26A4E` FOREIGN KEY (`proxy_id`) REFERENCES `procuration_v2_proxies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_proxy_slot`
--

LOCK TABLES `procuration_v2_proxy_slot` WRITE;
/*!40000 ALTER TABLE `procuration_v2_proxy_slot` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_proxy_slot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_proxy_slot_action`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_proxy_slot_action` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `proxy_slot_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `author_administrator_id` int DEFAULT NULL,
  `context` json DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `author_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_535D3E99D17F50A6` (`uuid`),
  KEY `IDX_535D3E994FCCD8F9` (`proxy_slot_id`),
  KEY `IDX_535D3E99F675F31B` (`author_id`),
  KEY `IDX_535D3E999301170` (`author_administrator_id`),
  CONSTRAINT `FK_535D3E994FCCD8F9` FOREIGN KEY (`proxy_slot_id`) REFERENCES `procuration_v2_proxy_slot` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_535D3E999301170` FOREIGN KEY (`author_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_535D3E99F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_proxy_slot_action`
--

LOCK TABLES `procuration_v2_proxy_slot_action` WRITE;
/*!40000 ALTER TABLE `procuration_v2_proxy_slot_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_proxy_slot_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_request_action`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_request_action` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `author_administrator_id` int DEFAULT NULL,
  `context` json DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `author_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_24E294ABD17F50A6` (`uuid`),
  KEY `IDX_24E294AB427EB8A5` (`request_id`),
  KEY `IDX_24E294ABF675F31B` (`author_id`),
  KEY `IDX_24E294AB9301170` (`author_administrator_id`),
  CONSTRAINT `FK_24E294AB427EB8A5` FOREIGN KEY (`request_id`) REFERENCES `procuration_v2_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_24E294AB9301170` FOREIGN KEY (`author_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_24E294ABF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_request_action`
--

LOCK TABLES `procuration_v2_request_action` WRITE;
/*!40000 ALTER TABLE `procuration_v2_request_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_request_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_request_slot`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_request_slot` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `request_id` int unsigned NOT NULL,
  `proxy_slot_id` int unsigned DEFAULT NULL,
  `round_id` int unsigned NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `manual` tinyint(1) NOT NULL DEFAULT '0',
  `match_reminded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DA56A35FD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_DA56A35F4FCCD8F9` (`proxy_slot_id`),
  KEY `IDX_DA56A35F9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_DA56A35FCF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_DA56A35F427EB8A5` (`request_id`),
  KEY `IDX_DA56A35FA6005CA0` (`round_id`),
  KEY `IDX_DA56A35F10DBBEC4` (`manual`),
  CONSTRAINT `FK_DA56A35F427EB8A5` FOREIGN KEY (`request_id`) REFERENCES `procuration_v2_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DA56A35F4FCCD8F9` FOREIGN KEY (`proxy_slot_id`) REFERENCES `procuration_v2_proxy_slot` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_DA56A35F9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_DA56A35FA6005CA0` FOREIGN KEY (`round_id`) REFERENCES `procuration_v2_rounds` (`id`),
  CONSTRAINT `FK_DA56A35FCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_request_slot`
--

LOCK TABLES `procuration_v2_request_slot` WRITE;
/*!40000 ALTER TABLE `procuration_v2_request_slot` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_request_slot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_request_slot_action`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_request_slot_action` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `request_slot_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `author_administrator_id` int DEFAULT NULL,
  `context` json DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `author_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A50F299D17F50A6` (`uuid`),
  KEY `IDX_A50F29973C163CB` (`request_slot_id`),
  KEY `IDX_A50F299F675F31B` (`author_id`),
  KEY `IDX_A50F2999301170` (`author_administrator_id`),
  CONSTRAINT `FK_A50F29973C163CB` FOREIGN KEY (`request_slot_id`) REFERENCES `procuration_v2_request_slot` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A50F2999301170` FOREIGN KEY (`author_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A50F299F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_request_slot_action`
--

LOCK TABLES `procuration_v2_request_slot_action` WRITE;
/*!40000 ALTER TABLE `procuration_v2_request_slot_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_request_slot_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_requests`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `vote_zone_id` int unsigned NOT NULL,
  `vote_place_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_names` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date NOT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `distant_vote_place` tinyint(1) NOT NULL DEFAULT '0',
  `client_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_additional_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_vote_place` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `join_newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `from_france` tinyint(1) NOT NULL DEFAULT '1',
  `status_detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F6D458CBD17F50A6` (`uuid`),
  KEY `IDX_F6D458CB9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_F6D458CBCF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_F6D458CB149E6033` (`vote_zone_id`),
  KEY `IDX_F6D458CBF3F90B30` (`vote_place_id`),
  KEY `IDX_F6D458CB25F06C53` (`adherent_id`),
  KEY `IDX_F6D458CB7B00651C` (`status`),
  KEY `IDX_F6D458CB8B8E8428` (`created_at`),
  CONSTRAINT `FK_F6D458CB149E6033` FOREIGN KEY (`vote_zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_F6D458CB25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_F6D458CB9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_F6D458CBCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_F6D458CBF3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_requests`
--

LOCK TABLES `procuration_v2_requests` WRITE;
/*!40000 ALTER TABLE `procuration_v2_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `procuration_v2_rounds`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_v2_rounds` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned NOT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A2DDD28D17F50A6` (`uuid`),
  KEY `IDX_A2DDD28A708DAFF` (`election_id`),
  KEY `IDX_A2DDD289DF5350C` (`created_by_administrator_id`),
  KEY `IDX_A2DDD28CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_A2DDD28AA9E377A` (`date`),
  CONSTRAINT `FK_A2DDD289DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A2DDD28A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `procuration_v2_elections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A2DDD28CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `procuration_v2_rounds`
--

LOCK TABLES `procuration_v2_rounds` WRITE;
/*!40000 ALTER TABLE `procuration_v2_rounds` DISABLE KEYS */;
/*!40000 ALTER TABLE `procuration_v2_rounds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projection_managed_users`
--

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
  `adherent_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_membership_donation` datetime DEFAULT NULL,
  `committee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `committee_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `nationality` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `cotisation_dates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `campus_registered_at` datetime DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `mandates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `declared_mandates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `zones_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mailchimp_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resubscribe_email_sent_at` datetime DEFAULT NULL,
  `first_membership_donation` datetime DEFAULT NULL,
  `public_id` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_logged_at` datetime DEFAULT NULL,
  `agora` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agora_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
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
-- Table structure for table `proxy_round`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proxy_round` (
  `proxy_id` int unsigned NOT NULL,
  `round_id` int unsigned NOT NULL,
  PRIMARY KEY (`proxy_id`,`round_id`),
  KEY `IDX_1C924019DB26A4E` (`proxy_id`),
  KEY `IDX_1C924019A6005CA0` (`round_id`),
  CONSTRAINT `FK_1C924019A6005CA0` FOREIGN KEY (`round_id`) REFERENCES `procuration_v2_rounds` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1C924019DB26A4E` FOREIGN KEY (`proxy_id`) REFERENCES `procuration_v2_proxies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proxy_round`
--

LOCK TABLES `proxy_round` WRITE;
/*!40000 ALTER TABLE `proxy_round` DISABLE KEYS */;
/*!40000 ALTER TABLE `proxy_round` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `push_token`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_token` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `last_activity_date` datetime DEFAULT NULL,
  `unsubscribed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_51BC1381772E836A` (`identifier`),
  UNIQUE KEY `UNIQ_51BC1381D17F50A6` (`uuid`)
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
-- Table structure for table `referral`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referral` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `referrer_id` int unsigned DEFAULT NULL,
  `referred_id` int unsigned DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `birthdate` date DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_additional_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `civility` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identifier` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reported_at` datetime DEFAULT NULL,
  `email_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `for_sympathizer` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_73079D00D17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_73079D00772E836A` (`identifier`),
  KEY `IDX_73079D00798C22DB` (`referrer_id`),
  KEY `IDX_73079D00CFE2A98` (`referred_id`),
  CONSTRAINT `FK_73079D00798C22DB` FOREIGN KEY (`referrer_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_73079D00CFE2A98` FOREIGN KEY (`referred_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referral`
--

LOCK TABLES `referral` WRITE;
/*!40000 ALTER TABLE `referral` DISABLE KEYS */;
/*!40000 ALTER TABLE `referral` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rememberme_token`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rememberme_token` (
  `series` varchar(88) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(88) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastUsed` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `renaissance_newsletter_subscription` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `token` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  CONSTRAINT `FK_F11FA745F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
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
-- Table structure for table `request_round`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_round` (
  `request_id` int unsigned NOT NULL,
  `round_id` int unsigned NOT NULL,
  PRIMARY KEY (`request_id`,`round_id`),
  KEY `IDX_98F95611427EB8A5` (`request_id`),
  KEY `IDX_98F95611A6005CA0` (`round_id`),
  CONSTRAINT `FK_98F95611427EB8A5` FOREIGN KEY (`request_id`) REFERENCES `procuration_v2_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_98F95611A6005CA0` FOREIGN KEY (`round_id`) REFERENCES `procuration_v2_rounds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_round`
--

LOCK TABLES `request_round` WRITE;
/*!40000 ALTER TABLE `request_round` DISABLE KEYS */;
/*!40000 ALTER TABLE `request_round` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scope`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scope` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `apps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `canary_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `color_primary` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_soft` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_hover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_active` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
-- Table structure for table `social_share_categories`
--

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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_shares` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
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
-- Table structure for table `tax_receipt`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tax_receipt` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `donator_id` int unsigned NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_12D1164FD17F50A6` (`uuid`),
  KEY `IDX_12D1164F831BACAF` (`donator_id`),
  CONSTRAINT `FK_12D1164F831BACAF` FOREIGN KEY (`donator_id`) REFERENCES `donators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax_receipt`
--

LOCK TABLES `tax_receipt` WRITE;
/*!40000 ALTER TABLE `tax_receipt` DISABLE KEYS */;
/*!40000 ALTER TABLE `tax_receipt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team`
--

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
-- Table structure for table `timeline_item_private_message`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_item_private_message` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_notification_active` tinyint(1) NOT NULL DEFAULT '1',
  `notification_sent_at` datetime DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cta_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CD291A19D17F50A6` (`uuid`),
  KEY `IDX_CD291A199DF5350C` (`created_by_administrator_id`),
  KEY `IDX_CD291A19CF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_CD291A199DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_CD291A19CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_item_private_message`
--

LOCK TABLES `timeline_item_private_message` WRITE;
/*!40000 ALTER TABLE `timeline_item_private_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_item_private_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_item_private_message_adherent`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_item_private_message_adherent` (
  `timeline_item_private_message_id` int unsigned NOT NULL,
  `adherent_id` int unsigned NOT NULL,
  PRIMARY KEY (`timeline_item_private_message_id`,`adherent_id`),
  KEY `IDX_A4581DEC87293FB5` (`timeline_item_private_message_id`),
  KEY `IDX_A4581DEC25F06C53` (`adherent_id`),
  CONSTRAINT `FK_A4581DEC25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A4581DEC87293FB5` FOREIGN KEY (`timeline_item_private_message_id`) REFERENCES `timeline_item_private_message` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_item_private_message_adherent`
--

LOCK TABLES `timeline_item_private_message_adherent` WRITE;
/*!40000 ALTER TABLE `timeline_item_private_message_adherent` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_item_private_message_adherent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactional_email_template`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactional_email_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned DEFAULT NULL,
  `created_by_administrator_id` int DEFAULT NULL,
  `updated_by_administrator_id` int DEFAULT NULL,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `json_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_sync` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_65A0950AD17F50A6` (`uuid`),
  UNIQUE KEY `UNIQ_65A0950A772E836A` (`identifier`),
  KEY `IDX_65A0950A727ACA70` (`parent_id`),
  KEY `IDX_65A0950A9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_65A0950ACF1918FF` (`updated_by_administrator_id`),
  CONSTRAINT `FK_65A0950A727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `transactional_email_template` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_65A0950A9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_65A0950ACF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactional_email_template`
--

LOCK TABLES `transactional_email_template` WRITE;
/*!40000 ALTER TABLE `transactional_email_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactional_email_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unregistrations`
--

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
  `email_hash` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `is_renaissance` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
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
-- Table structure for table `uploadable_file`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uploadable_file` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `file_dimensions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_55DF92E4D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uploadable_file`
--

LOCK TABLES `uploadable_file` WRITE;
/*!40000 ALTER TABLE `uploadable_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `uploadable_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_action_history`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_action_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `impersonator_id` int DEFAULT NULL,
  `TYPE` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `data` json DEFAULT NULL,
  `telegram_notified_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_969D27F325F06C53` (`adherent_id`),
  KEY `IDX_969D27F3D1107CFF` (`impersonator_id`),
  CONSTRAINT `FK_969D27F325F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_969D27F3D1107CFF` FOREIGN KEY (`impersonator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_action_history`
--

LOCK TABLES `user_action_history` WRITE;
/*!40000 ALTER TABLE `user_action_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_action_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_authorizations`
--

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
  `author_id` int unsigned DEFAULT NULL,
  `author_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_instance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_theme` json DEFAULT NULL,
  `instance_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A250FF6CD17F50A6` (`uuid`),
  KEY `IDX_A250FF6CF675F31B` (`author_id`),
  KEY `IDX_A250FF6C8278FE91` (`instance_key`),
  CONSTRAINT `FK_A250FF6CF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
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
-- Table structure for table `voting_platform_candidate`
--

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
  `cancel_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_entity` (
  `id` int NOT NULL AUTO_INCREMENT,
  `committee_id` int unsigned DEFAULT NULL,
  `election_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7AAD259FA708DAFF` (`election_id`),
  KEY `IDX_7AAD259FED1A100B` (`committee_id`),
  CONSTRAINT `FK_7AAD259FA708DAFF` FOREIGN KEY (`election_id`) REFERENCES `voting_platform_election` (`id`) ON DELETE CASCADE,
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_pool` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned DEFAULT NULL,
  `code` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_separator` tinyint(1) NOT NULL DEFAULT '0',
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

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_vote_result` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_round_id` int DEFAULT NULL,
  `voter_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `voted_at` datetime NOT NULL,
  `zone_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
-- Table structure for table `vox_action`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vox_action` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_insee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_additional_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `canceled_at` datetime DEFAULT NULL,
  `notified_at_first_notification` datetime DEFAULT NULL,
  `notified_at_second_notification` datetime DEFAULT NULL,
  `author_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_instance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_scope` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_theme` json DEFAULT NULL,
  `instance_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C721ED96D17F50A6` (`uuid`),
  KEY `IDX_C721ED96F675F31B` (`author_id`),
  CONSTRAINT `FK_C721ED96F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vox_action`
--

LOCK TABLES `vox_action` WRITE;
/*!40000 ALTER TABLE `vox_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `vox_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vox_action_participant`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vox_action_participant` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `action_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `is_present` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9A5816DCD17F50A6` (`uuid`),
  KEY `IDX_9A5816DC9D32F035` (`action_id`),
  KEY `IDX_9A5816DC25F06C53` (`adherent_id`),
  CONSTRAINT `FK_9A5816DC25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9A5816DC9D32F035` FOREIGN KEY (`action_id`) REFERENCES `vox_action` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vox_action_participant`
--

LOCK TABLES `vox_action_participant` WRITE;
/*!40000 ALTER TABLE `vox_action_participant` DISABLE KEYS */;
/*!40000 ALTER TABLE `vox_action_participant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vox_action_zone`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vox_action_zone` (
  `action_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  PRIMARY KEY (`action_id`,`zone_id`),
  KEY `IDX_3AA996179D32F035` (`action_id`),
  KEY `IDX_3AA996179F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_3AA996179D32F035` FOREIGN KEY (`action_id`) REFERENCES `vox_action` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3AA996179F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vox_action_zone`
--

LOCK TABLES `vox_action_zone` WRITE;
/*!40000 ALTER TABLE `vox_action_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `vox_action_zone` ENABLE KEYS */;
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
