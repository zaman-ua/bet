SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS `bets` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `user_id` int(11) UNSIGNED NOT NULL,
  `currency_id` int(11) UNSIGNED NOT NULL,
  `match_id` int(11) UNSIGNED NOT NULL,
  `outcome` enum('win','draw','loss') NOT NULL,
  `stake` bigint(20) NOT NULL DEFAULT 0,
  `coefficient` int(11) NOT NULL,
  `status` enum('placed','won','lost') NOT NULL DEFAULT 'placed',
  `payout` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_us_cur` (`user_id`,`currency_id`),
  KEY `fk_ual_currency` (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `currencies` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `code` varchar(3) NOT NULL,
  `symbol` varchar(6) NOT NULL,
  `is_base_currency` tinyint(1) NOT NULL DEFAULT 0,
  `convert_value` int(11) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `is_base_currency` (`is_base_currency`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `currencies` (`id`, `created_at`, `updated_at`, `code`, `symbol`, `is_base_currency`, `convert_value`) VALUES
(1, '2025-09-17 09:46:49', NULL, 'USD', 'USD', 0, 1),
(2, '2025-09-17 09:46:49', NULL, 'EUR', 'EUR', 0, 1),
(3, '2025-09-17 09:46:49', NULL, 'RUB', 'RUB', 0, 1);

CREATE TABLE IF NOT EXISTS `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20250913101108, 'UserTable', '2025-09-17 12:46:49', '2025-09-17 12:46:49', 0),
(20250913102005, 'UserContactsTable', '2025-09-17 12:46:49', '2025-09-17 12:46:49', 0),
(20250913103355, 'Currency', '2025-09-17 12:46:49', '2025-09-17 12:46:49', 0),
(20250913104149, 'UserAmount', '2025-09-17 12:46:49', '2025-09-17 12:46:49', 0),
(20250914115753, 'UserAmountChangeColAmount', '2025-09-17 12:46:49', '2025-09-17 12:46:49', 0),
(20250914120040, 'Bets', '2025-09-17 12:46:49', '2025-09-17 12:46:49', 0),
(20250914120045, 'UserAmountLogs', '2025-09-17 12:46:49', '2025-09-17 12:46:49', 0);

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `login` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gender` enum('m','f','o') NOT NULL DEFAULT 'o',
  `birth_date` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `gender` (`gender`),
  KEY `status` (`status`),
  KEY `is_admin` (`is_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `created_at`, `updated_at`, `login`, `password_hash`, `name`, `gender`, `birth_date`, `status`, `is_admin`) VALUES
(1, '2025-09-17 09:46:49', NULL, 'admin', '$2y$12$5cumGvC7XEu.nPudsEtfle1C5IRnG9LCXq7JqfCVhkHOBB6ujbiRm', 'Admin', 'o', '1990-01-01', 'active', 1),
(2, '2025-09-17 09:46:50', NULL, 'zaman', '$2y$12$E8BQ3FcL7aVdICcR67.oruD6oPRuoYliuRmxqIWmonhIdX2bRVnhC', 'Лазарев Евгений', 'm', '1990-02-21', 'active', 0);

CREATE TABLE IF NOT EXISTS `user_amounts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `user_id` int(11) UNSIGNED NOT NULL,
  `currency_id` int(11) UNSIGNED NOT NULL,
  `amount` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_us_cur` (`user_id`,`currency_id`),
  KEY `fk_ua_currency` (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_amount_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `user_id` int(11) UNSIGNED NOT NULL,
  `currency_id` int(11) UNSIGNED NOT NULL,
  `bet_id` int(11) UNSIGNED DEFAULT NULL,
  `type` enum('admin_adjust','bet_place','bet_win','deposit','withdraw','refund') NOT NULL,
  `amount` bigint(20) NOT NULL DEFAULT 0,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_us_cur` (`user_id`,`currency_id`),
  KEY `fk_user_amount_logs_currency` (`currency_id`),
  KEY `fk_user_amount_logs_bet` (`bet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_contacts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `user_id` int(11) UNSIGNED NOT NULL,
  `type` enum('phone','email','address') NOT NULL,
  `value` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `bets`
  ADD CONSTRAINT `fk_ual_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_ual_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `user_amounts`
  ADD CONSTRAINT `fk_ua_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_ua_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `user_amount_logs`
  ADD CONSTRAINT `fk_user_amount_logs_bet` FOREIGN KEY (`bet_id`) REFERENCES `bets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_amount_logs_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_amount_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_contacts`
  ADD CONSTRAINT `fk_uc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
