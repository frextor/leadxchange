/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `causer_id` bigint unsigned DEFAULT NULL,
  `event` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `activity_logs_causer_id_created_at_index` (`causer_id`,`created_at`),
  KEY `activity_logs_event_created_at_index` (`event`,`created_at`),
  KEY `activity_logs_event_index` (`event`),
  KEY `activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `activity_logs_causer_id_foreign` FOREIGN KEY (`causer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ambassador_announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ambassador_announcements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ambassador_id` bigint unsigned NOT NULL,
  `region_id` bigint unsigned DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('event_reminder','welcome','networking','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `recipients_count` int unsigned NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ambassador_announcements_ambassador_id_foreign` (`ambassador_id`),
  KEY `ambassador_announcements_region_id_foreign` (`region_id`),
  CONSTRAINT `ambassador_announcements_ambassador_id_foreign` FOREIGN KEY (`ambassador_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ambassador_announcements_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ambassador_objectives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ambassador_objectives` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ambassador_id` bigint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `target_members` int unsigned NOT NULL DEFAULT '30',
  `target_events` int unsigned NOT NULL DEFAULT '5',
  `target_leads` int unsigned NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ambassador_objectives_ambassador_id_month_year_unique` (`ambassador_id`,`month`,`year`),
  CONSTRAINT `ambassador_objectives_ambassador_id_foreign` FOREIGN KEY (`ambassador_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ambassador_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ambassador_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `biography` text COLLATE utf8mb4_unicode_ci,
  `linkedin_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` int NOT NULL DEFAULT '0',
  `national_rank` int DEFAULT NULL,
  `regional_rank` int DEFAULT NULL,
  `achievements` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ambassador_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `ambassador_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `balance_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `balance_purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `stripe_payment_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL,
  `amount_cents` int NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'eur',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requires_payment_method',
  `failure_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `balance_purchases_stripe_payment_intent_id_unique` (`stripe_payment_intent_id`),
  KEY `balance_purchases_user_id_index` (`user_id`),
  CONSTRAINT `balance_purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cities_country_id_foreign` (`country_id`),
  KEY `cities_name_index` (`name`),
  CONSTRAINT `cities_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `siret` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sector_id` bigint unsigned DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_siret_unique` (`siret`),
  KEY `companies_sector_id_foreign` (`sector_id`),
  CONSTRAINT `companies_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `connections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint unsigned NOT NULL,
  `receiver_id` bigint unsigned NOT NULL,
  `status` enum('pending','accepted','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `connections_sender_id_receiver_id_unique` (`sender_id`,`receiver_id`),
  KEY `connections_sender_id_status_index` (`sender_id`,`status`),
  KEY `connections_receiver_id_status_index` (`receiver_id`,`status`),
  CONSTRAINT `connections_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `connections_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consul_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consul_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('pending','approved','rejected','revoked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `validated_by` bigint unsigned DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consul_requests_validated_by_foreign` (`validated_by`),
  KEY `consul_requests_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `consul_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consul_requests_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user1_id` bigint unsigned NOT NULL,
  `user2_id` bigint unsigned NOT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversations_user1_id_user2_id_unique` (`user1_id`,`user2_id`),
  KEY `conversations_user2_id_foreign` (`user2_id`),
  CONSTRAINT `conversations_user1_id_foreign` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_user2_id_foreign` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `device_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` enum('ios','android','web') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_tokens_token_unique` (`token`),
  KEY `device_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `device_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('verification','password_reset','notification','marketing','test') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'notification',
  `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `mailer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'smtp',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_logs_status_created_at_index` (`status`,`created_at`),
  KEY `email_logs_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `driver` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'smtp',
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'smtp.ionos.com',
  `port` smallint unsigned NOT NULL DEFAULT '587',
  `encryption` enum('tls','ssl','none') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tls',
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci,
  `from_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_settings_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_templates_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `enterprise_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enterprise_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `license_id` bigint unsigned NOT NULL,
  `invited_by` bigint unsigned DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` enum('available','pending','active','revoked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enterprise_invitations_token_unique` (`token`),
  UNIQUE KEY `enterprise_invitations_license_id_email_unique` (`license_id`,`email`),
  KEY `enterprise_invitations_user_id_foreign` (`user_id`),
  KEY `enterprise_invitations_invited_by_foreign` (`invited_by`),
  CONSTRAINT `enterprise_invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enterprise_invitations_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `enterprise_licenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enterprise_invitations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `enterprise_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enterprise_licenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `holder_user_id` bigint unsigned NOT NULL,
  `company_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_id` bigint unsigned NOT NULL,
  `seats_total` smallint unsigned NOT NULL DEFAULT '1',
  `seats_used` smallint unsigned NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enterprise_licenses_holder_user_id_foreign` (`holder_user_id`),
  KEY `enterprise_licenses_plan_id_foreign` (`plan_id`),
  CONSTRAINT `enterprise_licenses_holder_user_id_foreign` FOREIGN KEY (`holder_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enterprise_licenses_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `enterprise_quote_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enterprise_quote_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `company_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seats_needed` smallint unsigned NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','contacted','converted','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enterprise_quote_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `enterprise_quote_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_categories_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `invited_by` bigint unsigned NOT NULL,
  `status` enum('pending','accepted','declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_invitations_event_id_user_id_unique` (`event_id`,`user_id`),
  KEY `event_invitations_user_id_foreign` (`user_id`),
  KEY `event_invitations_invited_by_foreign` (`invited_by`),
  CONSTRAINT `event_invitations_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_invitations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `event_id` bigint unsigned NOT NULL,
  `stripe_payment_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` int unsigned NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'eur',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requires_payment_method',
  `failure_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_payments_stripe_payment_intent_id_unique` (`stripe_payment_intent_id`),
  KEY `event_payments_event_id_foreign` (`event_id`),
  KEY `event_payments_user_id_event_id_status_index` (`user_id`,`event_id`,`status`),
  KEY `event_payments_stripe_payment_intent_id_index` (`stripe_payment_intent_id`),
  KEY `event_payments_status_index` (`status`),
  CONSTRAINT `event_payments_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_user` (
  `event_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` enum('attendee','organizer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'attendee',
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`,`user_id`),
  KEY `event_user_user_id_foreign` (`user_id`),
  CONSTRAINT `event_user_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('virtual','in_person','hybrid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'virtual',
  `category` enum('networking','workshop','conference','pitch','after_work','webinar','community') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `sector_id` bigint unsigned DEFAULT NULL,
  `city_id` bigint unsigned DEFAULT NULL,
  `region_id` bigint unsigned DEFAULT NULL,
  `cover_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1E8F88',
  `price` decimal(8,2) DEFAULT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_attendees` int unsigned DEFAULT NULL,
  `attendees_count` int unsigned NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `scope` enum('regional','private') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regional',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_created_by_foreign` (`created_by`),
  KEY `events_sector_id_foreign` (`sector_id`),
  KEY `events_city_id_foreign` (`city_id`),
  KEY `events_region_id_foreign` (`region_id`),
  CONSTRAINT `events_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `events_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `events_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint unsigned NOT NULL,
  `invited_by` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('pending','accepted','declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'invitation',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_invitations_group_id_user_id_unique` (`group_id`,`user_id`),
  KEY `group_invitations_invited_by_foreign` (`invited_by`),
  KEY `group_invitations_user_id_foreign` (`user_id`),
  CONSTRAINT `group_invitations_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_invitations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_post_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_post_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_post_comments_post_id_foreign` (`post_id`),
  KEY `group_post_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `group_post_comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `group_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_post_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('post','activity') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post',
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_posts_user_id_foreign` (`user_id`),
  KEY `group_posts_group_id_created_at_index` (`group_id`,`created_at`),
  CONSTRAINT `group_posts_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_user` (
  `group_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` enum('member','admin','owner') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `blocked_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `group_user_user_id_foreign` (`user_id`),
  CONSTRAINT `group_user_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sector_id` bigint unsigned DEFAULT NULL,
  `city_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `cover_color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1E8F88',
  `cover_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `members_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groups_sector_id_foreign` (`sector_id`),
  KEY `groups_created_by_foreign` (`created_by`),
  KEY `groups_city_id_foreign` (`city_id`),
  CONSTRAINT `groups_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `groups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groups_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inbox_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inbox_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kind` enum('lead-received','lead-accepted','lead-rejected','lead-converted','lead-reminder','message','network','connection','deadline') COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('action','info','message','alert') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actor_id` bigint unsigned DEFAULT NULL,
  `actor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actor_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actor_company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lead_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lead_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preview` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inbox_items_actor_id_foreign` (`actor_id`),
  KEY `inbox_items_lead_id_foreign` (`lead_id`),
  KEY `inbox_items_user_id_foreign` (`user_id`),
  CONSTRAINT `inbox_items_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inbox_items_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inbox_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `interests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `interests_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `native_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_ratings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint unsigned NOT NULL,
  `rater_id` bigint unsigned NOT NULL,
  `quality` tinyint unsigned NOT NULL,
  `relevance` tinyint unsigned NOT NULL,
  `reactivity` tinyint unsigned NOT NULL,
  `average_note` decimal(3,2) GENERATED ALWAYS AS (round((((`quality` + `relevance`) + `reactivity`) / 3.0),2)) STORED,
  `rated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lead_ratings_lead_id_rater_id_unique` (`lead_id`,`rater_id`),
  KEY `lead_ratings_rater_id_foreign` (`rater_id`),
  CONSTRAINT `lead_ratings_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lead_ratings_rater_id_foreign` FOREIGN KEY (`rater_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint unsigned NOT NULL,
  `receiver_id` bigint unsigned NOT NULL,
  `company_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deadline` date NOT NULL,
  `qualification` enum('chaud','tiede','froid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tiede',
  `lead_type` enum('MQL','SQL','SP') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('new','accepted','rejected','converted','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `accepted_at` timestamp NULL DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `rating_due_at` timestamp NULL DEFAULT NULL,
  `rating_extended_at` timestamp NULL DEFAULT NULL,
  `bonus_points` tinyint unsigned DEFAULT NULL,
  `expiry_processed` tinyint(1) NOT NULL DEFAULT '0',
  `sender_points_credited` tinyint(1) NOT NULL DEFAULT '0',
  `points_deducted` tinyint(1) NOT NULL DEFAULT '0',
  `rated_bonus_at` timestamp NULL DEFAULT NULL,
  `fraud_reported` tinyint(1) NOT NULL DEFAULT '0',
  `fraud_reason` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fraud_reported_at` timestamp NULL DEFAULT NULL,
  `reminder_15_sent_at` timestamp NULL DEFAULT NULL,
  `reminder_25_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leads_receiver_id_status_index` (`receiver_id`,`status`),
  KEY `leads_sender_id_status_index` (`sender_id`,`status`),
  KEY `leads_sector_id_foreign` (`sector_id`),
  CONSTRAINT `leads_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leads_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `markets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `markets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `receiver_id` bigint unsigned DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `client_message_id` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `media_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_ms` int unsigned DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `messages_conv_client_id_unique` (`conversation_id`,`client_message_id`),
  KEY `messages_sender_id_foreign` (`sender_id`),
  KEY `messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  KEY `messages_receiver_id_foreign` (`receiver_id`),
  CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nationalities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nationalities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nationalities_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permission_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_definitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Général',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bool',
  `null_label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_definitions_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `annual_price` decimal(8,2) DEFAULT NULL,
  `billing_period` enum('monthly','annual','free') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `stripe_product_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_price_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_annual_price_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_leads` int unsigned DEFAULT NULL COMMENT 'null = unlimited',
  `max_groups` int unsigned DEFAULT NULL COMMENT 'null = unlimited',
  `max_users` int unsigned NOT NULL DEFAULT '1',
  `initial_points` int unsigned NOT NULL DEFAULT '0',
  `features` json NOT NULL,
  `permissions` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_enterprise` tinyint(1) NOT NULL DEFAULT '0',
  `contact_cta` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `points_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `points_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `delta` int NOT NULL,
  `reason` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance_after` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `points_history_user_id_foreign` (`user_id`),
  CONSTRAINT `points_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `poll_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `poll_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` bigint unsigned NOT NULL,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `poll_options_poll_id_foreign` (`poll_id`),
  CONSTRAINT `poll_options_poll_id_foreign` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `poll_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `poll_votes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` bigint unsigned NOT NULL,
  `poll_option_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poll_votes_poll_id_user_id_unique` (`poll_id`,`user_id`),
  KEY `poll_votes_poll_option_id_foreign` (`poll_option_id`),
  KEY `poll_votes_user_id_foreign` (`user_id`),
  CONSTRAINT `poll_votes_poll_id_foreign` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_votes_poll_option_id_foreign` FOREIGN KEY (`poll_option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `polls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_multiple_choice` tinyint(1) NOT NULL DEFAULT '0',
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `polls_group_id_foreign` (`group_id`),
  KEY `polls_user_id_foreign` (`user_id`),
  CONSTRAINT `polls_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `polls_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `profile_visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profile_visitors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `profile_user_id` bigint unsigned NOT NULL,
  `visitor_id` bigint unsigned NOT NULL,
  `visit_count` int unsigned NOT NULL DEFAULT '1',
  `last_visited_at` timestamp NOT NULL,
  `is_new` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profile_visitors_profile_user_id_visitor_id_unique` (`profile_user_id`,`visitor_id`),
  KEY `profile_visitors_visitor_id_foreign` (`visitor_id`),
  KEY `profile_visitors_profile_user_id_last_visited_at_index` (`profile_user_id`,`last_visited_at`),
  CONSTRAINT `profile_visitors_profile_user_id_foreign` FOREIGN KEY (`profile_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `profile_visitors_visitor_id_foreign` FOREIGN KEY (`visitor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presentation_video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presentation_video_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presentation_video_rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `presentation_video_uploaded_at` timestamp NULL DEFAULT NULL,
  `presentation_video_reviewed_at` timestamp NULL DEFAULT NULL,
  `presentation_video_reviewed_by` bigint unsigned DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `motto` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector_ids` json DEFAULT NULL,
  `experience_level` enum('junior','mid','senior','expert') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `looking_for` json DEFAULT NULL,
  `services_offered` json DEFAULT NULL,
  `open_to_network` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `market_addressed_id` bigint unsigned DEFAULT NULL,
  `market_target_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_user_id_foreign` (`user_id`),
  KEY `profiles_presentation_video_reviewed_by_foreign` (`presentation_video_reviewed_by`),
  KEY `profiles_market_addressed_id_foreign` (`market_addressed_id`),
  KEY `profiles_market_target_id_foreign` (`market_target_id`),
  CONSTRAINT `profiles_market_addressed_id_foreign` FOREIGN KEY (`market_addressed_id`) REFERENCES `markets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `profiles_market_target_id_foreign` FOREIGN KEY (`market_target_id`) REFERENCES `markets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `profiles_presentation_video_reviewed_by_foreign` FOREIGN KEY (`presentation_video_reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rgpd_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rgpd_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `right_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `processed_by` bigint unsigned DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rgpd_requests_user_id_foreign` (`user_id`),
  KEY `rgpd_requests_processed_by_foreign` (`processed_by`),
  CONSTRAINT `rgpd_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rgpd_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sectors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sectors_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `plan_id` bigint unsigned NOT NULL,
  `stripe_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','canceled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `billing_period` enum('free','monthly','annual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `stripe_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `current_period_end` timestamp NULL DEFAULT NULL,
  `cancel_at_period_end` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_user_id_foreign` (`user_id`),
  KEY `subscriptions_plan_id_foreign` (`plan_id`),
  KEY `subscriptions_stripe_subscription_id_index` (`stripe_subscription_id`),
  CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'int',
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_interests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_interests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `interest_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_interests_user_id_interest_id_unique` (`user_id`,`interest_id`),
  KEY `user_interests_interest_id_foreign` (`interest_id`),
  CONSTRAINT `user_interests_interest_id_foreign` FOREIGN KEY (`interest_id`) REFERENCES `interests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_interests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `language_id` bigint unsigned NOT NULL,
  `level` enum('basic','intermediate','fluent','native') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fluent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_languages_user_id_language_id_unique` (`user_id`,`language_id`),
  KEY `user_languages_language_id_foreign` (`language_id`),
  CONSTRAINT `user_languages_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_languages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reporter_id` bigint unsigned NOT NULL,
  `reported_id` bigint unsigned DEFAULT NULL,
  `reason` enum('harcelement','fausses_infos','usurpation','spam','lead_fictif','concurrence_deloy','autre') COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','reviewed','dismissed','actioned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_reports_reviewed_by_foreign` (`reviewed_by`),
  KEY `user_reports_reported_id_status_index` (`reported_id`,`status`),
  KEY `user_reports_reporter_id_foreign` (`reporter_id`),
  CONSTRAINT `user_reports_reported_id_foreign` FOREIGN KEY (`reported_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_reports_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_reports_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_country_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality_id` bigint unsigned DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `city_id` bigint unsigned DEFAULT NULL,
  `region_id` bigint unsigned DEFAULT NULL,
  `onboarding_completed` tinyint(1) NOT NULL DEFAULT '0',
  `newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `cgu_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cgu_accepted_at` timestamp NULL DEFAULT NULL,
  `notifications` tinyint(1) NOT NULL DEFAULT '1',
  `role` enum('user','admin','super_admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `admin_permissions` json DEFAULT NULL,
  `points_balance` int NOT NULL DEFAULT '0',
  `points_negative_since` timestamp NULL DEFAULT NULL,
  `badge_level` enum('neutre','bronze','argent','or','platinium') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'neutre',
  `ambassador_status` enum('none','pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `consul_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consul_nominated_at` timestamp NULL DEFAULT NULL,
  `consul_nominated_by` bigint unsigned DEFAULT NULL,
  `consul_rejected_at` timestamp NULL DEFAULT NULL,
  `consul_city_id` bigint unsigned DEFAULT NULL,
  `consul_region_id` bigint unsigned DEFAULT NULL,
  `ambassador_requested_at` timestamp NULL DEFAULT NULL,
  `ambassador_reviewed_at` timestamp NULL DEFAULT NULL,
  `ambassador_reviewed_by` bigint unsigned DEFAULT NULL,
  `ambassador_city_id` bigint unsigned DEFAULT NULL,
  `ambassador_region_id` bigint unsigned DEFAULT NULL,
  `ambassador_rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  KEY `users_company_id_foreign` (`company_id`),
  KEY `users_nationality_id_foreign` (`nationality_id`),
  KEY `users_city_living_id_foreign` (`city_id`),
  KEY `users_ambassador_reviewed_by_foreign` (`ambassador_reviewed_by`),
  KEY `users_region_id_foreign` (`region_id`),
  KEY `users_stripe_customer_id_index` (`stripe_customer_id`),
  KEY `users_consul_nominated_by_foreign` (`consul_nominated_by`),
  CONSTRAINT `users_ambassador_reviewed_by_foreign` FOREIGN KEY (`ambassador_reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_city_living_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_consul_nominated_by_foreign` FOREIGN KEY (`consul_nominated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_nationality_id_foreign` FOREIGN KEY (`nationality_id`) REFERENCES `nationalities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_100000_create_password_reset_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_04_26_000001_create_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_04_26_000002_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_04_26_000003_create_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_04_26_000004_create_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_04_27_000001_add_phone_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_04_29_000001_create_connections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_05_04_092123_create_device_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_05_04_100235_create_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_05_04_100236_create_interests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_05_04_100237_create_user_interests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_05_05_000001_create_profile_visitors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_05_06_000001_create_sectors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_05_06_000002_add_sector_id_to_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_05_06_000003_create_languages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_05_06_000004_create_nationalities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_05_06_000005_create_cities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_05_06_000006_create_user_languages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_05_06_000007_add_nationality_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_05_06_000008_create_countries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_05_06_000009_refactor_cities_add_country_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_05_07_000001_add_position_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_05_07_000002_add_phone_country_code_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_05_07_000003_change_looking_for_services_to_json_in_profiles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_05_07_000004_add_fields_to_users_for_mobile',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_05_07_000005_add_fields_to_profiles_for_mobile',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_05_08_000001_create_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_05_08_000002_create_group_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_05_12_000001_create_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_05_12_000002_create_event_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_05_13_000001_refactor_user_cities_to_fk',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_05_13_000002_add_category_price_cover_to_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_05_15_000001_create_leads_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_05_15_000002_update_leads_cdc_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_05_15_000003_create_lead_ratings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_05_15_000004_add_points_badge_to_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_05_15_000005_add_sector_fraud_reminders_to_leads',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_05_15_000006_create_points_history_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_05_15_000007_create_inbox_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_05_15_000004_add_points_badge_to_users',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_05_15_000005_add_sector_fraud_reminders_to_leads',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_05_15_000006_create_points_history_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_05_15_000007_create_inbox_items_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_05_15_000008_enhance_plans_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_05_16_000001_add_cover_photo_to_groups_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_05_16_132939_add_city_id_to_groups_and_events_tables',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_05_16_134211_drop_city_birth_id_from_users_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_05_16_134536_rename_city_living_id_to_city_id_in_users_table',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_05_16_200001_create_conversations_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_05_16_200002_create_messages_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_05_17_000001_create_group_posts_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_05_18_193507_add_owner_role_to_group_user_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_05_19_000001_create_group_invitations_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_05_20_000001_create_event_invitations_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_05_20_000002_backfill_owner_role_for_group_creators',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_05_20_200000_seed_demo_users',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_05_20_210000_clean_groups_and_update_profiles',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_05_20_220000_seed_extra_users',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_05_20_230000_enrich_extra_users',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_05_20_240000_fix_user_sectors',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_05_20_250000_add_bios',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_05_20_260000_seed_plans',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_05_21_170000_drop_position_from_users_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_06_01_000001_drop_region_from_profiles',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_06_01_233910_create_polls_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_06_01_233920_create_poll_options_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_06_01_233930_create_poll_votes_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_06_03_000001_complete_lead_points',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_06_03_000002_add_presentation_video_to_profiles_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_06_05_120000_create_regions_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_06_05_120001_add_region_id_to_users_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_06_05_120002_add_annual_price_to_plans_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_06_05_120003_add_ambassador_fields_to_users_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_06_05_120004_add_region_id_to_events_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_06_05_100500_add_super_admin_role_to_users',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_06_08_000001_regions_use_cities_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_06_10_000001_add_admin_permissions_to_users_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_06_04_000001_create_notifications_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_06_08_000001_add_stripe_fields_to_billing_tables',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_06_08_000002_create_event_payments_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_06_08_000003_add_enterprise_plan_and_invitations',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_06_09_000001_extend_messages_for_media',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_06_10_000001_add_lead_type_to_leads',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_06_10_000002_create_system_settings_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_06_12_101516_assign_basic_plan_to_existing_users',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_06_12_135209_add_group_event_features_to_plans',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_06_12_140203_create_email_settings_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_06_12_140204_create_email_logs_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2026_06_12_200001_create_email_templates_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2026_06_11_000001_create_markets_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2026_06_11_000002_add_market_ids_to_profiles',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2026_06_11_000003_create_pages_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2026_06_12_210000_seed_currency_system_settings',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2026_06_17_000001_remove_ambassador_subscription_plans',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2026_06_18_133424_setup_plans_with_permissions',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2026_06_18_125944_add_permissions_and_restructure_plans',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2026_06_18_152611_add_is_active_to_cities_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2026_06_19_085905_update_badge_level_enum_on_users',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2026_06_18_200000_add_is_visible_to_plans_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2026_06_19_104349_update_all_plan_permissions',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2026_06_19_133618_seed_badge_thresholds_settings',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2026_06_19_133956_create_consul_requests_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2026_06_23_151505_add_points_fields_to_leads_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2026_06_24_101746_add_cgu_fields_to_users_table',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2026_06_27_120352_create_user_reports_table',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2026_07_01_000001_add_can_send_leads_to_plan_permissions',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2026_07_01_000002_create_permission_definitions_table',57);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2026_07_01_000003_add_group_event_email_templates',58);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2026_07_03_133629_add_is_enterprise_to_plans_table',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2026_06_24_000001_rename_premium_label_to_vip',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2026_07_03_140000_rebuild_enterprise_system',61);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2026_07_04_090732_create_rgpd_requests_table',62);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2026_07_04_092918_add_consul_status_to_users_table',63);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2026_07_04_upgrade_enterprise_system',64);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2026_07_04_120410_fix_points_balance_default_to_zero',65);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2026_07_05_000001_create_activity_logs_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2026_07_04_132807_make_reported_id_nullable_in_user_reports',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2026_07_05_145610_add_type_to_group_invitations_table',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2026_07_06_000001_create_ambassador_profiles_table',69);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2026_07_06_000002_create_ambassador_objectives_table',69);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2026_07_06_000003_create_ambassador_announcements_table',69);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2026_07_08_111232_create_event_categories_table',70);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2026_07_08_112402_add_points_negative_since_to_users_table',71);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2026_07_07_000001_add_blocked_at_to_group_user_table',72);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2026_07_07_132143_add_revoked_to_consul_requests_status',72);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2026_07_08_000001_add_scope_to_events_table',72);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2026_07_11_142953_add_converted_at_to_leads_table',72);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2026_07_14_105147_create_enterprise_quote_requests_table',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2026_07_14_094230_add_ambassador_city_id_to_users_table',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2026_07_14_103713_add_consul_city_id_to_users_table',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2026_07_15_000001_fix_basic_plan_subscriptions',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2026_07_15_000002_add_consul_rejected_at_to_users_table',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2026_07_15_000003_add_annual_billing_fields',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2026_07_18_000001_create_balance_purchases_table',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2026_07_18_000002_seed_point_price_setting',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2026_07_18_000003_add_max_connections_per_month_permission',74);
