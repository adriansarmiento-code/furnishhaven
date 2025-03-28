-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2025 at 04:10 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

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
(1, 4, 'karl', '2-8', 'angeles', 'pampanga', '2009', 'philippines', '32434243', 0, '2025-03-28 10:32:22');

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
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `billing_address_id`, `shipping_address_id`, `total_amount`, `status`, `payment_method`, `order_date`, `shipped_at`, `delivered_at`) VALUES
(1, 4, 1, 1, 123.00, 'Delivered', 'credit_card', '2025-03-28 10:32:22', NULL, '2025-03-28 12:07:30'),
(2, 4, 1, 1, 582.00, 'Pending', 'credit_card', '2025-03-28 12:19:52', NULL, NULL),
(3, 4, 1, 1, 3452.00, 'Pending', 'bank_transfer', '2025-03-28 12:43:48', NULL, NULL),
(4, 4, 1, 1, 12340.00, 'Shipped', 'bank_transfer', '2025-03-28 13:19:14', '2025-03-28 14:21:57', NULL),
(5, 4, 1, 1, 723.65, 'Pending', 'credit_card', '2025-03-28 14:55:18', NULL, NULL),
(6, 4, 1, 1, 723.65, 'Pending', 'credit_card', '2025-03-28 14:55:35', NULL, NULL),
(7, 4, 1, 1, 629.15, 'Pending', 'credit_card', '2025-03-28 14:58:48', NULL, NULL),
(8, 4, 1, 1, 629.15, 'Pending', 'credit_card', '2025-03-28 14:59:09', NULL, NULL),
(9, 4, 1, 1, 723.65, 'Pending', 'paypal', '2025-03-28 14:59:53', NULL, NULL),
(10, 4, 1, 1, 629.15, 'Pending', 'bank_transfer', '2025-03-28 15:01:12', NULL, NULL),
(11, 4, 1, 1, 629.15, 'Pending', 'credit_card', '2025-03-28 15:01:53', NULL, NULL),
(12, 4, 1, 1, 629.15, 'Pending', 'paypal', '2025-03-28 15:04:23', NULL, NULL);

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
(1, 1, 6, 13, 1, 123.00),
(2, 2, 5, 15, 1, 213.00),
(3, 2, 6, 13, 3, 123.00),
(4, 3, 6, 13, 8, 123.00),
(5, 3, 7, 12, 2, 1234.00),
(6, 4, 7, 12, 10, 1234.00),
(7, 5, 5, 15, 1, 213.00),
(8, 6, 5, 15, 1, 213.00),
(9, 7, 6, 13, 1, 123.00),
(10, 8, 6, 13, 1, 123.00),
(11, 9, 5, 15, 1, 213.00),
(12, 10, 6, 13, 1, 123.00),
(13, 11, 6, 13, 1, 123.00),
(14, 12, 6, 13, 1, 123.00);

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
(5, '67e6246955fd4_Minecraft-13_05_2020-1_36_59-AM-opf1op28fhbkr683hdy8ukpqanquakp3883mm34sns.png', 'couch33', 213.00, 'basta couch yun lang idk', 1233, 'Living Room'),
(6, '67e6248ade099_Bed.webp', 'bed ni ry', 123.00, 'ikkama kita mamaya', 1222, 'Bedroom'),
(7, '67e624ac82192_Medieval-Feast-Table-Set-1.webp', 'dining table', 1234.00, 'ma anong ulam, eto giniling', 1222, 'Dining Room'),
(8, '67e62ae8ded31_maxresdefault.jpg', 'bagong dining tabol', 1233.00, 'basta bagong table', 12312, 'Dining Room');

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
(12, 7, 'brown', '67e624ac83e1d_Medieval-Feast-Table-Set-1.webp', 1),
(13, 6, 'red', '67e6248ade5c1_Bed.webp', 1),
(14, 8, 'brown', '67e62ae8e072c_maxresdefault.jpg', 1),
(15, 5, 'white', '67e6246957801_Minecraft-13_05_2020-1_36_59-AM-opf1op28fhbkr683hdy8ukpqanquakp3883mm34sns.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user` varchar(100) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(4, 'Katd', 'karlos@gmail.com', '$2y$10$cbzkT151HAx3hLajRRtxpOA2BMDcd9lKuzvHvsAqpkCtPU8wUn9I.', '2025-03-28 02:57:05', '2025-03-28 15:00:57');

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
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_colorways`
--
ALTER TABLE `product_colorways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`product_colorway_id`) REFERENCES `product_colorways` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_colorways`
--
ALTER TABLE `product_colorways`
  ADD CONSTRAINT `product_colorways_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
