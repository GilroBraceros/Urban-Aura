-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2025 at 09:56 AM
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
-- Database: `online_merchandise`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `first_name`, `last_name`, `gender`, `email`, `username`, `password`, `phone`, `address`) VALUES
(1, 'Gilro', 'Braceros', 'Male', 'gilrobraceros11@gmail.com', 'Gilro', 'Pop.12345', '09123456789', 'Recto St., Manila, Manila, 1234, Philippines'),
(2, 'Steph', 'Curry', 'Male', 'steph@gmail.com', 'Steph', 'Pop.12345', '09123456789', 'Recto St., Metro Manila, Manila, 1234, Philippines'),
(3, 'James', 'Harden', 'Male', 'james@gmail.com', 'James', '$2y$10$Wg951Ws6QHWBeOgUAnWJse13dqTWcJNOPu.AxeXef5Yr.twX/BPT2', '09123456789', 'Ususan St., Taguig, Metro Manila, 3169, Philippines'),
(4, 'Luka', 'Doncic', 'Male', 'luka@gmail.com', 'LukaD', '$2y$10$zkW9Pa8NuIAxTrC9g3mwneY3P2GiYs6WJulcv.XQ8pnM757xY4uK2', '09123456789', 'Recto St., Metro Manila, Manila, 1234, Philippines');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `products` text NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(12,2) NOT NULL,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `customer_id`, `session_id`, `shipping_address`, `products`, `cart_id`, `order_date`, `total_amount`, `total_items`, `status`) VALUES
(16, 3, 0, 'Ususan St., Taguig, Metro Manila, 3111, Philippines', '[{\"product_id\":19,\"product_name\":\"Oversized Long Sleeve\",\"price\":\"699.00\",\"quantity\":5}]', NULL, '2025-07-03 07:23:00', 3914.40, 5, 'Pending'),
(17, 4, 0, 'Recto St., Metro Manila, Manila, 1222, Philippines', '[{\"product_id\":20,\"product_name\":\"Bucket Hat\",\"price\":\"299.00\",\"quantity\":5},{\"product_id\":19,\"product_name\":\"Oversized Long Sleeve\",\"price\":\"699.00\",\"quantity\":5}]', NULL, '2025-07-03 07:52:45', 5588.80, 10, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `payment_date`, `payment_method`, `amount`, `payment_status`) VALUES
(13, 16, '2025-07-03 07:23:00', 'Cash on Delivery', 3914.40, 'Paid'),
(14, 17, '2025-07-03 07:52:45', 'Cash on Delivery', 5588.80, 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `product_rating` decimal(2,1) DEFAULT NULL,
  `date_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `description`, `price`, `stock_quantity`, `category_id`, `product_rating`, `date_added`) VALUES
(1, 'Urban Vibe Hoodie', 'Comfortable cotton hoodie with urban design.', 1299.99, 50, NULL, 4.5, '2025-06-01 00:00:00'),
(2, 'Streetwear Graphic Tee', 'Printed shirt with street-style artwork.', 599.00, 100, NULL, 4.7, '2025-06-02 00:00:00'),
(3, 'Classic Denim Jacket', 'Vintage-style denim jacket.', 1799.50, 30, NULL, 4.6, '2025-06-03 00:00:00'),
(4, 'Chunky White Sneakers', 'Trendy white sneakers for everyday wear.', 2499.00, 40, NULL, 4.3, '2025-06-04 00:00:00'),
(5, 'Ripped Skinny Jeans', 'Dark washed ripped skinny jeans.', 1599.00, 35, NULL, 4.2, '2025-06-05 00:00:00'),
(6, 'Canvas Sling Bag', 'Minimalist sling bag with secure zipper.', 899.00, 80, NULL, 4.4, '2025-06-06 00:00:00'),
(7, 'Snapback Cap', 'Flat-brimmed cap with embroidered logo.', 499.00, 60, NULL, 4.0, '2025-06-07 00:00:00'),
(8, 'Vintage Sunglasses', 'Retro sunglasses with UV protection.', 699.00, 70, NULL, 4.6, '2025-06-08 00:00:00'),
(9, 'Urban Aura Tracksuit', 'Full-body tracksuit for active streetwear lovers.', 1999.00, 20, NULL, 4.8, '2025-06-09 00:00:00'),
(10, 'Tie-Dye Oversized Shirt', 'Colorful tie-dye shirt for chill vibes.', 649.00, 55, NULL, 4.1, '2025-06-10 00:00:00'),
(11, 'Distressed Cargo Pants', 'Loose fit cargo pants with utility pockets.', 1399.00, 45, NULL, 4.3, '2025-06-11 00:00:00'),
(12, 'Platform Sneakers', 'Boosted sole sneakers for height and style.', 2599.00, 33, NULL, 4.4, '2025-06-12 00:00:00'),
(13, 'Puffer Vest', 'Lightweight insulated vest.', 1099.00, 25, NULL, 4.2, '2025-06-13 00:00:00'),
(14, 'Street Print Hoodie', 'Statement hoodie with graffiti print.', 1399.00, 42, NULL, 4.7, '2025-06-14 00:00:00'),
(15, 'Wide-Leg Trousers', 'Flowy pants for relaxed urban look.', 1199.00, 30, NULL, 4.1, '2025-06-15 00:00:00'),
(16, 'Streetwear Beanie', 'Warm knitted beanie for cold days.', 399.00, 70, NULL, 4.3, '2025-06-16 00:00:00'),
(17, 'Urban Crossbody Bag', 'Small crossbody bag with durable strap.', 999.00, 50, NULL, 4.5, '2025-06-17 00:00:00'),
(18, 'Techwear Jacket', 'Futuristic jacket with zippers and straps.', 2899.00, 15, NULL, 4.6, '2025-06-18 00:00:00'),
(19, 'Oversized Long Sleeve', 'Relaxed fit long-sleeve tee.', 699.00, 60, NULL, 4.2, '2025-06-19 00:00:00'),
(20, 'Bucket Hat', 'Classic bucket hat for sunny days.', 299.00, 65, NULL, 4.0, '2025-06-20 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_ibfk_2` (`cart_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `payment_ibfk_1` (`order_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
