DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ticket_number` varchar(50) UNIQUE NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` enum('hardware','training','other') NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `page_url` varchar(500) DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `status` enum('open','ongoing','resolved','closed') DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL COMMENT 'Assigned staff member ID',
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `feedback_submitted` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  FOREIGN KEY (`user_id`) REFERENCES `staff_login`(`id`),
  FOREIGN KEY (`assigned_to`) REFERENCES `staff_login`(`id`),
  FOREIGN KEY (`resolved_by`) REFERENCES `staff_login`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Simplified feedback table.....Stores user satisfaction feedback after ticket is resolved.
DROP TABLE IF EXISTS `ticket_feedback`;
CREATE TABLE `ticket_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` int(11) NOT NULL,
  `rating` tinyint(1) CHECK (rating >= 1 AND rating <= 5),
  `satisfaction` enum('very_satisfied','satisfied','neutral','dissatisfied','very_dissatisfied') DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `submitted_at` timestamp DEFAULT current_timestamp(),
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Simple status log .....Tracks every status change (audit trail).
DROP TABLE IF EXISTS `ticket_status_log`;
CREATE TABLE `ticket_status_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`changed_by`) REFERENCES `staff_login`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;