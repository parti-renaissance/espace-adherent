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
  UNIQUE KEY `adherent_activation_token_unique` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_adherent_tag`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_adherent_tag` (
  `adherent_id` int unsigned NOT NULL,
  `adherent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`adherent_id`,`adherent_tag_id`),
  KEY `IDX_DD297F8225F06C53` (`adherent_id`),
  KEY `IDX_DD297F82AED03543` (`adherent_tag_id`),
  CONSTRAINT `FK_DD297F8225F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DD297F82AED03543` FOREIGN KEY (`adherent_tag_id`) REFERENCES `adherent_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_6F8B4B5AE7927C7477241BAC253ECC4` (`email`,`used_at`,`expired_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  CONSTRAINT `FK_D6F94F2B25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_commitment`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_email_subscribe_token`
--

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
  KEY `IDX_376DBA09DF5350C` (`created_by_administrator_id`),
  KEY `IDX_376DBA0CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_376DBA0F675F31B` (`author_id`),
  CONSTRAINT `FK_376DBA09DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_376DBA0CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_376DBA0F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_email_subscription_history_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_instance_quality`
--

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
  KEY `IDX_D63B17FA25F06C53` (`adherent_id`),
  KEY `IDX_D63B17FA9F2C3FAB` (`zone_id`),
  KEY `IDX_D63B17FAA623BBD7` (`instance_quality_id`),
  KEY `IDX_D63B17FAAAA61A99` (`territorial_council_id`),
  CONSTRAINT `FK_D63B17FA25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D63B17FA9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_D63B17FAA623BBD7` FOREIGN KEY (`instance_quality_id`) REFERENCES `instance_quality` (`id`),
  CONSTRAINT `FK_D63B17FAAAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_mandate`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_mandate` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned NOT NULL,
  `committee_id` int unsigned DEFAULT NULL,
  `territorial_council_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `begin_at` datetime NOT NULL,
  `finish_at` datetime DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quality` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_additionally_elected` tinyint(1) DEFAULT '0',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provisional` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_9C0C3D6025F06C53` (`adherent_id`),
  KEY `IDX_9C0C3D60AAA61A99` (`territorial_council_id`),
  KEY `IDX_9C0C3D60ED1A100B` (`committee_id`),
  CONSTRAINT `FK_9C0C3D6025F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9C0C3D60AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9C0C3D60ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_message_filters`
--

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
  `cause_id` int unsigned DEFAULT NULL,
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
  `interests` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `registered_since` date DEFAULT NULL,
  `registered_until` date DEFAULT NULL,
  `contact_volunteer_team` tinyint(1) DEFAULT '0',
  `contact_running_mate_team` tinyint(1) DEFAULT '0',
  `contact_only_volunteers` tinyint(1) DEFAULT '0',
  `contact_only_running_mates` tinyint(1) DEFAULT '0',
  `contact_adherents` tinyint(1) DEFAULT '0',
  `insee_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_newsletter` tinyint(1) DEFAULT '0',
  `postal_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mandate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `political_function` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qualities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `include_committee_provisional_supervisors` tinyint(1) DEFAULT NULL,
  `is_certified` tinyint(1) DEFAULT NULL,
  `scope` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_28CA9F9466E2221E` (`cause_id`),
  KEY `IDX_28CA9F949C262DB3` (`referent_tag_id`),
  KEY `IDX_28CA9F949F2C3FAB` (`zone_id`),
  KEY `IDX_28CA9F94AAA61A99` (`territorial_council_id`),
  KEY `IDX_28CA9F94C7A72` (`political_committee_id`),
  KEY `IDX_28CA9F94DB296AAD` (`segment_id`),
  KEY `IDX_28CA9F94ED1A100B` (`committee_id`),
  KEY `IDX_28CA9F94F74563E3` (`user_list_definition_id`),
  KEY `IDX_28CA9F94FAF04979` (`adherent_segment_id`),
  CONSTRAINT `FK_28CA9F9466E2221E` FOREIGN KEY (`cause_id`) REFERENCES `cause` (`id`),
  CONSTRAINT `FK_28CA9F949C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`),
  CONSTRAINT `FK_28CA9F949F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_28CA9F94AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`),
  CONSTRAINT `FK_28CA9F94C7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`),
  CONSTRAINT `FK_28CA9F94DB296AAD` FOREIGN KEY (`segment_id`) REFERENCES `audience_segment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_28CA9F94ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`),
  CONSTRAINT `FK_28CA9F94F74563E3` FOREIGN KEY (`user_list_definition_id`) REFERENCES `user_list_definition` (`id`),
  CONSTRAINT `FK_28CA9F94FAF04979` FOREIGN KEY (`adherent_segment_id`) REFERENCES `adherent_segment` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_messages`
--

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
  KEY `IDX_D187C183D395B25E` (`filter_id`),
  KEY `IDX_D187C183F675F31B` (`author_id`),
  CONSTRAINT `FK_D187C183D395B25E` FOREIGN KEY (`filter_id`) REFERENCES `adherent_message_filters` (`id`),
  CONSTRAINT `FK_D187C183F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `adherent_reset_password_token_unique` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_9DF0C7EBF675F31B` (`author_id`),
  CONSTRAINT `FK_9DF0C7EBF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherent_tags` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_tag_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherent_thematic_community`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `adherents`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adherents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `managed_area_id` int DEFAULT NULL,
  `coordinator_committee_area_id` int DEFAULT NULL,
  `media_id` bigint DEFAULT NULL,
  `procuration_managed_area_id` int DEFAULT NULL,
  `assessor_managed_area_id` int DEFAULT NULL,
  `municipal_chief_managed_area_id` int DEFAULT NULL,
  `jecoute_managed_area_id` int DEFAULT NULL,
  `senator_area_id` int DEFAULT NULL,
  `managed_district_id` int unsigned DEFAULT NULL,
  `consular_managed_area_id` int DEFAULT NULL,
  `assessor_role_id` int DEFAULT NULL,
  `municipal_manager_role_id` int DEFAULT NULL,
  `municipal_manager_supervisor_role_id` int DEFAULT NULL,
  `senatorial_candidate_managed_area_id` int DEFAULT NULL,
  `lre_area_id` int DEFAULT NULL,
  `legislative_candidate_managed_district_id` int unsigned DEFAULT NULL,
  `candidate_managed_area_id` int unsigned DEFAULT NULL,
  `coalition_moderator_role_id` int unsigned DEFAULT NULL,
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
  `local_host_emails_subscription` tinyint(1) NOT NULL DEFAULT '0',
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
  `com_mobile` tinyint(1) DEFAULT NULL,
  `adherent` tinyint(1) NOT NULL DEFAULT '0',
  `emails_subscriptions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `remind_sent` tinyint(1) NOT NULL DEFAULT '0',
  `mandates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `address_region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname_used` tinyint(1) NOT NULL DEFAULT '0',
  `comments_cgu_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `facebook_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_page_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_media` tinyint(1) NOT NULL,
  `nationality` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_gender` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canary_tester` tinyint(1) NOT NULL DEFAULT '0',
  `email_unsubscribed` tinyint(1) NOT NULL DEFAULT '0',
  `email_unsubscribed_at` datetime DEFAULT NULL,
  `print_privilege` tinyint(1) NOT NULL DEFAULT '0',
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
  `coalition_subscription` tinyint(1) NOT NULL DEFAULT '0',
  `cause_subscription` tinyint(1) NOT NULL DEFAULT '0',
  `coalitions_cgu_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `vote_inspector` tinyint(1) NOT NULL DEFAULT '0',
  `national_role` tinyint(1) NOT NULL DEFAULT '0',
  `mailchimp_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phoning_manager_role` tinyint(1) NOT NULL DEFAULT '0',
  `pap_national_manager_role` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherents_email_address_unique` (`email_address`),
  UNIQUE KEY `adherents_uuid_unique` (`uuid`),
  UNIQUE KEY `UNIQ_562C7DA393494FA8` (`senator_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3FCCAF6D5` (`senatorial_candidate_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3E4A5D7A5` (`assessor_role_id`),
  UNIQUE KEY `UNIQ_562C7DA3E1B55931` (`assessor_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3DC184E71` (`managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3CC72679B` (`municipal_chief_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3A188FE64` (`nickname`),
  UNIQUE KEY `UNIQ_562C7DA3A132C3C5` (`managed_district_id`),
  UNIQUE KEY `UNIQ_562C7DA39BF75CAD` (`legislative_candidate_managed_district_id`),
  UNIQUE KEY `UNIQ_562C7DA39801977F` (`municipal_manager_supervisor_role_id`),
  UNIQUE KEY `UNIQ_562C7DA394E3BB99` (`jecoute_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA38828ED30` (`coalition_moderator_role_id`),
  UNIQUE KEY `UNIQ_562C7DA379DE69AA` (`municipal_manager_role_id`),
  UNIQUE KEY `UNIQ_562C7DA379645AD5` (`lre_area_id`),
  UNIQUE KEY `UNIQ_562C7DA37657F304` (`candidate_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA339054338` (`procuration_managed_area_id`),
  UNIQUE KEY `UNIQ_562C7DA31A912B27` (`coordinator_committee_area_id`),
  UNIQUE KEY `UNIQ_562C7DA3122E5FF4` (`consular_managed_area_id`),
  KEY `IDX_562C7DA3EA9FDD75` (`media_id`),
  CONSTRAINT `FK_562C7DA3122E5FF4` FOREIGN KEY (`consular_managed_area_id`) REFERENCES `consular_managed_area` (`id`),
  CONSTRAINT `FK_562C7DA31A912B27` FOREIGN KEY (`coordinator_committee_area_id`) REFERENCES `coordinator_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA339054338` FOREIGN KEY (`procuration_managed_area_id`) REFERENCES `procuration_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA37657F304` FOREIGN KEY (`candidate_managed_area_id`) REFERENCES `candidate_managed_area` (`id`),
  CONSTRAINT `FK_562C7DA379645AD5` FOREIGN KEY (`lre_area_id`) REFERENCES `lre_area` (`id`),
  CONSTRAINT `FK_562C7DA379DE69AA` FOREIGN KEY (`municipal_manager_role_id`) REFERENCES `municipal_manager_role_association` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA38828ED30` FOREIGN KEY (`coalition_moderator_role_id`) REFERENCES `coalition_moderator_role_association` (`id`),
  CONSTRAINT `FK_562C7DA393494FA8` FOREIGN KEY (`senator_area_id`) REFERENCES `senator_area` (`id`),
  CONSTRAINT `FK_562C7DA394E3BB99` FOREIGN KEY (`jecoute_managed_area_id`) REFERENCES `jecoute_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA39801977F` FOREIGN KEY (`municipal_manager_supervisor_role_id`) REFERENCES `municipal_manager_supervisor_role` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA39BF75CAD` FOREIGN KEY (`legislative_candidate_managed_district_id`) REFERENCES `districts` (`id`),
  CONSTRAINT `FK_562C7DA3A132C3C5` FOREIGN KEY (`managed_district_id`) REFERENCES `districts` (`id`),
  CONSTRAINT `FK_562C7DA3CC72679B` FOREIGN KEY (`municipal_chief_managed_area_id`) REFERENCES `municipal_chief_areas` (`id`),
  CONSTRAINT `FK_562C7DA3DC184E71` FOREIGN KEY (`managed_area_id`) REFERENCES `referent_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA3E1B55931` FOREIGN KEY (`assessor_managed_area_id`) REFERENCES `assessor_managed_areas` (`id`),
  CONSTRAINT `FK_562C7DA3E4A5D7A5` FOREIGN KEY (`assessor_role_id`) REFERENCES `assessor_role_association` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_562C7DA3EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`),
  CONSTRAINT `FK_562C7DA3FCCAF6D5` FOREIGN KEY (`senatorial_candidate_managed_area_id`) REFERENCES `senatorial_candidate_areas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `administrator_export_history`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrator_export_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `administrator_id` int NOT NULL,
  `route_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `exported_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_10499F014B09E92C` (`administrator_id`),
  CONSTRAINT `FK_10499F014B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
  `activated` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `administrators_email_address_unique` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `algolia_candidature`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `algolia_candidature` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `application_request_running_mate`
--

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
  KEY `IDX_D1D6095625F06C53` (`adherent_id`),
  CONSTRAINT `FK_D1D6095625F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `application_request_tag`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_request_tag` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `application_request_technical_skill`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_request_technical_skill` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `application_request_theme`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_request_theme` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `application_request_volunteer`
--

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
  KEY `IDX_1139657025F06C53` (`adherent_id`),
  CONSTRAINT `FK_1139657025F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_proposal_theme`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `articles`
--

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BFDD3168989D9B62` (`slug`),
  KEY `IDX_BFDD316812469DE2` (`category_id`),
  KEY `IDX_BFDD3168EA9FDD75` (`media_id`),
  CONSTRAINT `FK_BFDD316812469DE2` FOREIGN KEY (`category_id`) REFERENCES `articles_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BFDD3168EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `articles_categories`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assessor_managed_areas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessor_managed_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assessor_requests`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessor_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vote_place_id` int DEFAULT NULL,
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date NOT NULL,
  `birth_city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vote_city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `office_number` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  PRIMARY KEY (`id`),
  KEY `IDX_26BC800F3F90B30` (`vote_place_id`),
  CONSTRAINT `FK_26BC800F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `vote_place` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assessor_requests_vote_place_wishes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessor_requests_vote_place_wishes` (
  `assessor_request_id` int unsigned NOT NULL,
  `vote_place_id` int NOT NULL,
  PRIMARY KEY (`assessor_request_id`,`vote_place_id`),
  KEY `IDX_1517FC131BD1903D` (`assessor_request_id`),
  KEY `IDX_1517FC13F3F90B30` (`vote_place_id`),
  CONSTRAINT `FK_1517FC131BD1903D` FOREIGN KEY (`assessor_request_id`) REFERENCES `assessor_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1517FC13F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `vote_place` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assessor_role_association`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessor_role_association` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vote_place_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B93395C2F3F90B30` (`vote_place_id`),
  CONSTRAINT `FK_B93395C2F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `vote_place` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_FDCD94189F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_FDCD94189F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_C5C2F52FF675F31B` (`author_id`),
  CONSTRAINT `FK_C5C2F52FD395B25E` FOREIGN KEY (`filter_id`) REFERENCES `adherent_message_filters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C5C2F52FF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_BA99FEBB9F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_BA99FEBB9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `banned_adherent`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banned_adherent` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `biography_executive_office_member`
--

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `executive_office_member_slug_unique` (`slug`),
  UNIQUE KEY `executive_office_member_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `board_member`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `board_member` (
  `id` int NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `area` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DCFABEDF25F06C53` (`adherent_id`),
  CONSTRAINT `FK_DCFABEDF25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `board_member_roles`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cause`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cause` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `coalition_id` int unsigned NOT NULL,
  `author_id` int unsigned DEFAULT NULL,
  `second_coalition_id` int unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `followers_count` int unsigned NOT NULL,
  `mailchimp_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cause_name_unique` (`name`),
  UNIQUE KEY `cause_uuid_unique` (`uuid`),
  KEY `IDX_F0DA7FBF38C2B2DC` (`second_coalition_id`),
  KEY `IDX_F0DA7FBFC2A46A23` (`coalition_id`),
  KEY `IDX_F0DA7FBFF675F31B` (`author_id`),
  CONSTRAINT `FK_F0DA7FBF38C2B2DC` FOREIGN KEY (`second_coalition_id`) REFERENCES `coalition` (`id`),
  CONSTRAINT `FK_F0DA7FBFC2A46A23` FOREIGN KEY (`coalition_id`) REFERENCES `coalition` (`id`),
  CONSTRAINT `FK_F0DA7FBFF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cause_follower`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cause_follower` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cause_id` int unsigned NOT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cgu_accepted` tinyint(1) DEFAULT NULL,
  `cause_subscription` tinyint(1) DEFAULT NULL,
  `coalition_subscription` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cause_follower_uuid_unique` (`uuid`),
  UNIQUE KEY `cause_follower_unique` (`cause_id`,`adherent_id`),
  KEY `IDX_6F9A854425F06C53` (`adherent_id`),
  KEY `IDX_6F9A854466E2221E` (`cause_id`),
  KEY `IDX_6F9A85449F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_6F9A854425F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_6F9A854466E2221E` FOREIGN KEY (`cause_id`) REFERENCES `cause` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6F9A85449F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cause_quick_action`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cause_quick_action` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cause_id` int unsigned NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DC1B329B66E2221E` (`cause_id`),
  CONSTRAINT `FK_DC1B329B66E2221E` FOREIGN KEY (`cause_id`) REFERENCES `cause` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_6E7481A925F06C53` (`adherent_id`),
  KEY `IDX_6E7481A92FFD4FD3` (`processed_by_id`),
  KEY `IDX_6E7481A96EA98020` (`found_duplicated_adherent_id`),
  CONSTRAINT `FK_6E7481A925F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6E7481A92FFD4FD3` FOREIGN KEY (`processed_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_6E7481A96EA98020` FOREIGN KEY (`found_duplicated_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chez_vous_cities`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chez_vous_cities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_codes` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `insee_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` float(10,6) NOT NULL COMMENT '(DC2Type:geo_point)',
  `longitude` float(10,6) NOT NULL COMMENT '(DC2Type:geo_point)',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A42D9BED15A3C1BC` (`insee_code`),
  UNIQUE KEY `UNIQ_A42D9BED989D9B62` (`slug`),
  KEY `IDX_A42D9BEDAE80F5DF` (`department_id`),
  CONSTRAINT `FK_A42D9BEDAE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `chez_vous_departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chez_vous_departments`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chez_vous_markers`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chez_vous_measure_types`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chez_vous_measures`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chez_vous_measures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int unsigned NOT NULL,
  `type_id` int unsigned NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `chez_vous_measures_city_type_unique` (`city_id`,`type_id`),
  KEY `IDX_E6E8973E8BAC62AF` (`city_id`),
  KEY `IDX_E6E8973EC54C8C93` (`type_id`),
  CONSTRAINT `FK_E6E8973E8BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `chez_vous_cities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E6E8973EC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `chez_vous_measure_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chez_vous_regions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chez_vous_regions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_A6C12FCC77153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cities`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clarifications`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coalition`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coalition` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube_id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coalition_name_unique` (`name`),
  UNIQUE KEY `coalition_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coalition_follower`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coalition_follower` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `coalition_id` int unsigned NOT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coalition_follower_uuid_unique` (`uuid`),
  KEY `IDX_DFF370E225F06C53` (`adherent_id`),
  KEY `IDX_DFF370E2C2A46A23` (`coalition_id`),
  CONSTRAINT `FK_DFF370E225F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DFF370E2C2A46A23` FOREIGN KEY (`coalition_id`) REFERENCES `coalition` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coalition_moderator_role_association`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coalition_moderator_role_association` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_candidacies_group`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_candidacy`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_candidacy` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  CONSTRAINT `FK_9A04454FC1537C1` FOREIGN KEY (`candidacies_group_id`) REFERENCES `committee_candidacies_group` (`id`),
  CONSTRAINT `FK_9A04454FCC6DA91` FOREIGN KEY (`committee_membership_id`) REFERENCES `committees_memberships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_candidacy_invitation`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee_candidacy_invitation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `membership_id` int unsigned NOT NULL,
  `candidacy_id` int NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `declined_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_368B01611FB354CD` (`membership_id`),
  KEY `IDX_368B016159B22434` (`candidacy_id`),
  CONSTRAINT `FK_368B01611FB354CD` FOREIGN KEY (`membership_id`) REFERENCES `committees_memberships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_368B016159B22434` FOREIGN KEY (`candidacy_id`) REFERENCES `committee_candidacy` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_2CA406E5ED1A100B` (`committee_id`),
  KEY `IDX_2CA406E5FAC7D83F` (`designation_id`),
  CONSTRAINT `FK_2CA406E5ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2CA406E5FAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_feed_item`
--

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
  KEY `IDX_4F1CDC8071F7E88B` (`event_id`),
  KEY `IDX_4F1CDC80ED1A100B` (`committee_id`),
  KEY `IDX_4F1CDC80F675F31B` (`author_id`),
  CONSTRAINT `FK_4F1CDC8071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4F1CDC80ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`),
  CONSTRAINT `FK_4F1CDC80F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_feed_item_user_documents`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_membership_history_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_merge_histories`
--

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
  CONSTRAINT `FK_BB95FBBC3BF0CCB3` FOREIGN KEY (`source_committee_id`) REFERENCES `committees` (`id`),
  CONSTRAINT `FK_BB95FBBC50FA8329` FOREIGN KEY (`merged_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BB95FBBC5C34CBC4` FOREIGN KEY (`destination_committee_id`) REFERENCES `committees` (`id`),
  CONSTRAINT `FK_BB95FBBCA8E1562` FOREIGN KEY (`reverted_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_merge_histories_merged_memberships`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_provisional_supervisor`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `committee_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `members_count` smallint unsigned NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `address_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `committee_canonical_name_unique` (`canonical_name`),
  UNIQUE KEY `committee_slug_unique` (`slug`),
  UNIQUE KEY `committee_uuid_unique` (`uuid`),
  KEY `committee_status_idx` (`status`),
  KEY `IDX_A36198C6B4D2A5D1` (`current_designation_id`),
  CONSTRAINT `FK_A36198C6B4D2A5D1` FOREIGN KEY (`current_designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  CONSTRAINT `FK_4BBAE2C7ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `adherent_has_joined_committee` (`adherent_id`,`committee_id`),
  UNIQUE KEY `adherent_votes_in_committee` (`adherent_id`,`enable_vote`),
  KEY `committees_memberships_role_idx` (`privilege`),
  KEY `IDX_E7A6490E25F06C53` (`adherent_id`),
  KEY `IDX_E7A6490EED1A100B` (`committee_id`),
  CONSTRAINT `FK_E7A6490E25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_E7A6490EED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consular_district`
--

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
  UNIQUE KEY `consular_district_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consular_managed_area`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consular_managed_area` (
  `id` int NOT NULL AUTO_INCREMENT,
  `consular_district_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7937A51292CA96FD` (`consular_district_id`),
  CONSTRAINT `FK_7937A51292CA96FD` FOREIGN KEY (`consular_district_id`) REFERENCES `consular_district` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `department`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deputy_managed_users_message`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deputy_managed_users_message` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `district_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `offset` bigint NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5AC419DD25F06C53` (`adherent_id`),
  KEY `IDX_5AC419DDB08FA272` (`district_id`),
  CONSTRAINT `FK_5AC419DD25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5AC419DDB08FA272` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `designation`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `candidacy_start_date` datetime NOT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `designation_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `devices_device_uuid_unique` (`device_uuid`),
  UNIQUE KEY `devices_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `districts`
--

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
  UNIQUE KEY `district_code_unique` (`code`),
  UNIQUE KEY `district_department_code_number` (`department_code`,`number`),
  UNIQUE KEY `UNIQ_68E318DC80E32C3E` (`geo_data_id`),
  UNIQUE KEY `district_referent_tag_unique` (`referent_tag_id`),
  CONSTRAINT `FK_68E318DC80E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`),
  CONSTRAINT `FK_68E318DC9C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `donation_tag_label_unique` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `paybox_payload` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `paybox_date_time` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `paybox_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paybox_subscription_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_89D6D36B5A4036C7` (`paybox_transaction_id`),
  KEY `donation_transactions_result_idx` (`paybox_result_code`),
  KEY `IDX_89D6D36B4DC1279C` (`donation_id`),
  CONSTRAINT `FK_89D6D36B4DC1279C` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `donation_duration_idx` (`duration`),
  KEY `donation_status_idx` (`status`),
  KEY `donation_uuid_idx` (`uuid`),
  KEY `IDX_CDE98962831BACAF` (`donator_id`),
  CONSTRAINT `FK_CDE98962831BACAF` FOREIGN KEY (`donator_id`) REFERENCES `donators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `donator_identifier`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donator_identifier` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `donator_tag_label_unique` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `donator_identifier_unique` (`identifier`),
  UNIQUE KEY `UNIQ_A902FDD7ABF665A8` (`reference_donation_id`),
  UNIQUE KEY `UNIQ_A902FDD7DE59CB1A` (`last_successful_donation_id`),
  KEY `IDX_A902FDD725F06C53` (`adherent_id`),
  KEY `IDX_A902FDD7B08E074EA9D1C132C808BA5A` (`email_address`,`first_name`,`last_name`),
  CONSTRAINT `FK_A902FDD725F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A902FDD7ABF665A8` FOREIGN KEY (`reference_donation_id`) REFERENCES `donations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A902FDD7DE59CB1A` FOREIGN KEY (`last_successful_donation_id`) REFERENCES `donations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BF51F0FD25F06C53` (`adherent_id`),
  CONSTRAINT `FK_BF51F0FD25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative_label`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_label` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int NOT NULL,
  `on_going` tinyint(1) NOT NULL DEFAULT '1',
  `begin_year` int DEFAULT NULL,
  `finish_year` int DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D8143704D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_D8143704D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative_mandate`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_mandate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int NOT NULL,
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
  PRIMARY KEY (`id`),
  KEY `IDX_38609146283AB2A9` (`geo_zone_id`),
  KEY `IDX_386091469F2C3FAB` (`zone_id`),
  KEY `IDX_38609146D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_38609146283AB2A9` FOREIGN KEY (`geo_zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_386091469F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `elected_representative_zone` (`id`),
  CONSTRAINT `FK_38609146D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative_political_function`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_political_function` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int NOT NULL,
  `mandate_id` int NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative_social_network_link`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_social_network_link` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `social_network_elected_representative_unique` (`type`,`elected_representative_id`),
  KEY `IDX_231377B5D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_231377B5D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative_sponsorship`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_sponsorship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int NOT NULL,
  `presidential_election_year` int NOT NULL,
  `candidate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CA6D486D38DA5D3` (`elected_representative_id`),
  CONSTRAINT `FK_CA6D486D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative_user_list_definition`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_user_list_definition` (
  `elected_representative_id` int NOT NULL,
  `user_list_definition_id` int unsigned NOT NULL,
  PRIMARY KEY (`elected_representative_id`,`user_list_definition_id`),
  KEY `IDX_A9C53A24D38DA5D3` (`elected_representative_id`),
  KEY `IDX_A9C53A24F74563E3` (`user_list_definition_id`),
  CONSTRAINT `FK_A9C53A24D38DA5D3` FOREIGN KEY (`elected_representative_id`) REFERENCES `elected_representative` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_A9C53A24F74563E3` FOREIGN KEY (`user_list_definition_id`) REFERENCES `user_list_definition` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative_user_list_definition_history`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_user_list_definition_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `elected_representative_id` int NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative_zone_category`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `elected_representative_zone_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `elected_representative_zone_category_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `elected_representative_zone_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_city_candidate`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_city_card`
--

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
  UNIQUE KEY `city_card_city_unique` (`city_id`),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_city_contact`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_city_manager`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `election_city_manager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:phone_number)',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_city_partner`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `election_city_prevision`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_templates`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_template_uuid_unique` (`uuid`),
  KEY `IDX_6023E2A5F675F31B` (`author_id`),
  CONSTRAINT `FK_6023E2A5F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `epci`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_group_category_name_unique` (`name`),
  UNIQUE KEY `event_group_category_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_user_documents`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_zone`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `organizer_id` int unsigned DEFAULT NULL,
  `committee_id` int unsigned DEFAULT NULL,
  `coalition_id` int unsigned DEFAULT NULL,
  `cause_id` int unsigned DEFAULT NULL,
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
  `address_postal_code` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `invitations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `address_geocodable_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visio_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminded` tinyint(1) NOT NULL DEFAULT '0',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `electoral` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_slug_unique` (`slug`),
  UNIQUE KEY `event_uuid_unique` (`uuid`),
  KEY `IDX_5387574A12469DE2` (`category_id`),
  KEY `IDX_5387574A3826374D` (`begin_at`),
  KEY `IDX_5387574A66E2221E` (`cause_id`),
  KEY `IDX_5387574A7B00651C` (`status`),
  KEY `IDX_5387574A876C4DDA` (`organizer_id`),
  KEY `IDX_5387574AC2A46A23` (`coalition_id`),
  KEY `IDX_5387574AED1A100B` (`committee_id`),
  KEY `IDX_5387574AFE28FD87` (`finish_at`),
  CONSTRAINT `FK_5387574A66E2221E` FOREIGN KEY (`cause_id`) REFERENCES `cause` (`id`),
  CONSTRAINT `FK_5387574A876C4DDA` FOREIGN KEY (`organizer_id`) REFERENCES `adherents` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `FK_5387574AC2A46A23` FOREIGN KEY (`coalition_id`) REFERENCES `coalition` (`id`),
  CONSTRAINT `FK_5387574AED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_category_name_unique` (`name`),
  UNIQUE KEY `event_category_slug_unique` (`slug`),
  KEY `IDX_EF0AF3E9A267D842` (`event_group_category_id`),
  CONSTRAINT `FK_EF0AF3E9A267D842` FOREIGN KEY (`event_group_category_id`) REFERENCES `event_group_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_B94D5AAD71F7E88B` (`event_id`),
  CONSTRAINT `FK_B94D5AAD71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events_registrations`
--

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
  PRIMARY KEY (`id`),
  KEY `event_registration_adherent_uuid_idx` (`adherent_uuid`),
  KEY `event_registration_email_address_idx` (`email_address`),
  KEY `IDX_EEFA30C071F7E88B` (`event_id`),
  CONSTRAINT `FK_EEFA30C071F7E88B` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `age_range` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `created_at` datetime NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `access_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_auto_uploaded` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facebook_profile_facebook_id` (`facebook_id`),
  UNIQUE KEY `facebook_profile_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `filesystem_file_slug_unique` (`slug`),
  KEY `IDX_47F0AE285E237E06` (`name`),
  KEY `IDX_47F0AE28727ACA70` (`parent_id`),
  KEY `IDX_47F0AE28896DBBDE` (`updated_by_id`),
  KEY `IDX_47F0AE288CDE5729` (`type`),
  KEY `IDX_47F0AE28B03A8386` (`created_by_id`),
  CONSTRAINT `FK_47F0AE28727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `filesystem_file` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_47F0AE28896DBBDE` FOREIGN KEY (`updated_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_47F0AE28B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `position` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7E652CB6989D9B62` (`slug`),
  KEY `IDX_7E652CB6D96C566B` (`path_id`),
  KEY `IDX_7E652CB6EA9FDD75` (`media_id`),
  CONSTRAINT `FK_7E652CB6D96C566B` FOREIGN KEY (`path_id`) REFERENCES `formation_paths` (`id`),
  CONSTRAINT `FK_7E652CB6EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `position` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_6B4806AC2B36786B` (`title`),
  UNIQUE KEY `UNIQ_6B4806AC989D9B62` (`slug`),
  KEY `IDX_6B4806AC2E30CD41` (`axe_id`),
  KEY `IDX_6B4806ACEA9FDD75` (`media_id`),
  CONSTRAINT `FK_6B4806AC2E30CD41` FOREIGN KEY (`axe_id`) REFERENCES `formation_axes` (`id`),
  CONSTRAINT `FK_6B4806ACEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `position` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FD311864989D9B62` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `geo_zone_code_type_unique` (`code`,`type`),
  UNIQUE KEY `UNIQ_A4CCEF0780E32C3E` (`geo_data_id`),
  KEY `geo_zone_type_idx` (`type`),
  CONSTRAINT `FK_A4CCEF0780E32C3E` FOREIGN KEY (`geo_data_id`) REFERENCES `geo_data` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `home_blocks`
--

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3EE9FCC5462CE4F5` (`position`),
  UNIQUE KEY `UNIQ_3EE9FCC54DBB5058` (`position_name`),
  KEY `IDX_3EE9FCC5EA9FDD75` (`media_id`),
  CONSTRAINT `FK_3EE9FCC5EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `instance_quality`
--

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
  UNIQUE KEY `UNIQ_BB26C6D377153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutional_events_categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutional_events_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ENABLED',
  PRIMARY KEY (`id`),
  UNIQUE KEY `institutional_event_category_name_unique` (`name`),
  UNIQUE KEY `institutional_event_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interactive_choices`
--

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
  UNIQUE KEY `interactive_choices_content_key_unique` (`content_key`),
  UNIQUE KEY `interactive_choices_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interactive_invitation_has_choices`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interactive_invitations`
--

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
  UNIQUE KEY `interactive_invitations_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `internal_application_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `je_marche_reports`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jecoute_choice`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_choice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int DEFAULT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_80BD898B1E27F6BF` (`question_id`),
  CONSTRAINT `FK_80BD898B1E27F6BF` FOREIGN KEY (`question_id`) REFERENCES `jecoute_question` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `IDX_6579E8E7B3FE509D` (`survey_id`),
  KEY `IDX_6579E8E7F675F31B` (`author_id`),
  CONSTRAINT `FK_6579E8E7B3FE509D` FOREIGN KEY (`survey_id`) REFERENCES `jecoute_survey` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6579E8E7F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jecoute_news`
--

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `jecoute_news_uuid_unique` (`uuid`),
  KEY `IDX_34362099F2C3FAB` (`zone_id`),
  KEY `IDX_3436209B03A8386` (`created_by_id`),
  KEY `IDX_3436209F675F31B` (`author_id`),
  CONSTRAINT `FK_34362099F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_3436209B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_3436209F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_4E74226F4B09E92C` (`administrator_id`),
  KEY `IDX_4E74226FF675F31B` (`author_id`),
  CONSTRAINT `FK_4E74226F4B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_4E74226F9F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_4E74226FF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `open_graph` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  PRIMARY KEY (`id`),
  KEY `IDX_17E1064BB03A8386` (`created_by_id`),
  KEY `IDX_17E1064BF675F31B` (`author_id`),
  CONSTRAINT `FK_17E1064BB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_17E1064BF675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jecoute_survey`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_survey` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned DEFAULT NULL,
  `administrator_id` int DEFAULT NULL,
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
  PRIMARY KEY (`id`),
  KEY `IDX_EC4948E54B09E92C` (`administrator_id`),
  KEY `IDX_EC4948E59F2C3FAB` (`zone_id`),
  KEY `IDX_EC4948E5F675F31B` (`author_id`),
  CONSTRAINT `FK_EC4948E54B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EC4948E59F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_EC4948E5F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jecoute_survey_question`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jecoute_survey_question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `survey_id` int unsigned DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `position` smallint NOT NULL,
  `from_suggested_question` int DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  KEY `IDX_A2FBFA811E27F6BF` (`question_id`),
  KEY `IDX_A2FBFA81B3FE509D` (`survey_id`),
  CONSTRAINT `FK_A2FBFA811E27F6BF` FOREIGN KEY (`question_id`) REFERENCES `jecoute_question` (`id`),
  CONSTRAINT `FK_A2FBFA81B3FE509D` FOREIGN KEY (`survey_id`) REFERENCES `jecoute_survey` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `UNIQ_8DF5D8183C5110AB` (`data_survey_id`),
  KEY `IDX_8DF5D81894A4C7D4` (`device_id`),
  CONSTRAINT `FK_8DF5D8183C5110AB` FOREIGN KEY (`data_survey_id`) REFERENCES `jecoute_data_survey` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8DF5D81894A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `legislative_candidates`
--

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
  UNIQUE KEY `legislative_candidates_slug_unique` (`slug`),
  KEY `IDX_AE55AF9B23F5C396` (`district_zone_id`),
  KEY `IDX_AE55AF9BEA9FDD75` (`media_id`),
  CONSTRAINT `FK_AE55AF9B23F5C396` FOREIGN KEY (`district_zone_id`) REFERENCES `legislative_district_zones` (`id`),
  CONSTRAINT `FK_AE55AF9BEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `legislative_district_zones_area_code_unique` (`area_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_total_result`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `live_links`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `live_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position` smallint NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lre_area`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lre_area` (
  `id` int NOT NULL AUTO_INCREMENT,
  `referent_tag_id` int unsigned DEFAULT NULL,
  `all_tags` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_8D3B8F189C262DB3` (`referent_tag_id`),
  CONSTRAINT `FK_8D3B8F189C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CFABD3094BD2A4C0` (`report_id`),
  KEY `IDX_CFABD309537A1329` (`message_id`),
  CONSTRAINT `FK_CFABD3094BD2A4C0` FOREIGN KEY (`report_id`) REFERENCES `mailchimp_campaign_report` (`id`),
  CONSTRAINT `FK_CFABD309537A1329` FOREIGN KEY (`message_id`) REFERENCES `adherent_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailchimp_campaign_report`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailchimp_segment`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mailchimp_segment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `list` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ministry_list_total_result`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ministry_vote_result`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `mooc_slug` (`slug`),
  UNIQUE KEY `UNIQ_9D5D3B5543C8160D` (`list_image_id`),
  UNIQUE KEY `UNIQ_9D5D3B55684DD106` (`article_image_id`),
  CONSTRAINT `FK_9D5D3B5543C8160D` FOREIGN KEY (`list_image_id`) REFERENCES `image` (`id`),
  CONSTRAINT `FK_9D5D3B55684DD106` FOREIGN KEY (`article_image_id`) REFERENCES `image` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `position` smallint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mooc_chapter_slug` (`slug`),
  KEY `IDX_A3EDA0D1255EEB87` (`mooc_id`),
  CONSTRAINT `FK_A3EDA0D1255EEB87` FOREIGN KEY (`mooc_id`) REFERENCES `mooc` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `position` smallint NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `municipal_chief_areas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `municipal_chief_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jecoute_access` tinyint(1) NOT NULL DEFAULT '0',
  `insee_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `municipal_manager_role_association`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `municipal_manager_role_association` (
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `municipal_manager_role_association_cities`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `municipal_manager_role_association_cities` (
  `municipal_manager_role_association_id` int NOT NULL,
  `city_id` int unsigned NOT NULL,
  PRIMARY KEY (`municipal_manager_role_association_id`,`city_id`),
  UNIQUE KEY `UNIQ_A713D9C28BAC62AF` (`city_id`),
  KEY `IDX_A713D9C2D96891C` (`municipal_manager_role_association_id`),
  CONSTRAINT `FK_A713D9C28BAC62AF` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  CONSTRAINT `FK_A713D9C2D96891C` FOREIGN KEY (`municipal_manager_role_association_id`) REFERENCES `municipal_manager_role_association` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `municipal_manager_supervisor_role`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `municipal_manager_supervisor_role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `referent_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F304FF35E47E35` (`referent_id`),
  CONSTRAINT `FK_F304FF35E47E35` FOREIGN KEY (`referent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `my_team_delegate_access_committee`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `my_team_delegated_access`
--

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
  PRIMARY KEY (`id`),
  KEY `IDX_421C13B98825BEFA` (`delegator_id`),
  KEY `IDX_421C13B9B7E7AE18` (`delegated_id`),
  CONSTRAINT `FK_421C13B98825BEFA` FOREIGN KEY (`delegator_id`) REFERENCES `adherents` (`id`),
  CONSTRAINT `FK_421C13B9B7E7AE18` FOREIGN KEY (`delegated_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `national_council_candidacies_group`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_council_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `national_council_candidacy`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_council_candidacy` (
  `id` int NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `national_council_election`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `national_council_election` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `designation_id` int unsigned DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  KEY `IDX_F3809347FAC7D83F` (`designation_id`),
  CONSTRAINT `FK_F3809347FAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newsletter_invitations`
--

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newsletter_subscriptions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_subscriptions` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_event` tinyint(1) NOT NULL DEFAULT '0',
  `confirmed_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  `token` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B3C13B0BE7927C74` (`email`),
  UNIQUE KEY `UNIQ_B3C13B0B5F37A13B` (`token`),
  UNIQUE KEY `UNIQ_B3C13B0BD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `oauth_access_tokens_identifier_unique` (`identifier`),
  UNIQUE KEY `oauth_access_tokens_uuid_unique` (`uuid`),
  KEY `IDX_CA42527C19EB6921` (`client_id`),
  KEY `IDX_CA42527C94A4C7D4` (`device_id`),
  KEY `IDX_CA42527CA76ED395` (`user_id`),
  CONSTRAINT `FK_CA42527C19EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`),
  CONSTRAINT `FK_CA42527C94A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CA42527CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `oauth_auth_codes_identifier_unique` (`identifier`),
  UNIQUE KEY `oauth_auth_codes_uuid_unique` (`uuid`),
  KEY `IDX_BB493F8319EB6921` (`client_id`),
  KEY `IDX_BB493F8394A4C7D4` (`device_id`),
  KEY `IDX_BB493F83A76ED395` (`user_id`),
  CONSTRAINT `FK_BB493F8319EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`),
  CONSTRAINT `FK_BB493F8394A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BB493F83A76ED395` FOREIGN KEY (`user_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `oauth_clients_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `oauth_refresh_tokens_identifier_unique` (`identifier`),
  UNIQUE KEY `oauth_refresh_tokens_uuid_unique` (`uuid`),
  KEY `IDX_5AB6872CCB2688` (`access_token_id`),
  CONSTRAINT `FK_5AB6872CCB2688` FOREIGN KEY (`access_token_id`) REFERENCES `oauth_access_tokens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_articles`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_section_order_article`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_sections`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `organizational_chart_item`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `IDX_47071E114118D12385E16F6B` (`latitude`,`longitude`),
  KEY `IDX_47071E11D17F50A6` (`uuid`),
  KEY `IDX_47071E11D8AD1DD1AFAA2D47` (`offset_x`,`offset_y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_112ABBE148ED5CAD` (`current_campaign_id`),
  CONSTRAINT `FK_112ABBE148ED5CAD` FOREIGN KEY (`current_campaign_id`) REFERENCES `pap_campaign` (`id`),
  CONSTRAINT `FK_112ABBE1F5B7AF75` FOREIGN KEY (`address_id`) REFERENCES `pap_address` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_61470C814D2A7E12` (`building_id`),
  KEY `IDX_61470C8185C9D733` (`created_by_adherent_id`),
  KEY `IDX_61470C81DF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_61470C814D2A7E12` FOREIGN KEY (`building_id`) REFERENCES `pap_building` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_61470C8185C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_61470C81DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `IDX_8B79BF6032618357` (`building_block_id`),
  KEY `IDX_8B79BF60F639F774` (`campaign_id`),
  CONSTRAINT `FK_8B79BF6032618357` FOREIGN KEY (`building_block_id`) REFERENCES `pap_building_block` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_8B79BF60F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `nb_doors` smallint unsigned NOT NULL DEFAULT '0',
  `nb_surveys` smallint unsigned NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B6FB4E7B4D2A7E12` (`building_id`),
  KEY `IDX_B6FB4E7BDCDF6621` (`last_passage_done_by_id`),
  KEY `IDX_B6FB4E7BF639F774` (`campaign_id`),
  CONSTRAINT `FK_B6FB4E7B4D2A7E12` FOREIGN KEY (`building_id`) REFERENCES `pap_building` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B6FB4E7BDCDF6621` FOREIGN KEY (`last_passage_done_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_B6FB4E7BF639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `IDX_EF50C8E84B09E92C` (`administrator_id`),
  KEY `IDX_EF50C8E8B3FE509D` (`survey_id`),
  CONSTRAINT `FK_EF50C8E84B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EF50C8E8B3FE509D` FOREIGN KEY (`survey_id`) REFERENCES `jecoute_survey` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pap_campaign_history`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pap_campaign_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `questioner_id` int unsigned DEFAULT NULL,
  `adherent_id` int unsigned DEFAULT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5A3F26F73C5110AB` (`data_survey_id`),
  KEY `IDX_5A3F26F725F06C53` (`adherent_id`),
  KEY `IDX_5A3F26F74D2A7E12` (`building_id`),
  KEY `IDX_5A3F26F7CC0DE6E1` (`questioner_id`),
  KEY `IDX_5A3F26F7F639F774` (`campaign_id`),
  CONSTRAINT `FK_5A3F26F725F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5A3F26F73C5110AB` FOREIGN KEY (`data_survey_id`) REFERENCES `jecoute_data_survey` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_5A3F26F74D2A7E12` FOREIGN KEY (`building_id`) REFERENCES `pap_building` (`id`),
  CONSTRAINT `FK_5A3F26F7CC0DE6E1` FOREIGN KEY (`questioner_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_5A3F26F7F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_633C3C6432618357` (`building_block_id`),
  KEY `IDX_633C3C6485C9D733` (`created_by_adherent_id`),
  KEY `IDX_633C3C64DF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_633C3C6432618357` FOREIGN KEY (`building_block_id`) REFERENCES `pap_building_block` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_633C3C6485C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_633C3C64DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `IDX_853B68C8854679E2` (`floor_id`),
  KEY `IDX_853B68C8F639F774` (`campaign_id`),
  CONSTRAINT `FK_853B68C8854679E2` FOREIGN KEY (`floor_id`) REFERENCES `pap_floor` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_853B68C8F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `pap_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_FBF5A013F5B7AF75` (`address_id`),
  CONSTRAINT `FK_FBF5A013F5B7AF75` FOREIGN KEY (`address_id`) REFERENCES `pap_address` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C3882BA4848CC616` (`audience_id`),
  KEY `IDX_C3882BA4296CD8AE` (`team_id`),
  KEY `IDX_C3882BA485C9D733` (`created_by_adherent_id`),
  KEY `IDX_C3882BA49DF5350C` (`created_by_administrator_id`),
  KEY `IDX_C3882BA4B3FE509D` (`survey_id`),
  KEY `IDX_C3882BA4CF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_C3882BA4DF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_C3882BA4296CD8AE` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C3882BA4848CC616` FOREIGN KEY (`audience_id`) REFERENCES `audience_snapshot` (`id`),
  CONSTRAINT `FK_C3882BA485C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C3882BA49DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C3882BA4B3FE509D` FOREIGN KEY (`survey_id`) REFERENCES `jecoute_survey` (`id`),
  CONSTRAINT `FK_C3882BA4CF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C3882BA4DF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `UNIQ_EC1911983C5110AB` (`data_survey_id`),
  KEY `IDX_EC19119825F06C53` (`adherent_id`),
  KEY `IDX_EC191198A5626C52` (`caller_id`),
  KEY `IDX_EC191198F639F774` (`campaign_id`),
  CONSTRAINT `FK_EC19119825F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_EC1911983C5110AB` FOREIGN KEY (`data_survey_id`) REFERENCES `jecoute_data_survey` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EC191198A5626C52` FOREIGN KEY (`caller_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_EC191198F639F774` FOREIGN KEY (`campaign_id`) REFERENCES `phoning_campaign` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `political_committee`
--

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
  CONSTRAINT `FK_39FAEE95AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `political_committee_feed_item`
--

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
  KEY `IDX_54369E83C7A72` (`political_committee_id`),
  KEY `IDX_54369E83F675F31B` (`author_id`),
  CONSTRAINT `FK_54369E83C7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_54369E83F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `political_committee_membership`
--

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
  KEY `IDX_FD85437BC7A72` (`political_committee_id`),
  CONSTRAINT `FK_FD85437B25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_FD85437BC7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `political_committee_quality`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `poll_uuid_unique` (`uuid`),
  KEY `IDX_84BCFA454B09E92C` (`administrator_id`),
  KEY `IDX_84BCFA459F2C3FAB` (`zone_id`),
  KEY `IDX_84BCFA45F675F31B` (`author_id`),
  CONSTRAINT `FK_84BCFA454B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_84BCFA459F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`),
  CONSTRAINT `FK_84BCFA45F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `poll_choice_uuid_unique` (`uuid`),
  KEY `IDX_2DAE19C93C947C0F` (`poll_id`),
  CONSTRAINT `FK_2DAE19C93C947C0F` FOREIGN KEY (`poll_id`) REFERENCES `poll` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procuration_managed_areas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_managed_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procuration_proxies`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_proxies` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `reliability` smallint NOT NULL,
  `disabled` tinyint(1) NOT NULL,
  `reliability_description` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proxies_count` smallint unsigned NOT NULL DEFAULT '1',
  `french_request_available` tinyint(1) NOT NULL DEFAULT '1',
  `foreign_request_available` tinyint(1) NOT NULL DEFAULT '1',
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reachable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procuration_proxies_to_election_rounds`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_proxies_to_election_rounds` (
  `procuration_proxy_id` int NOT NULL,
  `election_round_id` int NOT NULL,
  PRIMARY KEY (`procuration_proxy_id`,`election_round_id`),
  KEY `IDX_D075F5A9E15E419B` (`procuration_proxy_id`),
  KEY `IDX_D075F5A9FCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_D075F5A9E15E419B` FOREIGN KEY (`procuration_proxy_id`) REFERENCES `procuration_proxies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_D075F5A9FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `election_rounds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procuration_requests`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procuration_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `procuration_request_found_by_id` int unsigned DEFAULT NULL,
  `found_proxy_id` int DEFAULT NULL,
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
  `reason` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `processed` tinyint(1) NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `reminded` int NOT NULL,
  `request_from_france` tinyint(1) NOT NULL DEFAULT '1',
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reachable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_9769FD842F1B6663` (`found_proxy_id`),
  KEY `IDX_9769FD84888FDEEE` (`procuration_request_found_by_id`),
  CONSTRAINT `FK_9769FD842F1B6663` FOREIGN KEY (`found_proxy_id`) REFERENCES `procuration_proxies` (`id`),
  CONSTRAINT `FK_9769FD84888FDEEE` FOREIGN KEY (`procuration_request_found_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procuration_requests_to_election_rounds`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `programmatic_foundation_approach`
--

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `programmatic_foundation_measure`
--

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
  KEY `IDX_213A5F1EF0ED738A` (`sub_approach_id`),
  CONSTRAINT `FK_213A5F1EF0ED738A` FOREIGN KEY (`sub_approach_id`) REFERENCES `programmatic_foundation_sub_approach` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `programmatic_foundation_measure_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `programmatic_foundation_project`
--

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
  KEY `IDX_8E8E96D55DA37D00` (`measure_id`),
  CONSTRAINT `FK_8E8E96D55DA37D00` FOREIGN KEY (`measure_id`) REFERENCES `programmatic_foundation_measure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `programmatic_foundation_project_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `programmatic_foundation_sub_approach`
--

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
  KEY `IDX_735C1D0115140614` (`approach_id`),
  CONSTRAINT `FK_735C1D0115140614` FOREIGN KEY (`approach_id`) REFERENCES `programmatic_foundation_approach` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `programmatic_foundation_tag`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmatic_foundation_tag` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_12127927EA750E8` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projection_managed_users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projection_managed_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `status` smallint NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  PRIMARY KEY (`id`),
  KEY `IDX_90A7D656108B7592` (`original_id`),
  KEY `projection_managed_users_search` (`status`,`postal_code`,`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `push_token`
--

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
  KEY `IDX_51BC138125F06C53` (`adherent_id`),
  KEY `IDX_51BC138194A4C7D4` (`device_id`),
  CONSTRAINT `FK_51BC138125F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_51BC138194A4C7D4` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qr_code`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `qr_code` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_by_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `count` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `qr_code_name` (`name`),
  UNIQUE KEY `qr_code_uuid` (`uuid`),
  KEY `IDX_7D8B1FB5B03A8386` (`created_by_id`),
  CONSTRAINT `FK_7D8B1FB5B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `redirections`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redirections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `url_from` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` int NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent`
--

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
  UNIQUE KEY `referent_slug_unique` (`slug`),
  KEY `IDX_FE9AAC6CEA9FDD75` (`media_id`),
  CONSTRAINT `FK_FE9AAC6CEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_area`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_area` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `area_code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referent_area_area_code_unique` (`area_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_areas`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_managed_areas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_managed_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marker_latitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  `marker_longitude` float(10,6) DEFAULT NULL COMMENT '(DC2Type:geo_point)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_managed_areas_tags`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_managed_users_message`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referent_managed_users_message` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `adherent_id` int unsigned DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `include_adherents_no_committee` tinyint(1) NOT NULL DEFAULT '0',
  `include_adherents_in_committee` tinyint(1) NOT NULL DEFAULT '0',
  `include_hosts` tinyint(1) NOT NULL DEFAULT '0',
  `include_supervisors` tinyint(1) NOT NULL DEFAULT '0',
  `query_area_code` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `query_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `offset` bigint NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `interests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  `gender` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_minimum` int DEFAULT NULL,
  `age_maximum` int DEFAULT NULL,
  `registered_from` date DEFAULT NULL,
  `registered_to` date DEFAULT NULL,
  `query_zone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1E41AC6125F06C53` (`adherent_id`),
  CONSTRAINT `FK_1E41AC6125F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_person_link`
--

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
  `is_municipal_manager_supervisor` tinyint(1) NOT NULL DEFAULT '0',
  `co_referent` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `restricted_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
  PRIMARY KEY (`id`),
  KEY `IDX_BC75A60A25F06C53` (`adherent_id`),
  KEY `IDX_BC75A60A35E47E35` (`referent_id`),
  KEY `IDX_BC75A60A810B5A42` (`person_organizational_chart_item_id`),
  CONSTRAINT `FK_BC75A60A25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_BC75A60A35E47E35` FOREIGN KEY (`referent_id`) REFERENCES `referent` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BC75A60A810B5A42` FOREIGN KEY (`person_organizational_chart_item_id`) REFERENCES `organizational_chart_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_person_link_committee`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_space_access_information`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_tags`
--

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
  UNIQUE KEY `referent_tag_code_unique` (`code`),
  UNIQUE KEY `referent_tag_name_unique` (`name`),
  KEY `IDX_135D29D99F2C3FAB` (`zone_id`),
  CONSTRAINT `FK_135D29D99F2C3FAB` FOREIGN KEY (`zone_id`) REFERENCES `geo_zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_team_member`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_team_member_committee`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referent_user_filter_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `region`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `region` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_F62F17677153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `reasons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unresolved',
  `created_at` datetime NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_uuid_unique` (`uuid`),
  KEY `IDX_F11FA74583B12DAC` (`community_event_id`),
  KEY `IDX_F11FA745ED1A100B` (`committee_id`),
  KEY `IDX_F11FA745F675F31B` (`author_id`),
  KEY `report_status_idx` (`status`),
  KEY `report_type_idx` (`type`),
  CONSTRAINT `FK_F11FA74583B12DAC` FOREIGN KEY (`community_event_id`) REFERENCES `events` (`id`),
  CONSTRAINT `FK_F11FA745ED1A100B` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`),
  CONSTRAINT `FK_F11FA745F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `republican_silence_referent_tag`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `republican_silence_referent_tag` (
  `republican_silence_id` int unsigned NOT NULL,
  `referent_tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`republican_silence_id`,`referent_tag_id`),
  KEY `IDX_543DED2612359909` (`republican_silence_id`),
  KEY `IDX_543DED269C262DB3` (`referent_tag_id`),
  CONSTRAINT `FK_543DED2612359909` FOREIGN KEY (`republican_silence_id`) REFERENCES `republican_silence` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_543DED269C262DB3` FOREIGN KEY (`referent_tag_id`) REFERENCES `referent_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `board_member_role_code_unique` (`code`),
  UNIQUE KEY `board_member_role_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `running_mate_request_application_request_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `running_mate_request_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `running_mate_request_theme`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `saved_board_members`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `scope_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `senator_area`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `senator_area` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_tag_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D229BBF7AEC89CE1` (`department_tag_id`),
  CONSTRAINT `FK_D229BBF7AEC89CE1` FOREIGN KEY (`department_tag_id`) REFERENCES `referent_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `senatorial_candidate_areas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `senatorial_candidate_areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `senatorial_candidate_areas_tags`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sms_campaign`
--

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
  UNIQUE KEY `UNIQ_79E333DC848CC616` (`audience_id`),
  KEY `IDX_79E333DC4B09E92C` (`administrator_id`),
  CONSTRAINT `FK_79E333DC4B09E92C` FOREIGN KEY (`administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_79E333DC848CC616` FOREIGN KEY (`audience_id`) REFERENCES `audience_snapshot` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sms_stop_history`
--

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `social_share_categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_share_categories` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `social_shares`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_shares` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `social_share_category_id` bigint DEFAULT NULL,
  `media_id` bigint DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint unsigned NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_name_unique` (`name`),
  KEY `IDX_C4E0A61F85C9D733` (`created_by_adherent_id`),
  KEY `IDX_C4E0A61F9DF5350C` (`created_by_administrator_id`),
  KEY `IDX_C4E0A61FCF1918FF` (`updated_by_administrator_id`),
  KEY `IDX_C4E0A61FDF6CFDC9` (`updated_by_adherent_id`),
  CONSTRAINT `FK_C4E0A61F85C9D733` FOREIGN KEY (`created_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C4E0A61F9DF5350C` FOREIGN KEY (`created_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C4E0A61FCF1918FF` FOREIGN KEY (`updated_by_administrator_id`) REFERENCES `administrators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_C4E0A61FDF6CFDC9` FOREIGN KEY (`updated_by_adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_6FFBDA125F06C53` (`adherent_id`),
  KEY `IDX_6FFBDA1296CD8AE` (`team_id`),
  CONSTRAINT `FK_6FFBDA125F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6FFBDA1296CD8AE` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council`
--

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
  UNIQUE KEY `territorial_council_codes_unique` (`codes`),
  UNIQUE KEY `territorial_council_name_unique` (`name`),
  UNIQUE KEY `territorial_council_uuid_unique` (`uuid`),
  KEY `IDX_B6DCA2A5B4D2A5D1` (`current_designation_id`),
  CONSTRAINT `FK_B6DCA2A5B4D2A5D1` FOREIGN KEY (`current_designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_candidacies_group`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_candidacies_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_candidacy`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_candidacy` (
  `id` int NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_candidacy_invitation`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_candidacy_invitation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `membership_id` int unsigned NOT NULL,
  `candidacy_id` int NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `declined_at` datetime DEFAULT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DA86009A1FB354CD` (`membership_id`),
  KEY `IDX_DA86009A59B22434` (`candidacy_id`),
  CONSTRAINT `FK_DA86009A1FB354CD` FOREIGN KEY (`membership_id`) REFERENCES `territorial_council_membership` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_DA86009A59B22434` FOREIGN KEY (`candidacy_id`) REFERENCES `territorial_council_candidacy` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_convocation`
--

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
  KEY `IDX_A9919BF0AAA61A99` (`territorial_council_id`),
  KEY `IDX_A9919BF0B03A8386` (`created_by_id`),
  KEY `IDX_A9919BF0C7A72` (`political_committee_id`),
  CONSTRAINT `FK_A9919BF0AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`),
  CONSTRAINT `FK_A9919BF0B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_A9919BF0C7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_election`
--

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
  UNIQUE KEY `UNIQ_14CBC36B8649F5F1` (`election_poll_id`),
  KEY `IDX_14CBC36BAAA61A99` (`territorial_council_id`),
  KEY `IDX_14CBC36BFAC7D83F` (`designation_id`),
  CONSTRAINT `FK_14CBC36B8649F5F1` FOREIGN KEY (`election_poll_id`) REFERENCES `territorial_council_election_poll` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_14CBC36BAAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`),
  CONSTRAINT `FK_14CBC36BFAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_election_poll`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_election_poll` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_election_poll_choice`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territorial_council_election_poll_choice` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `election_poll_id` int unsigned NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  PRIMARY KEY (`id`),
  KEY `IDX_63EBCF6B8649F5F1` (`election_poll_id`),
  CONSTRAINT `FK_63EBCF6B8649F5F1` FOREIGN KEY (`election_poll_id`) REFERENCES `territorial_council_election_poll` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_election_poll_vote`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_feed_item`
--

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
  KEY `IDX_45241D62AAA61A99` (`territorial_council_id`),
  KEY `IDX_45241D62F675F31B` (`author_id`),
  CONSTRAINT `FK_45241D62AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_45241D62F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_membership`
--

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
  KEY `IDX_2A998316AAA61A99` (`territorial_council_id`),
  CONSTRAINT `FK_2A99831625F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2A998316AAA61A99` FOREIGN KEY (`territorial_council_id`) REFERENCES `territorial_council` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_membership_log`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_official_report`
--

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
  KEY `IDX_8D80D385896DBBDE` (`updated_by_id`),
  KEY `IDX_8D80D385B03A8386` (`created_by_id`),
  KEY `IDX_8D80D385C7A72` (`political_committee_id`),
  KEY `IDX_8D80D385F675F31B` (`author_id`),
  CONSTRAINT `FK_8D80D385896DBBDE` FOREIGN KEY (`updated_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8D80D385B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_8D80D385C7A72` FOREIGN KEY (`political_committee_id`) REFERENCES `political_committee` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_8D80D385F675F31B` FOREIGN KEY (`author_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_official_report_document`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_quality`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `territorial_council_zone`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thematic_community`
--

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thematic_community_contact`
--

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thematic_community_membership`
--

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
  KEY `IDX_22B6AC0525F06C53` (`adherent_id`),
  KEY `IDX_22B6AC05E7A1254A` (`contact_id`),
  KEY `IDX_22B6AC05FDA7B0BF` (`community_id`),
  CONSTRAINT `FK_22B6AC0525F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_22B6AC05E7A1254A` FOREIGN KEY (`contact_id`) REFERENCES `thematic_community_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_22B6AC05FDA7B0BF` FOREIGN KEY (`community_id`) REFERENCES `thematic_community` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thematic_community_membership_user_list_definition`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_manifesto_translations`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_manifestos`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_manifestos` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `media_id` bigint DEFAULT NULL,
  `display_media` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C6ED4403EA9FDD75` (`media_id`),
  CONSTRAINT `FK_C6ED4403EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `medias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_measure_translations`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_measures`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_measures_profiles`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_profile_translations`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_profiles`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_profiles` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_theme_translations`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_themes`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline_themes_measures`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ton_macron_choices`
--

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
  UNIQUE KEY `ton_macron_choices_content_key_unique` (`content_key`),
  UNIQUE KEY `ton_macron_choices_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ton_macron_friend_invitation_has_choices`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ton_macron_friend_invitations`
--

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
  UNIQUE KEY `ton_macron_friend_invitations_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `unregistration_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `reasons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `comment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `registered_at` datetime NOT NULL,
  `unregistered_at` datetime NOT NULL,
  `is_adherent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_F9E4AA0C5B30B80B` (`excluded_by_id`),
  CONSTRAINT `FK_F9E4AA0C5B30B80B` FOREIGN KEY (`excluded_by_id`) REFERENCES `administrators` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `user_authorizations_unique` (`user_id`,`client_id`),
  KEY `IDX_4044823019EB6921` (`client_id`),
  KEY `IDX_40448230A76ED395` (`user_id`),
  CONSTRAINT `FK_4044823019EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `FK_40448230A76ED395` FOREIGN KEY (`user_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_list_definition`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `volunteer_request_application_request_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `volunteer_request_referent_tag`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `volunteer_request_technical_skill`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `volunteer_request_theme`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vote_place`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vote_place` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `holder_office_available` tinyint(1) NOT NULL,
  `substitute_office_available` tinyint(1) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2574310677153098` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vote_result`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vote_result` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vote_place_id` int DEFAULT NULL,
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
  CONSTRAINT `FK_1F8DB349F3F90B30` FOREIGN KEY (`vote_place_id`) REFERENCES `vote_place` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1F8DB349FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `election_rounds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vote_result_list`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vote_result_list_collection`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `IDX_3F426D6D25F06C53` (`adherent_id`),
  KEY `IDX_3F426D6D5F0A9B94` (`candidate_group_id`),
  CONSTRAINT `FK_3F426D6D25F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_3F426D6D5F0A9B94` FOREIGN KEY (`candidate_group_id`) REFERENCES `voting_platform_candidate_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `IDX_2C1A353AC1E98F21` (`election_pool_id`),
  CONSTRAINT `FK_2C1A353AC1E98F21` FOREIGN KEY (`election_pool_id`) REFERENCES `voting_platform_election_pool` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_7249D5375F0A9B94` (`candidate_group_id`),
  KEY `IDX_7249D537B5BA5CC5` (`election_pool_result_id`),
  CONSTRAINT `FK_7249D5375F0A9B94` FOREIGN KEY (`candidate_group_id`) REFERENCES `voting_platform_candidate_group` (`id`),
  CONSTRAINT `FK_7249D537B5BA5CC5` FOREIGN KEY (`election_pool_result_id`) REFERENCES `voting_platform_election_pool_result` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `IDX_4E144C94FAC7D83F` (`designation_id`),
  CONSTRAINT `FK_4E144C94FAC7D83F` FOREIGN KEY (`designation_id`) REFERENCES `designation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voting_platform_election_entity`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voting_platform_election_pool`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_platform_election_pool` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_id` int unsigned DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7225D6EFA708DAFF` (`election_id`),
  CONSTRAINT `FK_7225D6EFA708DAFF` FOREIGN KEY (`election_id`) REFERENCES `voting_platform_election` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_13C1C73F8FFC0F0B` (`election_round_result_id`),
  KEY `IDX_13C1C73FC1E98F21` (`election_pool_id`),
  CONSTRAINT `FK_13C1C73F8FFC0F0B` FOREIGN KEY (`election_round_result_id`) REFERENCES `voting_platform_election_round_result` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_13C1C73FC1E98F21` FOREIGN KEY (`election_pool_id`) REFERENCES `voting_platform_election_pool` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `UNIQ_67EFA0E4A708DAFF` (`election_id`),
  CONSTRAINT `FK_67EFA0E4A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `voting_platform_election` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  KEY `IDX_F15D87B7A708DAFF` (`election_id`),
  CONSTRAINT `FK_F15D87B7A708DAFF` FOREIGN KEY (`election_id`) REFERENCES `voting_platform_election` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  UNIQUE KEY `UNIQ_F2670966FCBF5E32` (`election_round_id`),
  KEY `IDX_F267096619FCFB29` (`election_result_id`),
  CONSTRAINT `FK_F267096619FCFB29` FOREIGN KEY (`election_result_id`) REFERENCES `voting_platform_election_result` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F2670966FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `voting_platform_election_round` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`voter_key`,`election_round_id`),
  KEY `IDX_62C86890FCBF5E32` (`election_round_id`),
  CONSTRAINT `FK_62C86890FCBF5E32` FOREIGN KEY (`election_round_id`) REFERENCES `voting_platform_election_round` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AB02EC0225F06C53` (`adherent_id`),
  CONSTRAINT `FK_AB02EC0225F06C53` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `web_hooks`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `web_hooks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int unsigned NOT NULL,
  `event` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `callbacks` json NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
  `service` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `web_hook_event_client_id_unique` (`event`,`client_id`),
  UNIQUE KEY `web_hook_uuid_unique` (`uuid`),
  KEY `IDX_CDB836AD19EB6921` (`client_id`),
  CONSTRAINT `FK_CDB836AD19EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
