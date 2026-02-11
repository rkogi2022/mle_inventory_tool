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
  `created_at` timestamp DEFAULT current_timestamp(),
  FOREIGN KEY (`user_id`) REFERENCES `staff_login`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;