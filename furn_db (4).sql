-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2025 at 09:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `furn_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `full_name`, `street_address`, `city`, `state`, `postal_code`, `country`, `phone_number`, `is_default`, `created_at`) VALUES
(1, 4, 'karl', '2-8', 'angeles', 'pampanga', '2009', 'philippines', '32434243', 0, '2025-03-28 10:32:22'),
(2, 7, 'ryna', 'maria aquino', 'angeles city', 'pandan', '2009', 'philippines', '09916612949', 0, '2025-03-28 16:10:48'),
(3, 7, 'HELLO', 'June St', 'Angeles City', 'Pampanga', '2005', 'Philippines', '09754150857', 1, '2025-03-28 23:35:47'),
(5, 7, 'Crizzy', 'Malino', 'Sanfernando', 'Pampanga', '2000', 'Philippines', '0999999999', 0, '2025-03-29 01:36:45'),
(6, 9, 'Test User', 'Victoria St.', 'Angeles City', 'Pampanga', '2009', 'Qatar', '0999999999', 0, '2025-03-29 06:45:17'),
(7, 9, 'Test User', 'Ma. Aquino', 'Porac', 'Pampanga', '2000', 'Philippines', '09916612949', 0, '2025-03-29 06:46:21');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_colorway_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `product_colorway_id`, `quantity`, `added_at`) VALUES
(48, 8, 15, 24, 1, '2025-03-29 04:05:48'),
(58, 7, 16, 25, 1, '2025-03-29 15:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(2, 'Test Name', 'ransar39@gmail.com', 'Question', 'Can you add more products?', '2025-03-29 03:53:29'),
(3, 'adrian', 'adrian@gmail.com', 'Inquire', 'Hello welcome', '2025-03-29 06:52:22'),
(5, 'hello', 'helo@gmail.com', 'hello', 'hello its me', '2025-03-29 07:30:40');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `billing_address_id` int(11) NOT NULL,
  `shipping_address_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `payment_method` varchar(50) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 500.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `billing_address_id`, `shipping_address_id`, `total_amount`, `status`, `payment_method`, `order_date`, `shipped_at`, `delivered_at`, `shipping_fee`) VALUES
(1, 4, 1, 1, 123.00, 'Delivered', 'credit_card', '2025-03-28 10:32:22', NULL, '2025-03-28 12:07:30', 500.00),
(2, 4, 1, 1, 582.00, 'Pending', 'credit_card', '2025-03-28 12:19:52', NULL, NULL, 500.00),
(3, 4, 1, 1, 3452.00, 'Pending', 'bank_transfer', '2025-03-28 12:43:48', NULL, NULL, 500.00),
(4, 4, 1, 1, 12340.00, 'Shipped', 'bank_transfer', '2025-03-28 13:19:14', '2025-03-28 14:21:57', NULL, 500.00),
(5, 4, 1, 1, 723.65, 'Pending', 'credit_card', '2025-03-28 14:55:18', NULL, NULL, 500.00),
(6, 4, 1, 1, 723.65, 'Pending', 'credit_card', '2025-03-28 14:55:35', NULL, NULL, 500.00),
(7, 4, 1, 1, 629.15, 'Pending', 'credit_card', '2025-03-28 14:58:48', NULL, NULL, 500.00),
(8, 4, 1, 1, 629.15, 'Pending', 'credit_card', '2025-03-28 14:59:09', NULL, NULL, 500.00),
(9, 4, 1, 1, 723.65, 'Pending', 'paypal', '2025-03-28 14:59:53', NULL, NULL, 500.00),
(10, 4, 1, 1, 629.15, 'Pending', 'bank_transfer', '2025-03-28 15:01:12', NULL, NULL, 500.00),
(11, 4, 1, 1, 629.15, 'Pending', 'credit_card', '2025-03-28 15:01:53', NULL, NULL, 500.00),
(12, 4, 1, 1, 629.15, 'Cancelled', 'paypal', '2025-03-28 15:04:23', NULL, NULL, 500.00),
(13, 7, 2, 2, 36195.80, 'Processing', 'credit_card', '2025-03-28 16:10:48', NULL, NULL, 500.00),
(14, 7, 2, 2, 14148.95, 'Delivered', 'paypal', '2025-03-28 23:27:40', NULL, '2025-03-29 02:55:07', 500.00),
(15, 7, 3, 3, 85335.80, 'Pending', 'credit_card', '2025-03-28 23:35:47', NULL, NULL, 500.00),
(16, 7, 5, 5, 85335.80, 'Processing', 'bank_transfer', '2025-03-29 01:36:45', '2025-03-29 02:50:59', NULL, 500.00),
(17, 7, 5, 5, 77775.80, 'Shipped', 'bank_transfer', '2025-03-29 05:11:18', '2025-03-29 06:51:22', NULL, 500.00),
(18, 9, 6, 6, 10891.85, 'Shipped', 'credit_card', '2025-03-29 06:45:17', '2025-03-29 07:29:52', NULL, 500.00),
(19, 7, 2, 2, 40660.40, 'Pending', 'credit_card', '2025-03-29 08:46:26', NULL, NULL, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_colorway_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time_of_order` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_colorway_id`, `quantity`, `price_at_time_of_order`) VALUES
(1, 1, 6, NULL, 1, 123.00),
(2, 2, 5, NULL, 1, 213.00),
(3, 2, 6, NULL, 3, 123.00),
(4, 3, 6, NULL, 8, 123.00),
(5, 3, 7, NULL, 2, 1234.00),
(6, 4, 7, NULL, 10, 1234.00),
(7, 5, 5, NULL, 1, 213.00),
(8, 6, 5, NULL, 1, 213.00),
(9, 7, 6, NULL, 1, 123.00),
(10, 8, 6, NULL, 1, 123.00),
(11, 9, 5, NULL, 1, 213.00),
(12, 10, 6, NULL, 1, 123.00),
(13, 11, 6, NULL, 1, 123.00),
(14, 12, 6, NULL, 1, 123.00),
(15, 13, 6, 17, 4, 8499.00),
(16, 14, 11, 20, 1, 12999.00),
(17, 15, 15, 24, 4, 20199.00),
(18, 16, 15, 24, 4, 20199.00),
(19, 17, 11, 20, 1, 12999.00),
(20, 17, 15, 24, 3, 20199.00),
(21, 18, 13, 22, 3, 3299.00),
(22, 19, 16, 25, 2, 19124.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `main_image` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `stock` int(11) DEFAULT 0,
  `category` enum('Living Room','Bedroom','Dining Room') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `main_image`, `name`, `price`, `description`, `stock`, `category`) VALUES
(5, '67e79cfc6de1e_centerpiece.jpg', 'Centerpiece Decor', 5000.00, 'A decorative floral arrangement to elevate your dining table aesthetics.', 50, 'Dining Room'),
(6, '67e6c046b3344_sideboard.jpg', 'Sideboard', 8499.00, 'A spacious sideboard with cabinets and drawers for organized storage.', 54, 'Dining Room'),
(9, '67e6c0900ffbd_tablewareset.jpg', 'Tableware Set', 3299.00, 'A 16-piece porcelain dinnerware set, perfect for any occasion.', 55, 'Dining Room'),
(10, '67e6c0dd59887_tablelamp.jpg', 'Table Lamp', 2499.00, 'Handcrafted ceramic lamp with linen shade for bedroom ambiance.', 105, 'Bedroom'),
(11, '67e6c1683bfc1_bedframe.webp', 'Bed', 12999.00, 'Sleek platform bed with sturdy wooden slats and built-in storage.', 25, 'Bedroom'),
(12, '67e6c19619045_chairs.webp', 'Chairs', 9499.00, 'Set of four upholstered dining chairs with a sleek and modern design.', 200, 'Dining Room'),
(13, '67e6c1c70123d_nightstand.webp', 'Nightstand', 3299.00, 'Classic nightstand with drawer and open shelf for storage.', 150, 'Bedroom'),
(14, '67e6c1ff21435_mattress.webp', 'Mattress', 18499.00, 'Premium memory foam mattress with cooling gel technology.', 60, 'Bedroom'),
(15, '67e6c2318f2f1_modernsofa.webp', 'Sofa', 20199.00, 'A sleek and comfortable modern sofa perfect for any living room.', 25, 'Living Room'),
(16, '67e6c265e2171_coffeetable.webp', 'Coffee Table', 19124.00, 'A stylish wooden coffee table with a durable finish.', 255, 'Living Room'),
(17, '67e6c288d8bec_tvstand.webp', 'TV Stand', 39319.00, 'A sleek TV stand with ample storage space for all your media devices.', 99, 'Living Room'),
(18, '67e6c2aa35214_wallart.webp', 'Wall Art', 10999.00, 'A stunning piece of abstract wall art to add character to your living room.', 50, 'Living Room');

-- --------------------------------------------------------

--
-- Table structure for table `product_colorways`
--

CREATE TABLE `product_colorways` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_name` varchar(100) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_colorways`
--

INSERT INTO `product_colorways` (`id`, `product_id`, `color_name`, `image_path`, `is_default`) VALUES
(17, 6, 'White', '67e6c046b3cd9_sideboard.jpg', 1),
(18, 9, 'Green', '67e6c09010c53_tablewareset.jpg', 1),
(19, 10, 'Red', '67e6c0dd59c7f_tablelamp.jpg', 1),
(20, 11, 'Pink', '67e6c1683ff13_bedframe.webp', 1),
(21, 12, 'Brown', '67e6c196195d7_chairs.webp', 1),
(22, 13, 'Light', '67e6c1c7022b3_nightstand.webp', 1),
(23, 14, 'Blue', '67e6c1ff229f5_mattress.webp', 1),
(24, 15, 'Blue', '67e6c23190482_modernsofa.webp', 1),
(25, 16, 'Brown', '67e6c265e4242_coffeetable.webp', 1),
(27, 18, 'White', '67e6c2aa3570c_wallart.webp', 1),
(28, 17, 'Black', '67e6c288d8f94_tvstand.webp', 1),
(31, 5, 'Brown', '67e79cfc6fbf9_centerpiece.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `last_login`) VALUES
(1, 'Karl', 'karl@gmail.com', '$2y$10$KGfOsV0sGcY8E7VqB0PaGeuZPM66QlZp5I3f439wre4iocuws2pQW', '2025-03-26 16:39:57', '2025-03-26 16:46:18'),
(2, 'Ry', 'ry@gmail.com', '$2y$10$UDaTDHuAvdpX1XBknUiYU.UWXsM7C3WCaAQRoj22uE5GYwRbeLX7y', '2025-03-26 16:48:13', '2025-03-26 17:01:56'),
(3, 'lebrun', 'lebrun@gmail.com', '$2y$10$alRRmB1cBVunaWksEDhK9.1IZcFXb/HPBpQ3S1xtk5otNaJTeoyxi', '2025-03-26 16:51:16', '2025-03-26 16:51:45'),
(4, 'Katd', 'karlos@gmail.com', '$2y$10$cbzkT151HAx3hLajRRtxpOA2BMDcd9lKuzvHvsAqpkCtPU8wUn9I.', '2025-03-28 02:57:05', '2025-03-29 03:11:50'),
(5, 'hello', 'sarmientoadrian941@gmail.com', '$2y$10$L7QEGXGYbf9e/bweQU4qv.98Bs3zm09col3OvKQ5K1fkR32PIl5kG', '2025-03-28 15:19:08', '2025-03-28 15:19:31'),
(6, 'rynaa', 'adriansarmiento53@yahoo.com', '$2y$10$r/5CM0S7r3VQ1jfmYRLTjeEtFqo36sllCAyG7ZVI/m0Idp8M540b6', '2025-03-28 15:40:42', NULL),
(7, 'rynamae', 'apsarmiento3@student.hau.edu.ph', '$2y$10$cpwpzKa4yThfvlJUzRzq3ey7O.MaEQ/Rd.pHSL4bkoOQoyCO23dRW', '2025-03-28 15:42:39', '2025-03-29 15:22:25'),
(8, 'adriansarmiento', 'samirakatarina@gmail.com', '$2y$10$qRCbZSACDwPTyyJWUj1prOAAKQoYpAGOqd3J4orFd6aDiQnlaghmy', '2025-03-29 04:04:42', '2025-03-29 05:03:51'),
(9, 'testuser', 'testuser@gmail.com', '$2y$10$FoAV5w11zVjW2.zIgPJd.eT/7yOp7FHn8xfEuAJqe4FYAuluzbPFi', '2025-03-29 06:42:52', '2025-03-29 07:47:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_addresses_user` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`,`product_colorway_id`),
  ADD KEY `idx_cart_product` (`product_id`),
  ADD KEY `idx_cart_colorway` (`product_colorway_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_billing_address` (`billing_address_id`),
  ADD KEY `idx_orders_shipping_address` (`shipping_address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`),
  ADD KEY `idx_order_items_colorway` (`product_colorway_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_colorways`
--
ALTER TABLE `product_colorways`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_colorways` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_colorways`
--
ALTER TABLE `product_colorways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`product_colorway_id`) REFERENCES `product_colorways` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`billing_address_id`) REFERENCES `addresses` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`product_colorway_id`) REFERENCES `product_colorways` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_colorways`
--
ALTER TABLE `product_colorways`
  ADD CONSTRAINT `product_colorways_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
