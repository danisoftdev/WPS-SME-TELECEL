-- =============================================================================
-- user_subscriptions — "My subscriptions" in the app (/subscriptions)
-- =============================================================================
-- Prerequisites (must exist first):
--   • users (with column id bigint unsigned)
--   • bundle_subscriptions (with column id bigint unsigned)
--
-- Run in phpMyAdmin: select your DB → SQL → paste → Go
-- =============================================================================

CREATE TABLE IF NOT EXISTS `user_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `bundle_subscription_id` bigint unsigned NOT NULL,
  `balance_gb` decimal(12,2) NOT NULL DEFAULT 0.00,
  `beneficiaries` json DEFAULT NULL COMMENT 'JSON array of phone numbers',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_subscriptions_user_id_bundle_subscription_id_index` (`user_id`,`bundle_subscription_id`),
  CONSTRAINT `user_subscriptions_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_subscriptions_bundle_subscription_id_foreign`
    FOREIGN KEY (`bundle_subscription_id`) REFERENCES `bundle_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- If foreign keys fail (wrong types / missing parent tables), use this instead:
-- Remove the CONSTRAINT lines and create indexes only, then fix data and add FKs later.
-- =============================================================================
/*
CREATE TABLE IF NOT EXISTS `user_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `bundle_subscription_id` bigint unsigned NOT NULL,
  `balance_gb` decimal(12,2) NOT NULL DEFAULT 0.00,
  `beneficiaries` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_subscriptions_user_id_bundle_subscription_id_index` (`user_id`,`bundle_subscription_id`),
  KEY `user_subscriptions_user_id_foreign` (`user_id`),
  KEY `user_subscriptions_bundle_subscription_id_foreign` (`bundle_subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/
