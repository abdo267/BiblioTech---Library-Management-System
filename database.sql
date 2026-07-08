--Library Management System Database Schema & Seed Data

CREATE DATABASE IF NOT EXISTS `library_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `library_db`;


-- 1. Table `users`

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'member') NOT NULL DEFAULT 'member',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_token_expiry` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2. Table `members`

CREATE TABLE IF NOT EXISTS `members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `registration_date` DATE NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 3. Table `authors`

CREATE TABLE IF NOT EXISTS `authors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `biography` TEXT DEFAULT NULL,
  `nationality` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
-- 4. Table `categories`
 
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
-- 5. Table `books`
 
CREATE TABLE IF NOT EXISTS `books` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `isbn` VARCHAR(20) DEFAULT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `author_id` INT DEFAULT NULL,
  `category_id` INT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `publisher` VARCHAR(255) DEFAULT NULL,
  `publication_year` INT DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 0,
  `available_copies` INT NOT NULL DEFAULT 0,
  `shelf_location` VARCHAR(100) DEFAULT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  INDEX `idx_author` (`author_id`),
  INDEX `idx_category` (`category_id`),
  INDEX `idx_title` (`title`),
  INDEX `idx_isbn` (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
-- 6. Table `borrow_requests`
 
CREATE TABLE IF NOT EXISTS `borrow_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  INDEX `idx_request_member` (`member_id`),
  INDEX `idx_request_book` (`book_id`),
  INDEX `idx_request_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
-- 7. Table `borrowings`
 
CREATE TABLE IF NOT EXISTS `borrowings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `borrow_request_id` INT DEFAULT NULL,
  `member_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `borrow_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `return_date` DATE DEFAULT NULL,
  `status` ENUM('borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'borrowed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`borrow_request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  INDEX `idx_borrow_member` (`member_id`),
  INDEX `idx_borrow_book` (`book_id`),
  INDEX `idx_borrow_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
-- 8. Table `returns`
 
CREATE TABLE IF NOT EXISTS `returns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `borrowing_id` INT NOT NULL,
  `return_date` DATE NOT NULL,
  `fine_amount` DECIMAL(10,2) DEFAULT 0.00,
  `payment_status` ENUM('unpaid', 'paid', 'waived') NOT NULL DEFAULT 'unpaid',
  `paid_at` DATETIME DEFAULT NULL,
  `stripe_payment_intent_id` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('returned', 'damaged', 'lost') NOT NULL DEFAULT 'returned',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`borrowing_id`) REFERENCES `borrowings` (`id`) ON DELETE CASCADE,
  INDEX `idx_return_borrowing` (`borrowing_id`),
  INDEX `idx_return_payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
-- 8b. Table `cart_items`
 
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT NOT NULL,
  `return_id` INT NOT NULL,
  `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_member_return` (`member_id`, `return_id`),
  INDEX `idx_cart_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8c. Table `payments`
 
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` INT NOT NULL,
  `stripe_payment_intent_id` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'usd',
  `status` ENUM('pending', 'succeeded', 'failed', 'canceled') NOT NULL DEFAULT 'pending',
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'card',
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_stripe_pi` (`stripe_payment_intent_id`),
  INDEX `idx_payment_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
-- 8d. Table `payment_items`
 
CREATE TABLE IF NOT EXISTS `payment_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `payment_id` INT NOT NULL,
  `return_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE,
  INDEX `idx_payment_item_payment` (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
-- 9. Table `activity_logs`
 
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_log_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 

 
-- Insert Users
-- password for admin is 'admin123'
-- password for member is 'member123'
INSERT INTO `users` (`id`, `email`, `password`, `role`, `status`) VALUES
(1, 'admin@library.com', '$2y$10$GoA2YwOM9epifanDjmJlQ.q/oBV95T5Olrpg.fAiLPnA0OFU9a6hy', 'admin', 'active'),
(2, 'member@library.com', '$2y$10$KRCFCSlpXWQF28yVoEDpOuGjiVjYcXa2MVndathc3h/gk0E7X60wi', 'member', 'active'),
(3, 'jane.doe@library.com', '$2y$10$KRCFCSlpXWQF28yVoEDpOuGjiVjYcXa2MVndathc3h/gk0E7X60wi', 'member', 'active');

-- Insert Members
INSERT INTO `members` (`id`, `user_id`, `full_name`, `phone`, `address`, `registration_date`, `status`) VALUES
(1, 2, 'John Doe', '+1234567890', '123 Library Street, Booktown', '2026-01-15', 'active'),
(2, 3, 'Jane Doe', '+0987654321', '456 Reader Lane, Novel City', '2026-02-10', 'active');

-- Insert Authors
INSERT INTO `authors` (`id`, `name`, `biography`, `nationality`) VALUES
(1, 'Robert C. Martin', 'Robert Cecil Martin, colloquially known as "Uncle Bob", is an American software engineer, instructor, and author.', 'American'),
(2, 'Martin Fowler', 'Martin Fowler is a British software developer, author and international public speaker on software development.', 'British'),
(3, 'George Orwell', 'Eric Arthur Blair, better known by his pen name George Orwell, was an English novelist, essayist, journalist, and critic.', 'English'),
(4, 'Isaac Asimov', 'Isaac Asimov was an American writer and professor of biochemistry at Boston University, known for his works of science fiction.', 'American'),
(5, 'J.K. Rowling', 'Joanne Rowling, better known by her pen name J. K. Rowling, is a British author, philanthropist, film producer, and screenwriter.', 'British');

-- Insert Categories
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Programming'),
(2, 'Science Fiction'),
(3, 'Literature'),
(4, 'History'),
(5, 'Biography');

-- Insert Books
INSERT INTO `books` (`id`, `isbn`, `title`, `author_id`, `category_id`, `description`, `publisher`, `publication_year`, `quantity`, `available_copies`, `shelf_location`, `cover_image`) VALUES
(1, '9780132350884', 'Clean Code', 1, 1, 'A Handbook of Agile Software Craftsmanship. Clean Code is divided into three parts. The first describes the principles, patterns, and practices of writing clean code.', 'Prentice Hall', 2008, 5, 4, 'Shelf A-12', 'clean_code.jpg'),
(2, '9780134494166', 'Clean Architecture', 1, 1, 'A Craftsman\'s Guide to Software Structure and Design. Uncle Bob presents the universal rules of software architecture.', 'Prentice Hall', 2017, 3, 3, 'Shelf A-13', 'clean_architecture.jpg'),
(3, '9780201633610', 'Design Patterns', 2, 1, 'Elements of Reusable Object-Oriented Software. This book provides a catalog of simple and succinct solutions to commonly occurring design problems.', 'Addison-Wesley', 1994, 4, 3, 'Shelf A-14', 'design_patterns.jpg'),
(4, '9780451524935', '1984', 3, 3, 'Winston Smith reins in his rebellion against the Party, which demands absolute allegiance and controls him through the Thought Police.', 'Signet Classics', 1950, 10, 10, 'Shelf B-01', '1984.jpg'),
(5, '9780553293357', 'Foundation', 4, 2, 'The first novel in Isaac Asimov\'s classic science-fiction masterpiece, the Foundation Trilogy.', 'Spectra', 1951, 6, 5, 'Shelf C-05', 'foundation.jpg');

-- Insert Borrow Requests
INSERT INTO `borrow_requests` (`id`, `member_id`, `book_id`, `request_date`, `status`, `admin_notes`) VALUES
(1, 1, 1, '2026-06-28 10:00:00', 'approved', 'Approved request'),
(2, 1, 3, '2026-07-01 11:30:00', 'approved', 'Approved by admin'),
(3, 2, 5, '2026-07-03 14:15:00', 'approved', 'Approved request'),
(4, 2, 1, '2026-07-05 09:00:00', 'pending', NULL);

-- Insert Borrowings
INSERT INTO `borrowings` (`id`, `borrow_request_id`, `member_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`) VALUES
(1, 1, 1, 1, '2026-06-28', '2026-07-12', '2026-07-03', 'returned'),
(2, 2, 1, 3, '2026-07-01', '2026-07-15', NULL, 'borrowed'),
(3, 3, 2, 5, '2026-07-03', '2026-07-17', NULL, 'borrowed');

-- Insert Returns
INSERT INTO `returns` (`id`, `borrowing_id`, `return_date`, `fine_amount`, `payment_status`, `status`) VALUES
(1, 1, '2026-07-03', 0.00, 'waived', 'returned');


INSERT INTO `borrowings` (`id`, `borrow_request_id`, `member_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`) VALUES
(4, NULL, 1, 4, '2026-06-01', '2026-06-15', '2026-06-20', 'returned');

INSERT INTO `returns` (`id`, `borrowing_id`, `return_date`, `fine_amount`, `payment_status`, `status`) VALUES
(2, 4, '2026-06-20', 7.50, 'unpaid', 'returned');

-- Insert Activity Logs
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`) VALUES
(1, 1, 'Database Seed', 'Initial database schema and sample records seeded.'),
(2, 2, 'Register', 'Member John Doe registered successfully.'),
(3, 3, 'Register', 'Member Jane Doe registered successfully.'),
(4, 1, 'Approve Borrow', 'Approved borrow request #1 for member John Doe (Book: Clean Code).'),
(5, 1, 'Approve Borrow', 'Approved borrow request #2 for member John Doe (Book: Design Patterns).');
