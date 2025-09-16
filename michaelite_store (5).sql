-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 16, 2025 at 06:20 AM
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
-- Database: `michaelite_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmark`
--

CREATE TABLE `bookmark` (
  `bookmark_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `listings_id` int(11) UNSIGNED NOT NULL,
  `bookmarked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Audio'),
(2, 'Baby'),
(3, 'Cameras'),
(4, 'Gaming'),
(5, 'Groceries'),
(6, 'Health & Personal Care'),
(7, 'Hobbies & Stationery'),
(8, 'Home & Living'),
(9, 'Home Appliances'),
(10, 'Home Entertainment'),
(11, 'Laptops & Computers'),
(12, 'Makeup & Fragrances'),
(13, 'Men\'s Apparel'),
(14, 'Men\'s Bags & Accessories'),
(15, 'Men\'s Shoes'),
(16, 'Mobiles & Gadgets'),
(17, 'Mobiles Accessories'),
(18, 'Motors'),
(19, 'Pet Care'),
(20, 'Sports & Travel'),
(21, 'Toys, Games & Collectibles'),
(22, 'Women Accessories'),
(23, 'Women\'s Apparel'),
(24, 'Women\'s Bags'),
(25, 'Women\'s Shoes');

-- --------------------------------------------------------

--
-- Table structure for table `follow`
--

CREATE TABLE `follow` (
  `follow_id` int(11) UNSIGNED NOT NULL,
  `follower_id` int(11) UNSIGNED NOT NULL,
  `followed_id` int(11) UNSIGNED NOT NULL,
  `followed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `listings_id` int(11) UNSIGNED NOT NULL,
  `listing_owner_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `subcategory_id` int(11) UNSIGNED NOT NULL,
  `listing_status` enum('active','draft','pending','unavailable','inactive','out of stock','pre-order','discontinued','sold') NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `condition` enum('Brand New','Like New','Lightly Used','Well Used','Heavily Used','Refurbished') NOT NULL,
  `discount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`listings_id`, `listing_owner_id`, `name`, `brand`, `description`, `price`, `stock_quantity`, `subcategory_id`, `listing_status`, `date_created`, `date_modified`, `condition`, `discount`) VALUES
(1, 1, 'Nike Pegasus 41', 'Nike', 'Springy foam plus snappy Air Zoom units equals our most energised ride.\r\n\r\nResponsive cushioning in the Pegasus provides an energised ride for everyday road running. Experience lighter-weight energy return with dual Air Zoom units and a ReactX foam midsole. Plus, improved engineered mesh on the upper decreases weight and increases breathability.\r\n\r\nResponsive Ride\r\nReactX foam midsole surrounds forefoot and heel Air Zoom units for an energised ride. It\'s 13% more responsive than previous React technology.\r\n\r\nWaffle-inspired traction\r\nThe signature Waffle-inspired rubber outsole provides traction and flexibility.\r\n\r\nPlush padding\r\nA plush collar, tongue and sockliner provide a secure and comfortable fit.', 7395.00, 1, 190, 'active', '2025-09-16 02:01:27', '2025-09-16 02:08:37', 'Brand New', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `listing_images`
--

CREATE TABLE `listing_images` (
  `image_id` int(11) UNSIGNED NOT NULL,
  `listings_id` int(11) UNSIGNED NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listing_images`
--

INSERT INTO `listing_images` (`image_id`, `listings_id`, `image_url`, `is_primary`, `date_created`) VALUES
(1, 1, 'pegasus_41.jpeg', 1, '2025-09-16 02:02:41'),
(2, 1, 'AIR+ZOOM+PEGASUS+41.png', 0, '2025-09-16 02:04:57'),
(3, 1, 'AIR+ZOOM+PEGASUS+41_reflect.png', 0, '2025-09-16 02:04:57'),
(4, 1, 'AIR+ZOOM+PEGASUS+41_pink.png', 0, '2025-09-16 02:04:57');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','on hold','processing','shipped','delivered','completed','cancelled') NOT NULL,
  `shipping_address_id` int(11) UNSIGNED NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_id` int(11) UNSIGNED NOT NULL,
  `listing_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `item_total` decimal(10,2) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `method` enum('cod','paypal') DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recently_viewed`
--

CREATE TABLE `recently_viewed` (
  `recently_viewed_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `listings_id` int(11) UNSIGNED NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `review_id` int(11) UNSIGNED NOT NULL,
  `listing_id` int(11) UNSIGNED NOT NULL,
  `seller_id` int(11) UNSIGNED DEFAULT NULL,
  `review_content` text DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_image`
--

CREATE TABLE `review_image` (
  `review_image_id` int(11) UNSIGNED NOT NULL,
  `review_id` int(11) UNSIGNED NOT NULL,
  `image_file` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) UNSIGNED NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`, `date_created`, `date_modified`) VALUES
(1, 'Admin', '2025-09-14 13:23:02', '2025-09-14 13:23:02'),
(2, 'User', '2025-09-14 13:23:13', '2025-09-14 13:23:13');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_address`
--

CREATE TABLE `shipping_address` (
  `shipping_address_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `type` enum('home','work','pick up') DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `subcategory_id` int(11) UNSIGNED NOT NULL,
  `subcategory_name` varchar(100) NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`subcategory_id`, `subcategory_name`, `category_id`, `date_created`, `date_modified`) VALUES
(1, 'Audio & Video Cables & Converters', 1, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(2, 'Earphones, Headphones & Headsets', 1, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(3, 'Amplifiers & Mixers', 1, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(4, 'Speakers and Karaoke', 1, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(5, 'Home Audio & Speakers', 1, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(6, 'Media Players', 1, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(7, 'Baby Detergent', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(8, 'Babies\' Fashion', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(9, 'Rain Gear', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(10, 'Nursery', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(11, 'Moms & Maternity', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(12, 'Baby Gear', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(13, 'Health & Safety', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(14, 'Bath & Skin Care', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(15, 'Boys\' Fashion', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(16, 'Girls\' Fashion', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(17, 'Feeding & Nursing', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(18, 'Feeding', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(19, 'Diapers & Wipes', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(20, 'Others', 2, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(21, 'Car / Dash Camera', 3, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(22, 'Drones', 3, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(23, 'CCTV / IP Camera', 3, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(24, 'Action Camera', 3, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(25, 'Camera Accessories', 3, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(26, 'Digital Camera', 3, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(27, 'Others', 3, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(28, 'Computer Gaming', 4, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(29, 'Mobile Gaming', 4, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(30, 'Console Gaming', 4, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(31, 'Others', 4, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(32, 'Seasoning, Staple Foods & Baking Ingredients', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(33, 'Gift Set & Hampers', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(34, 'Dairy & Eggs', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(35, 'Cigarettes', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(36, 'Superfoods & Healthy Foods', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(37, 'Breakfast Food', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(38, 'Snack & Sweets', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(39, 'Frozen & Fresh foods', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(40, 'Alcoholic Beverages', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(41, 'Laundry & Household Care', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(42, 'Beverages', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(43, 'Others', 5, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(44, 'Sexual Wellness', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(45, 'Medical Supplies', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(46, 'Men\'s Grooming', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(47, 'Health Supplements', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(48, 'Slimming', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(49, 'Suncare', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(50, 'Whitening', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(51, 'Personal Care', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(52, 'Bath & Body', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(53, 'Hair Care', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(54, 'Skin Care', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(55, 'Others', 6, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(56, 'E-Books', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(57, 'Books and Magazines', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(58, 'Paper Supplies', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(59, 'Writing Materials', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(60, 'Religious Artifacts', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(61, 'Packaging & Wrapping', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(62, 'Arts & Crafts', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(63, 'School & Office Supplies', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(64, 'Musical Instruments', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(65, 'Others', 7, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(66, 'Hand Warmers, Hot Water Bags & Ice Bags', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(67, 'Home Maintenance', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(68, 'Furniture', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(69, 'Lighting', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(70, 'Party Supplies', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(71, 'Beddings', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(72, 'Bath', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(73, 'Glassware & Drinkware', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(74, 'Dinnerware', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(75, 'Bakeware', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(76, 'Kitchenware', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(77, 'Sinkware', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(78, 'Power Tools', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(79, 'Home Improvement', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(80, 'Storage & Organization', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(81, 'Home Decor', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(82, 'Garden Decor', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(83, 'Outdoor & Garden', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(84, 'Others', 8, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(85, 'Small Household Appliances', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(86, 'Home Appliance Parts & Accessories', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(87, 'Large Appliances', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(88, 'Vacuum Cleaners & Floor Care', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(89, 'Humidifier & Air Purifier', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(90, 'Cooling & Heating', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(91, 'Specialty Appliances', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(92, 'Small kitchen Appliances', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(93, 'Garment Care', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(94, 'Others', 9, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(95, 'Projectors', 10, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(96, 'TV Accessories', 10, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(97, 'Television', 10, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(98, 'Others', 10, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(99, 'USB Gadgets', 11, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(100, 'Computer Hardware', 11, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(101, 'Software', 11, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(102, 'Printers and Inks', 11, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(103, 'Storage', 11, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(104, 'Computer Accessories', 11, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(105, 'Network Components', 11, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(106, 'Laptops and Desktops', 11, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(107, 'Others', 11, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(108, 'Palettes & Makeup Sets', 12, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(109, 'Tools & Accessories', 12, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(110, 'Nails', 12, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(111, 'Fragrances', 12, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(112, 'Face Makeup', 12, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(113, 'Lip Makeup', 12, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(114, 'Eye Makeup', 12, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(115, 'Others', 12, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(116, 'Tops', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(117, 'Shorts', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(118, 'Pants', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(119, 'Jeans', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(120, 'Underwear', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(121, 'Socks', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(122, 'Hoodies & Sweatshirts', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(123, 'Jackets & Sweaters', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(124, 'Sleepwear', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(125, 'Suits', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(126, 'Sets', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(127, 'Occupational Attire', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(128, 'Traditional Wear', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(129, 'Costumes', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(130, 'Others', 13, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(131, 'Hats & Caps', 14, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(132, 'Wallets', 14, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(133, 'Eyewear', 14, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(134, 'Accessories', 14, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(135, 'Jewelry', 14, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(136, 'Watches', 14, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(137, 'Men\'s Bags', 14, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(138, 'Accessories Sets & Packages', 14, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(139, 'Loafer & Boat Shoes', 15, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(140, 'Sneakers', 15, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(141, 'Sandals & Flip Flops', 15, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(142, 'Boots', 15, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(143, 'Formal', 15, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(144, 'Shoe Care & Accessories', 15, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(145, 'Others', 15, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(146, 'Portable Audio', 16, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(147, 'Wearables', 16, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(148, 'E-Cigarettes', 16, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(149, 'Tablets', 16, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(150, 'Mobiles', 16, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(151, 'Others Mobile Accessories', 17, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(152, 'Attachments', 17, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(153, 'Cases & Covers', 17, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(154, 'Powerbanks & Chargers', 17, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(155, 'Car Care & Detailing', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(156, 'Automotive Parts', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(157, 'Engine Parts', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(158, 'Ignition', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(159, 'Exterior Car Accessories', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(160, 'Oils, Coolants, & Fluids', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(161, 'Car Electronics', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(162, 'Moto Riding & Protective Gear', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(163, 'Tools & Garage', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(164, 'Motorcycle Accessories', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(165, 'Motorcycle & ATV Parts', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(166, 'Interior Car Accessories', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(167, 'Others', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(168, 'Motorcycles', 18, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(169, 'Toys & Accessories', 19, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(170, 'Litter & Toilet', 19, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(171, 'Pet Essentials', 19, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(172, 'Pet Clothing & Accessories', 19, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(173, 'Pet Grooming Supplies', 19, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(174, 'Pet Toys & Accessories', 19, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(175, 'Pet Food & Treats', 19, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(176, 'Others', 19, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(177, 'Travel Bags', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(178, 'Travel Accessories', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(179, 'Travel Organizer', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(180, 'Kid\'s Activewear', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(181, 'Boxing & MMA', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(182, 'Weather Protection', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(183, 'WinterSports Gear', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(184, 'Outdoor Recreation', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(185, 'Leisure Sports & Game Room', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(186, 'Golf', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(187, 'Racket Sports', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(188, 'Sports Bags', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(189, 'Women\'s Activewear', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(190, 'Men\'s Activewear', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(191, 'Cycling, Skates & Scooters', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(192, 'Team Sports', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(193, 'Water Sports', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(194, 'Camping & Hiking', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(195, 'Weightlifting', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(196, 'Fitness Accessory', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(197, 'Yoga', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(198, 'Exercise & Fitness', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(199, 'Others', 20, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(200, 'Celebrity Merchandise', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(201, 'Dress Up & Pretend', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(202, 'Blasters & Toy Guns', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(203, 'Sports & Outdoor Toys', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(204, 'Dolls', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(205, 'Educational Toys', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(206, 'Electronic Toys', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(207, 'Boards & Family Games', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(208, 'Collectibles', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(209, 'Character', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(210, 'Action Figure', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(211, 'Others', 21, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(212, 'Jewelry', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(213, 'Watches', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(214, 'Hair Accessories', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(215, 'Eyewear', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(216, 'Wallets & Pouches', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(217, 'Hats & Caps', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(218, 'Belts & Scarves', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(219, 'Gloves', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(220, 'Accessories Sets & Packages', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(221, 'Additional Accessories', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(222, 'Watch & Jewelry Organizers', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(223, 'Others', 22, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(224, 'Skirts', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(225, 'Jumpsuits & Rompers', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(226, 'Lingerie & Nightwear', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(227, 'Sets', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(228, 'Swimsuit', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(229, 'Jackets & Outerwear', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(230, 'Plus Size', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(231, 'Sweater & Cardigans', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(232, 'Maternity Wear', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(233, 'Socks & Stockings', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(234, 'Costumes', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(235, 'Traditional Wear', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(236, 'Fabric', 23, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(237, 'Shoulder Bags', 24, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(238, 'Tote Bags', 24, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(239, 'Handbags', 24, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(240, 'Clutches', 24, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(241, 'Backpacks', 24, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(242, 'Drawstrings', 24, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(243, 'Accessories', 24, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(244, 'Others', 24, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(245, 'Flats', 25, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(246, 'Heels', 25, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(247, 'Flip Flops', 25, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(248, 'Sneakers', 25, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(249, 'Wedges & Platforms', 25, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(250, 'Boots', 25, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(251, 'Shoe Care & Accessories', 25, '2025-09-14 13:21:27', '2025-09-14 13:21:27'),
(252, 'Others', 25, '2025-09-14 13:21:27', '2025-09-14 13:21:27');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `user_account_id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `civil_status` enum('single','married') DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_account_id`, `first_name`, `middle_name`, `last_name`, `mobile`, `gender`, `dob`, `civil_status`, `date_created`, `date_modified`, `avatar`) VALUES
(1, 1, 'Kim Test', NULL, 'Valencia', '09355891759', 'male', '1999-07-18', 'single', '2025-09-14 13:28:52', '2025-09-14 13:41:02', NULL),
(2, 2, 'Ben', NULL, '10', '09355891759', 'male', '1999-03-30', 'single', '2025-09-14 13:31:08', '2025-09-14 13:41:08', '1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `user_account_id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) UNSIGNED NOT NULL,
  `account_status` enum('active','inactive') NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_account_id`, `email`, `password`, `role_id`, `account_status`, `date_created`, `date_modified`) VALUES
(1, 'kimo0rven@gmail.com', '123', 1, 'active', '2025-09-14 13:25:46', '2025-09-14 13:25:46'),
(2, 'test@mail.com', '123', 2, 'active', '2025-09-14 13:29:18', '2025-09-14 13:29:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookmark`
--
ALTER TABLE `bookmark`
  ADD PRIMARY KEY (`bookmark_id`),
  ADD UNIQUE KEY `unique_bookmark` (`user_id`,`listings_id`),
  ADD KEY `fk_bookmark_listing` (`listings_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`followed_id`),
  ADD KEY `followed_id` (`followed_id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`listings_id`),
  ADD KEY `fk_listings_owner` (`listing_owner_id`),
  ADD KEY `fk_listings_category` (`subcategory_id`);

--
-- Indexes for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `fk_listing_images_listing` (`listings_id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_order_user` (`user_id`),
  ADD KEY `fk_order_shipping_address` (`shipping_address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_id`,`listing_id`),
  ADD KEY `fk_order_items_listings` (`listing_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payment_order` (`order_id`);

--
-- Indexes for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD PRIMARY KEY (`recently_viewed_id`),
  ADD KEY `idx_recently_viewed_user` (`user_id`),
  ADD KEY `idx_recently_viewed_listing` (`listings_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_review_listings` (`listing_id`),
  ADD KEY `fk_reviews_seller` (`seller_id`);

--
-- Indexes for table `review_image`
--
ALTER TABLE `review_image`
  ADD PRIMARY KEY (`review_image_id`),
  ADD KEY `fk_review_image_review` (`review_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `shipping_address`
--
ALTER TABLE `shipping_address`
  ADD PRIMARY KEY (`shipping_address_id`),
  ADD KEY `fk_shipping_address_user` (`user_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`subcategory_id`),
  ADD KEY `fk_subcategories_category` (`category_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_account_id` (`user_account_id`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_account_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_account_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmark`
--
ALTER TABLE `bookmark`
  MODIFY `bookmark_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `follow`
--
ALTER TABLE `follow`
  MODIFY `follow_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `listings_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `listing_images`
--
ALTER TABLE `listing_images`
  MODIFY `image_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `order_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  MODIFY `recently_viewed_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `review_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_image`
--
ALTER TABLE `review_image`
  MODIFY `review_image_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shipping_address`
--
ALTER TABLE `shipping_address`
  MODIFY `shipping_address_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_account_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmark`
--
ALTER TABLE `bookmark`
  ADD CONSTRAINT `fk_bookmark_listing` FOREIGN KEY (`listings_id`) REFERENCES `listings` (`listings_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookmark_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `fk_follow_followed` FOREIGN KEY (`followed_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_follow_follower` FOREIGN KEY (`follower_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `fk_listings_category` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_listings_owner` FOREIGN KEY (`listing_owner_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD CONSTRAINT `fk_listing_images_listing` FOREIGN KEY (`listings_id`) REFERENCES `listings` (`listings_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `fk_order_shipping_address` FOREIGN KEY (`shipping_address_id`) REFERENCES `shipping_address` (`shipping_address_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_listings` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listings_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD CONSTRAINT `fk_recently_viewed_listing` FOREIGN KEY (`listings_id`) REFERENCES `listings` (`listings_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recently_viewed_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `fk_review_listings` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listings_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_seller` FOREIGN KEY (`seller_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `review_image`
--
ALTER TABLE `review_image`
  ADD CONSTRAINT `fk_review_image_review` FOREIGN KEY (`review_id`) REFERENCES `review` (`review_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shipping_address`
--
ALTER TABLE `shipping_address`
  ADD CONSTRAINT `fk_shipping_address_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `fk_subcategories_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_user_account` FOREIGN KEY (`user_account_id`) REFERENCES `user_account` (`user_account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_account`
--
ALTER TABLE `user_account`
  ADD CONSTRAINT `fk_user_account_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
