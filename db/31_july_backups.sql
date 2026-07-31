-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 31, 2026 at 12:41 PM
-- Server version: 9.7.1
-- PHP Version: 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crms`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-crm.settings.all', 'a:19:{s:12:\"company_name\";s:10:\"Oracle CRM\";s:13:\"company_email\";s:20:\"info@oraclegmail.com\";s:13:\"company_phone\";s:12:\"+91980624794\";s:15:\"company_website\";s:26:\"https://www.w3schools.com/\";s:15:\"company_address\";s:43:\"Sirsa Gate Square, 3, GE Road, Bajrang para\";s:8:\"timezone\";s:12:\"Asia/Kolkata\";s:11:\"date_format\";s:5:\"d-m-Y\";s:11:\"time_format\";s:5:\"h:i A\";s:13:\"currency_code\";s:3:\"INR\";s:15:\"currency_symbol\";s:3:\"₹\";s:12:\"company_logo\";s:0:\"\";s:7:\"favicon\";s:0:\"\";s:13:\"primary_color\";s:7:\"#2543DA\";s:15:\"secondary_color\";s:7:\"#0F172A\";s:17:\"show_company_logo\";s:1:\"1\";s:16:\"sidebar_subtitle\";s:11:\"Admin Panel\";s:11:\"footer_text\";s:18:\"Powered by PRO CRM\";s:13:\"login_heading\";s:19:\"Welcome Back, Admin\";s:17:\"login_description\";s:96:\"Login to manage your leads, clients, projects, tasks and CRM settings from one secure dashboard.\";}', 2100852885);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `lead_id`, `name`, `phone`, `email`, `company`, `status`, `assigned_to`, `created_by`, `notes`, `created_at`, `updated_at`) VALUES
(2, 10, 'Manish Sahu', '9893456712', 'manish.sahu@example.com', 'Sahu Automobiles', 'inactive', NULL, 14, 'Visited office and discussed automobile service management software.', '2026-07-17 03:45:55', '2026-07-17 03:59:10'),
(3, NULL, 'pankaj tripathi', '1122335566', 'tripathi@gmail.com', 'Lavanya Enterprises', 'active', 15, 1, 'demo video share.', '2026-07-17 04:14:15', '2026-07-17 04:14:15'),
(5, 8, 'Rohit Jain', '9301234567', 'rohit.jain@example.com', 'Jain Traders', 'active', NULL, 1, 'Called regarding billing and inventory management software.', '2026-07-17 12:03:31', '2026-07-17 12:03:31'),
(6, 12, 'Vikas Yadav', '7999123456', 'vikas.yadav@example.com', 'Yadav Construction', 'active', NULL, 1, 'Requires project and workforce management software for construction work.', '2026-07-18 10:46:19', '2026-07-18 10:46:19'),
(7, 6, 'Amit Patel', '9988776655', 'amit.patel@example.com', 'Patel Hardware Solutions', 'active', NULL, 35, 'Referred by an existing client. Interested in CRM and employee task management.', '2026-07-20 09:38:49', '2026-07-20 09:38:49'),
(8, 13, 'Anjali Mishra', '8817123456', 'anjali.mishra@example.com', 'Mishra Boutique', 'active', NULL, 1, 'Interested in a business website and WhatsApp product catalogue automation.', '2026-07-20 09:39:27', '2026-07-20 09:39:27'),
(9, 24, 'Rahul Sharma', '9876543210', 'rahul.project@example.com', 'Rahul Enterprises', 'active', 35, 34, 'Client needs a professional business landing page.', '2026-07-20 12:49:13', '2026-07-20 12:54:05'),
(10, 4, 'Rahul Sharma', '9876543210', 'rahul.sharma@example.com', 'Sharma Enterprises', 'active', 14, 1, 'Interested in CRM software for lead tracking and customer follow-ups. Requested a product demo.', '2026-07-21 07:51:45', '2026-07-21 07:51:45'),
(11, 5, 'Priya Verma', '9123456780', 'priya.verma@example.com', 'Verma Fashion Hub', 'active', 14, 1, 'Needs a WhatsApp automation solution urgently. Initial discussion has been completed.', '2026-07-21 12:19:05', '2026-07-21 12:19:05'),
(14, NULL, 'Kumar Modui', '9827012345', 'amit.kumar@example.com', 'AK Solutions', 'on_hold', NULL, 1, 'Client discussion pending; follow up next week.', '2026-07-29 07:18:51', '2026-07-29 07:18:51');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `priority` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `next_follow_up_at` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `converted_at` datetime DEFAULT NULL,
  `converted_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `name`, `phone`, `email`, `company`, `source`, `status`, `priority`, `assigned_to`, `created_by`, `next_follow_up_at`, `notes`, `converted_at`, `converted_by`, `created_at`, `updated_at`) VALUES
(1, 'Rahul Sharma', '9876543210', 'rahul.sharma@example.com', 'Sharma Enterprises', 'website', 'new', 'medium', 14, 1, '2026-07-20 10:30:00', 'Interested in CRM software for managing sales leads and customer follow-ups. Requested a product demo and pricing details.', NULL, NULL, '2026-07-16 04:34:21', '2026-07-16 04:34:21'),
(2, 'Priya Verma', '9123456780', 'priya.verma@example.com', 'Verma Fashion Hub', 'whatsapp', 'follow_up', 'urgent', 15, 1, '2026-07-21 10:48:00', 'Customer contacted through WhatsApp and needs a business automation solution urgently. Initial discussion completed; follow-up required for final quotation.', NULL, NULL, '2026-07-16 04:44:08', '2026-07-16 04:46:09'),
(3, 'Amit Patel', '9988776655', 'amit.patel@example.com', 'Patel Hardware Solutions', 'referral', 'qualified', 'high', 14, 1, '2026-07-16 17:35:00', 'Referred by an existing client. Interested in lead management, WhatsApp automation and employee task tracking. Budget and requirements have been discussed.', NULL, NULL, '2026-07-16 04:45:45', '2026-07-16 04:45:45'),
(4, 'Rahul Sharma', '9876543210', 'rahul.sharma@example.com', 'Sharma Enterprises', 'website', 'converted', 'medium', 14, NULL, NULL, 'Interested in CRM software for lead tracking and customer follow-ups. Requested a product demo.', '2026-07-21 13:21:45', 1, '2026-07-16 10:19:31', '2026-07-21 07:51:45'),
(5, 'Priya Verma', '9123456780', 'priya.verma@example.com', 'Verma Fashion Hub', 'whatsapp', 'converted', 'urgent', 14, NULL, NULL, 'Needs a WhatsApp automation solution urgently. Initial discussion has been completed.', '2026-07-21 17:49:05', 1, '2026-07-16 10:19:31', '2026-07-21 12:19:05'),
(6, 'Amit Patel', '9988776655', 'amit.patel@example.com', 'Patel Hardware Solutions', 'referral', 'converted', 'high', NULL, NULL, NULL, 'Referred by an existing client. Interested in CRM and employee task management.', '2026-07-20 15:08:49', 35, '2026-07-16 10:19:31', '2026-07-20 09:38:49'),
(7, 'Sneha Gupta', '9826012345', 'sneha.gupta@example.com', 'Gupta Consultancy', 'google', 'contacted', 'medium', NULL, NULL, '2026-07-23 14:55:00', 'Looking for a custom web application for managing consultancy clients.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-17 03:56:20'),
(8, 'Rohit Jain', '9301234567', 'rohit.jain@example.com', 'Jain Traders', 'phone_call', 'converted', 'low', NULL, NULL, NULL, 'Called regarding billing and inventory management software.', '2026-07-17 17:33:31', 1, '2026-07-16 10:19:31', '2026-07-17 12:03:31'),
(9, 'Neha Singh', '9753214680', 'neha.singh@example.com', 'Singh Education Academy', 'facebook', 'follow_up', 'high', NULL, NULL, '2026-07-18 16:00:00', 'Interested in student management software and automated fee reminders.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(10, 'Manish Sahu', '9893456712', 'manish.sahu@example.com', 'Sahu Automobiles', 'walk_in', 'converted', 'urgent', NULL, NULL, NULL, 'Visited office and discussed automobile service management software.', '2026-07-17 09:15:55', 14, '2026-07-16 10:19:31', '2026-07-17 03:45:55'),
(11, 'Pooja Agrawal', '9425512346', 'pooja.agrawal@example.com', 'Agrawal Foods', 'instagram', 'new', 'medium', NULL, NULL, '2026-07-22 11:00:00', 'Asked about website development and social media lead integration.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(12, 'Vikas Yadav', '7999123456', 'vikas.yadav@example.com', 'Yadav Construction', 'website', 'converted', 'high', NULL, NULL, NULL, 'Requires project and workforce management software for construction work.', '2026-07-18 16:16:19', 1, '2026-07-16 10:19:31', '2026-07-18 10:46:19'),
(13, 'Anjali Mishra', '8817123456', 'anjali.mishra@example.com', 'Mishra Boutique', 'whatsapp', 'converted', 'low', NULL, NULL, NULL, 'Interested in a business website and WhatsApp product catalogue automation.', '2026-07-20 15:09:27', 1, '2026-07-16 10:19:31', '2026-07-20 09:39:27'),
(14, 'Suresh Chandrakar', '9827123489', 'suresh.chandrakar@example.com', 'Chandrakar Logistics', 'referral', 'contacted', 'high', NULL, NULL, '2026-07-20 16:30:00', 'Needs logistics tracking software and automated customer notifications.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 05:13:54'),
(15, 'Kavita Tiwari', '9302456781', 'kavita.tiwari@example.com', 'Tiwari Healthcare', 'google', 'qualified', 'medium', NULL, NULL, '2026-07-21 13:00:00', 'Interested in patient appointment and clinic management software.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(16, 'Deepak Thakur', '9755123467', 'deepak.thakur@example.com', 'Thakur Electronics', 'facebook', 'lost', 'low', NULL, NULL, NULL, 'Customer postponed the project due to budget limitations.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(17, 'Nisha Dewangan', '6263123456', 'nisha.dewangan@example.com', 'Dewangan Interior Studio', 'instagram', 'contacted', 'medium', NULL, NULL, '2026-07-24 12:00:00', 'Needs a portfolio website and lead enquiry management system.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(18, 'Arjun Mehta', '7987456123', 'arjun.mehta@example.com', 'Mehta Financial Services', 'phone_call', 'converted', 'high', NULL, NULL, NULL, 'Lead agreed to proceed with CRM implementation and reporting dashboard.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(19, 'Ritu Bansal', '9098123456', 'ritu.bansal@example.com', 'Bansal Event Management', 'website', 'follow_up', 'urgent', NULL, NULL, '2026-07-18 17:00:00', 'Requested event registration software with payment and attendee management.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(20, 'Mohit Kosle', '9340123456', 'mohit.kosle@example.com', 'Kosle Agro Industries', 'walk_in', 'new', 'medium', NULL, NULL, '2026-07-25 11:30:00', 'Interested in inventory and distributor management software.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(21, 'Shweta Rao', '7771012345', 'shweta.rao@example.com', 'Rao Beauty Studio', 'whatsapp', 'contacted', 'low', NULL, NULL, '2026-07-22 14:00:00', 'Asked about appointment booking website and automated WhatsApp reminders.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(22, 'Rajesh Netam', '8962123456', 'rajesh.netam@example.com', 'Netam Security Services', 'referral', 'qualified', 'high', NULL, NULL, '2026-07-19 12:00:00', 'Requires employee attendance, duty scheduling and client billing system.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(23, 'Komal Nair', '8085123456', 'komal.nair@example.com', 'Nair Travel Solutions', 'google', 'new', 'medium', NULL, NULL, '2026-07-26 10:00:00', 'Interested in travel enquiry management, quotation and follow-up automation.', NULL, NULL, '2026-07-16 10:19:31', '2026-07-16 10:19:31'),
(24, 'Rahul Sharma', '9876543210', 'rahul.project@example.com', 'Rahul Enterprises', 'website', 'converted', 'high', 34, 34, NULL, 'Client needs a professional business landing page.', '2026-07-20 18:19:13', 34, '2026-07-20 12:18:43', '2026-07-20 12:49:13'),
(25, 'Amit Verma', '9876501001', 'amit.verma@example.com', 'Verma Enterprises', 'whatsapp', 'new', 'high', NULL, 1, '2026-07-30 11:00:00', 'Interested in CRM demo for a 6-member sales team.', NULL, NULL, '2026-07-28 12:20:04', '2026-07-28 12:20:04'),
(26, 'Neha Sharma', '9876501002', 'neha.sharma@example.com', 'Sharma Interiors', 'instagram', 'contacted', 'medium', NULL, 1, '2026-07-31 15:30:00', 'Shared pricing details; follow up after internal discussion.', NULL, NULL, '2026-07-28 12:20:04', '2026-07-28 12:20:04'),
(27, 'Rajesh Patel', '9876501003', 'rajesh.patel@example.com', 'Patel Realty', 'facebook', 'follow_up', 'urgent', NULL, 1, '2026-07-29 10:00:00', 'Needs lead tracking and WhatsApp follow-up automation.', NULL, NULL, '2026-07-28 12:20:04', '2026-07-28 12:20:04'),
(28, 'Priya Singh', '9876501004', 'priya.singh@example.com', 'Singh Financial Services', 'referral', 'qualified', 'high', NULL, 1, '2026-08-01 12:00:00', 'Qualified lead; requested final proposal and onboarding timeline.', NULL, NULL, '2026-07-28 12:20:04', '2026-07-28 12:20:04'),
(29, 'Imran Khan', '9876501005', 'imran.khan@example.com', 'Khan Auto Parts', 'google', 'new', 'low', NULL, 1, '2026-08-02 16:00:00', 'Website inquiry for basic CRM and customer follow-up.', NULL, NULL, '2026-07-28 12:20:04', '2026-07-28 12:20:04'),
(30, 'Kavita Joshi', '9876501006', 'kavita.joshi@example.com', 'Joshi Education Hub', 'website', 'contacted', 'medium', NULL, 1, '2026-08-03 11:30:00', 'Looking for inquiry management for training admissions.', NULL, NULL, '2026-07-28 12:20:04', '2026-07-28 12:20:04'),
(31, 'Sandeep Yadav', '9876501007', 'sandeep.yadav@example.com', 'Yadav Distributors', 'phone_call', 'follow_up', 'high', NULL, 1, '2026-07-30 14:00:00', 'Follow up regarding multi-user access and reporting features.', NULL, NULL, '2026-07-28 12:20:04', '2026-07-28 12:20:04'),
(32, 'Manish Gupta', '9876501009', 'manish.gupta@example.com', 'Gupta Traders', 'other', 'new', 'medium', NULL, 1, NULL, 'Not interested currently; may reconnect after three months.', NULL, NULL, '2026-07-28 12:20:04', '2026-07-30 12:47:40'),
(33, 'Ritu Mehta', '9876501010', 'ritu.mehta@example.com', 'Mehta Digital Solutions', 'whatsapp', 'new', 'medium', NULL, 1, '2026-07-30 11:00:00', 'Asked for CRM features, pricing, and WhatsApp integration details.', NULL, NULL, '2026-07-28 12:20:04', '2026-07-30 13:32:00');

-- --------------------------------------------------------

--
-- Table structure for table `lead_follow_ups`
--

CREATE TABLE `lead_follow_ups` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `followed_up_at` datetime NOT NULL,
  `outcome` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `next_follow_up_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_follow_ups`
--

INSERT INTO `lead_follow_ups` (`id`, `lead_id`, `user_id`, `type`, `followed_up_at`, `outcome`, `notes`, `next_follow_up_at`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'call', '2026-07-17 08:17:00', 'interested', 'test lead.', '2026-07-20 13:49:00', '2026-07-17 02:49:58', '2026-07-17 02:49:58'),
(2, 6, 14, 'other', '2026-07-17 08:25:21', 'converted', 'Lead converted into client.', NULL, '2026-07-17 02:55:21', '2026-07-17 02:55:21'),
(3, 10, 14, 'other', '2026-07-17 09:15:55', 'converted', 'Lead converted into client.', NULL, '2026-07-17 03:45:55', '2026-07-17 03:45:55'),
(4, 7, 14, 'whatsapp', '2026-07-17 09:25:00', 'interested', 'demo video share.', '2026-07-23 14:55:00', '2026-07-17 03:56:20', '2026-07-17 03:56:20'),
(5, 6, 1, 'other', '2026-07-17 10:08:03', 'converted', 'Lead converted into client.', NULL, '2026-07-17 04:38:03', '2026-07-17 04:38:03'),
(6, 8, 1, 'other', '2026-07-17 17:33:31', 'converted', 'Lead converted into client.', NULL, '2026-07-17 12:03:31', '2026-07-17 12:03:31'),
(7, 12, 1, 'call', '2026-07-18 16:16:00', 'interested', 'test', NULL, '2026-07-18 10:46:13', '2026-07-18 10:46:13'),
(8, 12, 1, 'other', '2026-07-18 16:16:19', 'converted', 'Lead converted into client.', NULL, '2026-07-18 10:46:19', '2026-07-18 10:46:19'),
(9, 6, 35, 'other', '2026-07-20 15:08:49', 'converted', 'Lead converted into client.', NULL, '2026-07-20 09:38:49', '2026-07-20 09:38:49'),
(10, 13, 1, 'other', '2026-07-20 15:09:27', 'converted', 'Lead converted into client.', NULL, '2026-07-20 09:39:27', '2026-07-20 09:39:27'),
(11, 24, 34, 'call', '2026-07-20 17:50:00', 'interested', 'Client wants a responsive landing page with lead enquiry form.', '2026-07-21 09:50:00', '2026-07-20 12:20:47', '2026-07-20 12:20:47'),
(12, 24, 34, 'other', '2026-07-20 18:19:13', 'converted', 'Lead converted into client.', NULL, '2026-07-20 12:49:13', '2026-07-20 12:49:13'),
(13, 4, 1, 'other', '2026-07-21 13:21:45', 'converted', 'Lead converted into client.', NULL, '2026-07-21 07:51:45', '2026-07-21 07:51:45'),
(14, 5, 1, 'other', '2026-07-21 17:49:05', 'converted', 'Lead converted into client.', NULL, '2026-07-21 12:19:05', '2026-07-21 12:19:05'),
(15, 33, 1, 'call', '2026-07-29 16:30:00', 'interested', 'CRM requirements discussed. Lead requested pricing details and a follow-up call.', '2026-07-30 11:00:00', '2026-07-29 12:12:23', '2026-07-29 12:12:23');

-- --------------------------------------------------------

--
-- Table structure for table `lead_priorities`
--

CREATE TABLE `lead_priorities` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#64748B',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_priorities`
--

INSERT INTO `lead_priorities` (`id`, `name`, `slug`, `color`, `is_default`, `is_active`, `is_system`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Low', 'low', '#64748B', 0, 1, 0, 10, '2026-07-30 12:37:23', '2026-07-30 12:37:23'),
(2, 'Medium', 'medium', '#2563EB', 1, 1, 1, 20, '2026-07-30 12:37:23', '2026-07-30 12:37:23'),
(3, 'High', 'high', '#EA580C', 0, 1, 0, 30, '2026-07-30 12:37:23', '2026-07-30 12:37:23'),
(4, 'Urgent', 'urgent', '#DC2626', 0, 1, 0, 40, '2026-07-30 12:37:23', '2026-07-30 12:37:23');

-- --------------------------------------------------------

--
-- Table structure for table `lead_statuses`
--

CREATE TABLE `lead_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `system_key` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#64748B',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_statuses`
--

INSERT INTO `lead_statuses` (`id`, `name`, `slug`, `system_key`, `color`, `is_default`, `is_active`, `is_closed`, `is_system`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'News', 'new', 'new', '#2563EB', 1, 1, 0, 1, 10, '2026-07-30 12:37:23', '2026-07-30 13:07:57'),
(2, 'Contacted', 'contacted', NULL, '#7C3AED', 0, 1, 0, 0, 20, '2026-07-30 12:37:23', '2026-07-30 13:07:57'),
(3, 'Follow-up', 'follow_up', NULL, '#CA8A04', 0, 1, 0, 0, 30, '2026-07-30 12:37:23', '2026-07-30 13:07:57'),
(4, 'Qualified', 'qualified', 'qualified', '#16A34A', 0, 1, 0, 1, 40, '2026-07-30 12:37:23', '2026-07-30 13:07:57'),
(5, 'Converted', 'converted', 'converted', '#059669', 0, 1, 1, 1, 50, '2026-07-30 12:37:23', '2026-07-30 13:07:57'),
(6, 'Lost', 'lost', 'lost', '#DC2626', 0, 1, 1, 1, 60, '2026-07-30 12:37:23', '2026-07-30 13:07:57');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_10_095943_create_roles_table', 2),
(5, '2026_07_10_100128_add_role_id_to_users_table', 2),
(6, '2026_07_10_124528_create_permissions_table', 3),
(7, '2026_07_10_124608_create_permission_role_table', 3),
(8, '2026_07_11_112234_add_is_active_to_users_table', 4),
(9, '2026_07_16_090528_create_leads_table', 5),
(10, '2026_07_16_125802_create_clients_table', 6),
(11, '2026_07_16_125857_create_lead_follow_ups_table', 6),
(12, '2026_07_16_125931_add_conversion_fields_to_leads_table', 6),
(13, '2026_07_18_122329_create_projects_table', 7),
(14, '2026_07_18_123639_create_project_members_table', 7),
(15, '2026_07_18_124008_create_project_services_table', 7),
(16, '2026_07_18_124017_create_tasks_table', 7),
(17, '2026_07_18_124025_create_task_comments_table', 7),
(18, '2026_07_18_124035_create_task_attachments_table', 7),
(19, '2026_07_18_124043_create_project_activities_table', 7),
(20, '2026_07_20_162517_create_task_dependencies_table', 8),
(21, '2026_07_21_154423_create_notifications_table', 9),
(22, '2026_07_25_150510_create_time_entries_table', 10),
(23, '2026_07_25_150532_create_time_entry_breaks_table', 10),
(24, '2026_07_30_150322_create_settings_table', 11),
(25, '2026_07_30_164147_create_lead_statuses_and_priorities_tables', 12),
(26, '2026_07_31_123008_create_task_statuses_and_priorities_tables', 13);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('00ad28f5-ed99-488f-bfd5-9a51828aea86', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"design A\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/13\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":13,\"project_id\":4}', '2026-07-22 08:31:17', '2026-07-22 08:31:00', '2026-07-22 08:31:17'),
('0f3e90db-fab8-4692-900e-cc36803076bf', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"23eee\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/20\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":20,\"project_id\":4}', '2026-07-23 10:39:59', '2026-07-23 10:39:47', '2026-07-23 10:39:59'),
('1dd5fc01-4987-432c-bf5c-38c88c08e451', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"task xt\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/16\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":16,\"project_id\":4}', '2026-07-23 09:51:15', '2026-07-23 09:51:04', '2026-07-23 09:51:15'),
('239f0255-7cb8-47bd-9e4a-e48699a63c4c', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"check all commands\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/15\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":15,\"project_id\":4}', '2026-07-23 06:07:40', '2026-07-23 06:07:17', '2026-07-23 06:07:40'),
('246e874c-2de0-4d23-ad39-5ec7f58c0d46', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"task 55\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/18\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":18,\"project_id\":4}', '2026-07-23 09:53:14', '2026-07-23 09:52:43', '2026-07-23 09:53:14'),
('5a1684ac-bb70-42bd-865b-71ca717a9dd0', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_completed\",\"title\":\"Task Completed\",\"message\":\"Amisha completed task \\\"check all commands\\\".\",\"url\":\"\\/task\\/15\",\"icon\":\"\\u2705\",\"level\":\"success\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":15,\"project_id\":4}', '2026-07-25 11:41:18', '2026-07-23 10:23:32', '2026-07-25 11:41:18'),
('5f1f1019-c6c7-473e-888d-cac5798777c4', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_approved\",\"title\":\"Task Approved\",\"message\":\"Your task \\\"task B\\\" has been approved and completed.\",\"url\":\"\\/task\\/14\",\"icon\":\"\\ud83c\\udf89\",\"level\":\"success\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":14,\"project_id\":4}', '2026-07-23 06:07:37', '2026-07-23 06:06:43', '2026-07-23 06:07:37'),
('606a6611-01e4-4b05-b634-269c796ee1e1', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_approved\",\"title\":\"Task Approved\",\"message\":\"Your task \\\"design A_editing\\\" has been approved and completed.\",\"url\":\"\\/task\\/13\",\"icon\":\"\\ud83c\\udf89\",\"level\":\"success\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":13,\"project_id\":4}', '2026-07-25 12:44:35', '2026-07-25 12:19:47', '2026-07-25 12:44:35'),
('61e09eee-7094-438b-82f7-6ae1cd74b124', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"project_assigned\",\"title\":\"Project Assigned\",\"message\":\"You have been assigned as Project Manager for PRJ-2026-0004 \\u2014 Project Phoenix22.\",\"url\":\"\\/project\\/4\",\"icon\":\"\\ud83d\\udcc1\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"project_id\":4}', '2026-07-21 12:42:02', '2026-07-21 12:19:27', '2026-07-21 12:42:02'),
('6ece1be9-a717-43c4-9c1c-0c0c6237453a', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"task 5\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/12\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":12,\"project_id\":4}', '2026-07-21 13:12:07', '2026-07-21 13:11:36', '2026-07-21 13:12:07'),
('6f8f19e8-5253-45e1-9dea-5aab651a01b2', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"qqqq\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/19\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":19,\"project_id\":4}', '2026-07-25 11:41:14', '2026-07-23 10:38:48', '2026-07-25 11:41:14'),
('971a11f8-d58b-4381-8c12-f99a903de5e8', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_approved\",\"title\":\"Task Approved\",\"message\":\"Your task \\\"task A\\\" has been approved and completed.\",\"url\":\"\\/task\\/10\",\"icon\":\"\\ud83c\\udf89\",\"level\":\"success\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":10,\"project_id\":4}', '2026-07-21 13:11:21', '2026-07-21 13:07:02', '2026-07-21 13:11:21'),
('aad51761-f929-447a-9b76-a7c989987c89', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"task 34\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/17\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":17,\"project_id\":4}', '2026-07-23 09:53:11', '2026-07-23 09:51:58', '2026-07-23 09:53:11'),
('beb863ee-840f-4198-b28c-f757d5455b47', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_approved\",\"title\":\"Task Approved\",\"message\":\"Your task \\\"qqqq\\\" has been approved and completed.\",\"url\":\"\\/task\\/19\",\"icon\":\"\\ud83c\\udf89\",\"level\":\"success\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":19,\"project_id\":4}', '2026-07-25 12:44:37', '2026-07-25 11:58:59', '2026-07-25 12:44:37'),
('d6ac3b43-20e4-4710-9123-62e6d7d87f6e', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"task B\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/14\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":14,\"project_id\":4}', '2026-07-23 06:06:13', '2026-07-22 08:31:58', '2026-07-23 06:06:13'),
('e67b948e-9986-4692-b93d-5eb747b7b633', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"task A\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/10\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":10,\"project_id\":4}', '2026-07-21 12:41:57', '2026-07-21 12:19:58', '2026-07-21 12:41:57'),
('ed83a28c-f577-4dd3-9849-83376ea573f4', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"task 24\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/21\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":21,\"project_id\":4}', '2026-07-25 11:30:36', '2026-07-25 11:28:17', '2026-07-25 11:30:36'),
('f7256c4f-6075-43e2-bce8-5755f0790e10', 'crm-notification', 'App\\Modules\\User\\Models\\User', 37, '{\"kind\":\"task_assigned\",\"title\":\"New Task Assigned\",\"message\":\"You have been assigned task \\\"task B\\\" in project PRJ-2026-0004.\",\"url\":\"\\/task\\/11\",\"icon\":\"\\u2705\",\"level\":\"info\",\"event_key\":null,\"actor_id\":1,\"actor_name\":\"Admin\",\"task_id\":11,\"project_id\":4}', '2026-07-21 13:11:19', '2026-07-21 13:10:39', '2026-07-21 13:11:19');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `group`, `created_at`, `updated_at`) VALUES
(1, 'View Dashboard', 'dashboard.view', 'Dashboard', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(2, 'View Users', 'users.view', 'User Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(3, 'Create Users', 'users.create', 'User Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(4, 'Edit Users', 'users.edit', 'User Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(5, 'Delete Users', 'users.delete', 'User Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(6, 'View Roles', 'roles.view', 'Role Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(7, 'Manage Role Permissions', 'roles.manage_permissions', 'Role Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(8, 'View Permissions', 'permissions.view', 'Permission Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(9, 'View Leads', 'leads.view', 'Lead Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(10, 'Create Leads', 'leads.create', 'Lead Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(11, 'Edit Leads', 'leads.edit', 'Lead Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(12, 'Delete Leads', 'leads.delete', 'Lead Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(13, 'View Clients', 'clients.view', 'Client Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(14, 'Create Clients', 'clients.create', 'Client Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(15, 'Edit Clients', 'clients.edit', 'Client Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(16, 'Delete Clients', 'clients.delete', 'Client Management', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(17, 'View Settings', 'settings.view', 'Settings', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(18, 'Update Settings', 'settings.update', 'Settings', '2026-07-10 08:02:44', '2026-07-10 08:02:44'),
(19, 'Activate or Deactivate Users', 'users.toggle_status', 'User Management', '2026-07-16 00:45:36', '2026-07-16 00:45:36'),
(20, 'View All Leads', 'leads.view_all', 'Lead Management', '2026-07-16 03:37:13', '2026-07-16 03:37:13'),
(21, 'Assign Leads', 'leads.assign', 'Lead Management', '2026-07-16 03:37:13', '2026-07-16 03:37:13'),
(22, 'Convert Leads To Clients', 'leads.convert', 'Lead Management', '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(23, 'View Follow-ups', 'follow_ups.view', 'Follow-up Management', '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(24, 'View All Follow-ups', 'follow_ups.view_all', 'Follow-up Management', '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(25, 'Create Follow-ups', 'follow_ups.create', 'Follow-up Management', '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(26, 'Edit Follow-ups', 'follow_ups.edit', 'Follow-up Management', '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(27, 'Delete Follow-ups', 'follow_ups.delete', 'Follow-up Management', '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(28, 'View All Clients', 'clients.view_all', 'Client Management', '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(29, 'Assign Clients', 'clients.assign', 'Client Management', '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(30, 'View Projects', 'projects.view', 'Project Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(31, 'View All Projects', 'projects.view_all', 'Project Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(32, 'Create Projects', 'projects.create', 'Project Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(33, 'Edit Projects', 'projects.edit', 'Project Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(34, 'Delete Projects', 'projects.delete', 'Project Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(35, 'Assign Project Manager', 'projects.assign_manager', 'Project Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(36, 'Manage Project Members', 'projects.manage_members', 'Project Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(37, 'Complete Projects', 'projects.complete', 'Project Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(38, 'View Project Services', 'project_services.view', 'Project Services', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(39, 'Create Project Services', 'project_services.create', 'Project Services', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(40, 'Edit Project Services', 'project_services.edit', 'Project Services', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(41, 'Delete Project Services', 'project_services.delete', 'Project Services', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(42, 'Assign Project Services', 'project_services.assign', 'Project Services', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(43, 'View Tasks', 'tasks.view', 'Task Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(44, 'View All Tasks', 'tasks.view_all', 'Task Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(45, 'Create Tasks', 'tasks.create', 'Task Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(46, 'Edit Tasks', 'tasks.edit', 'Task Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(47, 'Delete Tasks', 'tasks.delete', 'Task Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(48, 'Assign Tasks', 'tasks.assign', 'Task Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(49, 'Update Task Status', 'tasks.update_status', 'Task Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(50, 'Review Tasks', 'tasks.review', 'Task Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(51, 'Complete Tasks', 'tasks.complete', 'Task Management', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(52, 'Create Task Comments', 'task_comments.create', 'Task Collaboration', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(53, 'Edit Task Comments', 'task_comments.edit', 'Task Collaboration', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(54, 'Delete Task Comments', 'task_comments.delete', 'Task Collaboration', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(55, 'Upload Task Attachments', 'task_attachments.upload', 'Task Collaboration', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(56, 'Download Task Attachments', 'task_attachments.download', 'Task Collaboration', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(57, 'Delete Task Attachments', 'task_attachments.delete', 'Task Collaboration', '2026-07-18 07:14:43', '2026-07-18 07:14:43'),
(58, 'Create Roles', 'roles.create', 'Role Management', '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(59, 'Manage Task Dependencies', 'tasks.manage_dependencies', 'Task Management', '2026-07-20 11:39:19', '2026-07-20 11:39:19'),
(60, 'Use Time Tracking', 'time_tracking.use', 'Time Tracking', '2026-07-25 11:01:54', '2026-07-25 11:01:54'),
(61, 'View Own Time Entries', 'time_tracking.view_own', 'Time Tracking', '2026-07-25 11:01:54', '2026-07-25 11:01:54'),
(62, 'View Team Time Report', 'time_tracking.view_team', 'Time Tracking', '2026-07-25 11:01:54', '2026-07-25 11:01:54'),
(63, 'View All Time Reports', 'time_tracking.view_all', 'Time Tracking', '2026-07-25 11:01:54', '2026-07-25 11:01:54'),
(64, 'View Executive Dashboard Report', 'reports.executive.view', 'Reports and Analytics', '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(65, 'View All Executive Dashboard Data', 'reports.executive.view_all', 'Reports and Analytics', '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(66, 'View Project Reports', 'reports.projects.view', 'Reports and Analytics', '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(67, 'View All Project Reports', 'reports.projects.view_all', 'Reports and Analytics', '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(68, 'View Follow-up Reports', 'reports.followups.view', 'Reports and Analytics', '2026-07-28 07:47:36', '2026-07-28 07:47:36'),
(69, 'View All Follow-up Reports', 'reports.followups.view_all', 'Reports and Analytics', '2026-07-28 07:47:36', '2026-07-28 07:47:36'),
(70, 'View Lead Reports', 'reports.leads.view', 'Reports and Analytics', '2026-07-28 10:14:13', '2026-07-28 10:14:13'),
(71, 'View All Lead Report Data', 'reports.leads.view_all', 'Reports and Analytics', '2026-07-28 10:14:13', '2026-07-28 10:14:13'),
(72, 'Import Leads', 'leads.import', 'Lead Management', '2026-07-28 12:07:53', '2026-07-28 12:07:53'),
(73, 'Export Leads', 'leads.export', 'Lead Management', '2026-07-28 12:07:53', '2026-07-28 12:07:53'),
(74, 'Import Clients', 'clients.import', 'Client Management', '2026-07-29 06:51:04', '2026-07-29 06:51:04'),
(75, 'Export Clients', 'clients.export', 'Client Management', '2026-07-29 06:51:05', '2026-07-29 06:51:05'),
(76, 'Import Tasks', 'tasks.import', 'Task Management', '2026-07-29 10:14:27', '2026-07-29 10:14:27'),
(77, 'Export Tasks', 'tasks.export', 'Task Management', '2026-07-29 10:14:27', '2026-07-29 10:14:27'),
(78, 'Import Follow-ups', 'follow_ups.import', 'Follow-up Management', '2026-07-29 11:43:25', '2026-07-29 11:43:25'),
(79, 'Export Follow-ups', 'follow_ups.export', 'Follow-up Management', '2026-07-29 11:43:25', '2026-07-29 11:43:25');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_role`
--

INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, 14, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(2, 1, 16, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(3, 1, 15, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(4, 1, 13, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(5, 1, 1, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(6, 1, 10, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(7, 1, 12, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(8, 1, 11, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(9, 1, 9, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(10, 1, 8, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(11, 1, 7, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(12, 1, 6, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(13, 1, 18, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(14, 1, 17, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(15, 1, 3, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(16, 1, 5, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(17, 1, 4, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(18, 1, 2, '2026-07-10 08:11:06', '2026-07-10 08:11:06'),
(20, 3, 1, '2026-07-11 01:56:01', '2026-07-11 01:56:01'),
(21, 3, 6, '2026-07-11 02:37:29', '2026-07-11 02:37:29'),
(24, 5, 1, '2026-07-11 03:42:45', '2026-07-11 03:42:45'),
(25, 1, 19, '2026-07-16 00:45:36', '2026-07-16 00:45:36'),
(26, 1, 21, '2026-07-16 03:37:13', '2026-07-16 03:37:13'),
(27, 1, 20, '2026-07-16 03:37:13', '2026-07-16 03:37:13'),
(30, 4, 9, '2026-07-16 05:58:58', '2026-07-16 05:58:58'),
(31, 4, 11, '2026-07-16 06:00:05', '2026-07-16 06:00:05'),
(34, 1, 29, '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(35, 1, 28, '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(36, 1, 25, '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(37, 1, 27, '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(38, 1, 26, '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(39, 1, 23, '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(40, 1, 24, '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(41, 1, 22, '2026-07-16 07:34:40', '2026-07-16 07:34:40'),
(57, 4, 15, '2026-07-17 02:29:50', '2026-07-17 02:29:50'),
(58, 4, 13, '2026-07-17 02:29:50', '2026-07-17 02:29:50'),
(59, 4, 1, '2026-07-17 02:29:50', '2026-07-17 02:29:50'),
(60, 4, 25, '2026-07-17 02:29:50', '2026-07-17 02:29:50'),
(61, 4, 26, '2026-07-17 02:29:50', '2026-07-17 02:29:50'),
(62, 4, 23, '2026-07-17 02:29:50', '2026-07-17 02:29:50'),
(63, 4, 22, '2026-07-17 02:29:50', '2026-07-17 02:29:50'),
(64, 4, 10, '2026-07-17 02:29:50', '2026-07-17 02:29:50'),
(66, 1, 42, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(67, 1, 39, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(68, 1, 41, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(69, 1, 40, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(70, 1, 38, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(71, 1, 35, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(72, 1, 37, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(73, 1, 32, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(74, 1, 34, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(75, 1, 33, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(76, 1, 36, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(77, 1, 30, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(78, 1, 31, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(79, 1, 57, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(80, 1, 56, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(81, 1, 55, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(82, 1, 52, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(83, 1, 54, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(84, 1, 53, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(85, 1, 48, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(86, 1, 51, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(87, 1, 45, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(88, 1, 47, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(89, 1, 46, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(90, 1, 50, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(91, 1, 49, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(92, 1, 43, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(93, 1, 44, '2026-07-18 07:14:44', '2026-07-18 07:14:44'),
(95, 3, 37, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(96, 3, 32, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(98, 3, 33, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(99, 3, 36, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(101, 3, 30, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(102, 3, 42, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(103, 3, 39, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(104, 3, 41, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(105, 3, 40, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(106, 3, 38, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(107, 3, 52, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(108, 3, 57, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(109, 3, 54, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(110, 3, 56, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(111, 3, 53, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(112, 3, 55, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(113, 3, 48, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(114, 3, 51, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(115, 3, 45, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(117, 3, 46, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(118, 3, 50, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(119, 3, 49, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(121, 3, 43, '2026-07-18 10:59:32', '2026-07-18 10:59:32'),
(122, 5, 13, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(123, 5, 30, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(124, 5, 38, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(125, 5, 52, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(126, 5, 57, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(127, 5, 54, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(128, 5, 56, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(129, 5, 53, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(130, 5, 55, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(131, 5, 49, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(132, 5, 43, '2026-07-18 12:33:55', '2026-07-18 12:33:55'),
(133, 1, 58, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(134, 2, 29, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(135, 2, 14, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(136, 2, 16, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(137, 2, 15, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(138, 2, 13, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(139, 2, 28, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(140, 2, 1, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(141, 2, 25, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(142, 2, 27, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(143, 2, 26, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(144, 2, 23, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(145, 2, 24, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(146, 2, 21, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(147, 2, 22, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(148, 2, 10, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(149, 2, 12, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(150, 2, 11, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(151, 2, 9, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(152, 2, 20, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(153, 2, 8, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(154, 2, 42, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(155, 2, 39, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(156, 2, 41, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(157, 2, 40, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(158, 2, 38, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(159, 2, 35, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(160, 2, 37, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(161, 2, 32, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(162, 2, 34, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(163, 2, 33, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(164, 2, 36, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(165, 2, 30, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(166, 2, 31, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(167, 2, 58, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(168, 2, 7, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(169, 2, 6, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(170, 2, 18, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(171, 2, 17, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(172, 2, 57, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(173, 2, 56, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(174, 2, 55, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(175, 2, 52, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(176, 2, 54, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(177, 2, 53, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(178, 2, 48, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(179, 2, 51, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(180, 2, 45, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(181, 2, 47, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(182, 2, 46, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(183, 2, 50, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(184, 2, 49, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(185, 2, 43, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(186, 2, 44, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(187, 2, 3, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(188, 2, 5, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(189, 2, 4, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(190, 2, 19, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(191, 2, 2, '2026-07-18 12:57:01', '2026-07-18 12:57:01'),
(192, 6, 1, '2026-07-18 13:28:10', '2026-07-18 13:28:10'),
(193, 6, 52, '2026-07-18 13:28:10', '2026-07-18 13:28:10'),
(194, 6, 57, '2026-07-18 13:28:10', '2026-07-18 13:28:10'),
(195, 6, 54, '2026-07-18 13:28:10', '2026-07-18 13:28:10'),
(196, 6, 56, '2026-07-18 13:28:10', '2026-07-18 13:28:10'),
(197, 6, 53, '2026-07-18 13:28:10', '2026-07-18 13:28:10'),
(198, 6, 49, '2026-07-18 13:28:10', '2026-07-18 13:28:10'),
(199, 6, 43, '2026-07-18 13:28:10', '2026-07-18 13:28:10'),
(200, 1, 59, '2026-07-20 11:39:19', '2026-07-20 11:39:19'),
(201, 2, 59, '2026-07-20 11:39:19', '2026-07-20 11:39:19'),
(203, 7, 1, '2026-07-20 11:59:15', '2026-07-20 11:59:15'),
(204, 7, 56, '2026-07-20 11:59:15', '2026-07-20 11:59:15'),
(205, 7, 55, '2026-07-20 11:59:15', '2026-07-20 11:59:15'),
(206, 7, 49, '2026-07-20 11:59:15', '2026-07-20 11:59:15'),
(207, 7, 43, '2026-07-20 11:59:15', '2026-07-20 11:59:15'),
(208, 3, 59, '2026-07-20 12:00:25', '2026-07-20 12:00:25'),
(209, 3, 15, '2026-07-20 12:14:22', '2026-07-20 12:14:22'),
(210, 3, 13, '2026-07-20 12:14:22', '2026-07-20 12:14:22'),
(211, 3, 35, '2026-07-20 12:14:22', '2026-07-20 12:14:22'),
(212, 7, 52, '2026-07-20 12:15:47', '2026-07-20 12:15:47'),
(213, 7, 53, '2026-07-20 12:15:47', '2026-07-20 12:15:47'),
(214, 1, 60, '2026-07-25 11:01:55', '2026-07-25 11:01:55'),
(215, 1, 63, '2026-07-25 11:01:55', '2026-07-25 11:01:55'),
(216, 1, 61, '2026-07-25 11:01:55', '2026-07-25 11:01:55'),
(217, 1, 62, '2026-07-25 11:01:55', '2026-07-25 11:01:55'),
(218, 2, 60, '2026-07-25 11:01:55', '2026-07-25 11:01:55'),
(219, 2, 63, '2026-07-25 11:01:55', '2026-07-25 11:01:55'),
(220, 2, 61, '2026-07-25 11:01:55', '2026-07-25 11:01:55'),
(221, 2, 62, '2026-07-25 11:01:55', '2026-07-25 11:01:55'),
(222, 7, 60, '2026-07-25 11:35:22', '2026-07-25 11:35:22'),
(223, 7, 63, '2026-07-25 11:35:22', '2026-07-25 11:35:22'),
(224, 7, 61, '2026-07-25 11:35:22', '2026-07-25 11:35:22'),
(225, 6, 60, '2026-07-25 11:35:57', '2026-07-25 11:35:57'),
(226, 6, 63, '2026-07-25 11:35:57', '2026-07-25 11:35:57'),
(227, 6, 61, '2026-07-25 11:35:57', '2026-07-25 11:35:57'),
(228, 3, 60, '2026-07-25 11:36:10', '2026-07-25 11:36:10'),
(229, 3, 63, '2026-07-25 11:36:10', '2026-07-25 11:36:10'),
(230, 3, 61, '2026-07-25 11:36:11', '2026-07-25 11:36:11'),
(231, 4, 60, '2026-07-25 11:36:27', '2026-07-25 11:36:27'),
(232, 4, 63, '2026-07-25 11:36:27', '2026-07-25 11:36:27'),
(233, 4, 61, '2026-07-25 11:36:27', '2026-07-25 11:36:27'),
(234, 5, 60, '2026-07-25 11:36:50', '2026-07-25 11:36:50'),
(235, 5, 63, '2026-07-25 11:36:50', '2026-07-25 11:36:50'),
(236, 5, 61, '2026-07-25 11:36:50', '2026-07-25 11:36:50'),
(237, 1, 64, '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(238, 1, 65, '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(239, 1, 66, '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(240, 1, 67, '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(241, 2, 64, '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(242, 2, 65, '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(243, 2, 66, '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(244, 2, 67, '2026-07-27 08:29:06', '2026-07-27 08:29:06'),
(245, 1, 68, '2026-07-28 07:47:36', '2026-07-28 07:47:36'),
(246, 1, 69, '2026-07-28 07:47:36', '2026-07-28 07:47:36'),
(247, 2, 68, '2026-07-28 07:47:37', '2026-07-28 07:47:37'),
(248, 2, 69, '2026-07-28 07:47:37', '2026-07-28 07:47:37'),
(249, 1, 70, '2026-07-28 10:14:13', '2026-07-28 10:14:13'),
(250, 1, 71, '2026-07-28 10:14:13', '2026-07-28 10:14:13'),
(251, 2, 70, '2026-07-28 10:14:13', '2026-07-28 10:14:13'),
(252, 2, 71, '2026-07-28 10:14:13', '2026-07-28 10:14:13'),
(253, 1, 73, '2026-07-28 12:07:53', '2026-07-28 12:07:53'),
(254, 1, 72, '2026-07-28 12:07:53', '2026-07-28 12:07:53'),
(255, 2, 73, '2026-07-28 12:07:53', '2026-07-28 12:07:53'),
(256, 2, 72, '2026-07-28 12:07:53', '2026-07-28 12:07:53'),
(257, 1, 75, '2026-07-29 06:51:05', '2026-07-29 06:51:05'),
(258, 1, 74, '2026-07-29 06:51:05', '2026-07-29 06:51:05'),
(259, 2, 75, '2026-07-29 06:51:06', '2026-07-29 06:51:06'),
(260, 2, 74, '2026-07-29 06:51:06', '2026-07-29 06:51:06'),
(261, 1, 77, '2026-07-29 10:14:27', '2026-07-29 10:14:27'),
(262, 1, 76, '2026-07-29 10:14:27', '2026-07-29 10:14:27'),
(263, 2, 77, '2026-07-29 10:14:27', '2026-07-29 10:14:27'),
(264, 2, 76, '2026-07-29 10:14:27', '2026-07-29 10:14:27'),
(265, 7, 77, '2026-07-29 10:23:17', '2026-07-29 10:23:17'),
(267, 1, 79, '2026-07-29 11:43:25', '2026-07-29 11:43:25'),
(268, 1, 78, '2026-07-29 11:43:25', '2026-07-29 11:43:25'),
(269, 2, 79, '2026-07-29 11:43:25', '2026-07-29 11:43:25'),
(270, 2, 78, '2026-07-29 11:43:25', '2026-07-29 11:43:25');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint UNSIGNED NOT NULL,
  `project_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `project_manager_id` bigint UNSIGNED DEFAULT NULL,
  `priority` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_code`, `client_id`, `name`, `description`, `project_manager_id`, `priority`, `status`, `start_date`, `due_date`, `budget`, `completed_at`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'PRJ-2026-0001', 6, 'Project Phoenix', 'web development', NULL, 'medium', 'completed', '2026-07-18', '2026-07-23', 30000.00, NULL, 1, '2026-07-18 10:47:39', '2026-07-18 11:38:28', NULL),
(2, 'PRJ-2026-0002', 8, 'website landing page', 'Simple website landing page.', 35, 'urgent', 'completed', '2026-07-20', '2026-07-25', 10000.00, '2026-07-21 12:54:50', 1, '2026-07-20 09:40:43', '2026-07-21 07:24:50', NULL),
(3, 'PRJ-2026-0003', 9, 'Rahul Enterprises Landing Page', 'Design and development of a responsive business landing page with enquiry form.', 35, 'high', 'completed', '2026-07-20', '2026-07-27', 50000.00, '2026-07-21 12:54:18', 35, '2026-07-20 12:57:30', '2026-07-21 07:24:18', NULL),
(4, 'PRJ-2026-0004', 11, 'Project Phoenix22', NULL, 37, 'medium', 'active', NULL, NULL, NULL, NULL, 1, '2026-07-21 12:19:27', '2026-07-25 11:37:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_activities`
--

CREATE TABLE `project_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_activities`
--

INSERT INTO `project_activities` (`id`, `project_id`, `user_id`, `action`, `subject_type`, `subject_id`, `description`, `old_values`, `new_values`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'project_created', 'App\\Modules\\Project\\Models\\Project', 1, 'Project PRJ-2026-0001 created.', NULL, NULL, '2026-07-18 10:47:39', '2026-07-18 10:47:39'),
(2, 1, 1, 'member_added', 'App\\Modules\\User\\Models\\User', 19, 'User 20 added to project team.', NULL, NULL, '2026-07-18 10:48:01', '2026-07-18 10:48:01'),
(3, 1, 1, 'member_added', 'App\\Modules\\User\\Models\\User', 20, 'User 21 added to project team.', NULL, NULL, '2026-07-18 10:50:26', '2026-07-18 10:50:26'),
(4, 1, 1, 'member_added', 'App\\Modules\\User\\Models\\User', 21, 'User 22 added to project team.', NULL, NULL, '2026-07-18 10:50:38', '2026-07-18 10:50:38'),
(5, 1, 1, 'service_created', 'App\\Modules\\Project\\Models\\ProjectService', 1, 'Service Landing page coding part created.', NULL, NULL, '2026-07-18 10:53:19', '2026-07-18 10:53:19'),
(6, 1, 1, 'project_updated', 'App\\Modules\\Project\\Models\\Project', 1, 'Project PRJ-2026-0001 updated.', '{\"name\": \"Project Phoenix\", \"budget\": null, \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": \"6\", \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '2026-07-18 10:57:34', '2026-07-18 10:57:34'),
(7, 1, 18, 'project_updated', 'App\\Modules\\Project\\Models\\Project', 1, 'Project PRJ-2026-0001 updated.', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": \"6\", \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '2026-07-18 11:01:59', '2026-07-18 11:01:59'),
(8, 1, 21, 'project_updated', 'App\\Modules\\Project\\Models\\Project', 1, 'Project PRJ-2026-0001 updated.', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": \"6\", \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '2026-07-18 11:02:21', '2026-07-18 11:02:21'),
(9, 1, 21, 'project_updated', 'App\\Modules\\Project\\Models\\Project', 1, 'Project PRJ-2026-0001 updated.', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": \"6\", \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '2026-07-18 11:24:12', '2026-07-18 11:24:12'),
(10, 1, 21, 'task_created', 'App\\Modules\\Task\\Models\\Task', 1, 'Task design landing page created.', NULL, NULL, '2026-07-18 11:25:15', '2026-07-18 11:25:15'),
(11, 1, 21, 'task_attachment_uploaded', 'App\\Modules\\Task\\Models\\TaskAttachment', 1, 'Attachment final 2.png uploaded.', NULL, NULL, '2026-07-18 11:26:00', '2026-07-18 11:26:00'),
(12, 1, 21, 'task_comment_added', 'App\\Modules\\Task\\Models\\TaskComment', 1, 'Comment added to task design landing page.', NULL, NULL, '2026-07-18 11:26:13', '2026-07-18 11:26:13'),
(13, 1, 21, 'task_updated', 'App\\Modules\\Task\\Models\\Task', 1, 'Task design landing page updated.', '{\"id\": 1, \"title\": \"design landing page\", \"due_at\": \"2026-07-25T11:24:00.000000Z\", \"status\": \"to_do\", \"project\": {\"id\": 1, \"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"members\": [{\"id\": 19, \"name\": \"User 20\", \"email\": \"user20@gmail.com\", \"pivot\": {\"user_id\": 19, \"added_by\": 1, \"created_at\": \"2026-07-18T10:48:01.000000Z\", \"project_id\": 1, \"updated_at\": \"2026-07-18T10:48:01.000000Z\", \"member_role\": null}, \"role_id\": 3, \"is_active\": true, \"created_at\": \"2026-07-10T06:14:20.000000Z\", \"updated_at\": \"2026-07-10T06:14:20.000000Z\", \"email_verified_at\": null}, {\"id\": 20, \"name\": \"User 21\", \"email\": \"user21@gmail.com\", \"pivot\": {\"user_id\": 20, \"added_by\": 1, \"created_at\": \"2026-07-18T10:50:26.000000Z\", \"project_id\": 1, \"updated_at\": \"2026-07-18T10:50:26.000000Z\", \"member_role\": \"developer\"}, \"role_id\": 3, \"is_active\": true, \"created_at\": \"2026-07-10T06:14:20.000000Z\", \"updated_at\": \"2026-07-10T06:14:20.000000Z\", \"email_verified_at\": null}, {\"id\": 21, \"name\": \"User 22\", \"email\": \"user22@gmail.com\", \"pivot\": {\"user_id\": 21, \"added_by\": 1, \"created_at\": \"2026-07-18T10:50:38.000000Z\", \"project_id\": 1, \"updated_at\": \"2026-07-18T10:50:38.000000Z\", \"member_role\": \"manager\"}, \"role_id\": 3, \"is_active\": true, \"created_at\": \"2026-07-10T06:14:20.000000Z\", \"updated_at\": \"2026-07-10T06:14:20.000000Z\", \"email_verified_at\": null}], \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"created_at\": \"2026-07-18T10:47:39.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"updated_at\": \"2026-07-18T10:57:34.000000Z\", \"description\": \"web development\", \"completed_at\": null, \"project_code\": \"PRJ-2026-0001\", \"project_manager_id\": null}, \"priority\": \"high\", \"created_at\": \"2026-07-18T11:25:15.000000Z\", \"created_by\": 21, \"deleted_at\": null, \"project_id\": 1, \"start_date\": \"2026-07-19T18:30:00.000000Z\", \"updated_at\": \"2026-07-18T11:25:15.000000Z\", \"assigned_to\": 19, \"description\": \"wqewwewe\", \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": 21, \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": \"18.00\", \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 1, \"submitted_for_review_at\": null}', '{\"id\": 1, \"title\": \"design landing page\", \"due_at\": \"2026-07-25T11:24:00.000000Z\", \"status\": \"to_do\", \"project\": {\"id\": 1, \"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"members\": [{\"id\": 19, \"name\": \"User 20\", \"email\": \"user20@gmail.com\", \"pivot\": {\"user_id\": 19, \"added_by\": 1, \"created_at\": \"2026-07-18T10:48:01.000000Z\", \"project_id\": 1, \"updated_at\": \"2026-07-18T10:48:01.000000Z\", \"member_role\": null}, \"role_id\": 3, \"is_active\": true, \"created_at\": \"2026-07-10T06:14:20.000000Z\", \"updated_at\": \"2026-07-10T06:14:20.000000Z\", \"email_verified_at\": null}, {\"id\": 20, \"name\": \"User 21\", \"email\": \"user21@gmail.com\", \"pivot\": {\"user_id\": 20, \"added_by\": 1, \"created_at\": \"2026-07-18T10:50:26.000000Z\", \"project_id\": 1, \"updated_at\": \"2026-07-18T10:50:26.000000Z\", \"member_role\": \"developer\"}, \"role_id\": 3, \"is_active\": true, \"created_at\": \"2026-07-10T06:14:20.000000Z\", \"updated_at\": \"2026-07-10T06:14:20.000000Z\", \"email_verified_at\": null}, {\"id\": 21, \"name\": \"User 22\", \"email\": \"user22@gmail.com\", \"pivot\": {\"user_id\": 21, \"added_by\": 1, \"created_at\": \"2026-07-18T10:50:38.000000Z\", \"project_id\": 1, \"updated_at\": \"2026-07-18T10:50:38.000000Z\", \"member_role\": \"manager\"}, \"role_id\": 3, \"is_active\": true, \"created_at\": \"2026-07-10T06:14:20.000000Z\", \"updated_at\": \"2026-07-10T06:14:20.000000Z\", \"email_verified_at\": null}], \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"created_at\": \"2026-07-18T10:47:39.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"updated_at\": \"2026-07-18T10:57:34.000000Z\", \"description\": \"web development\", \"completed_at\": null, \"project_code\": \"PRJ-2026-0001\", \"project_manager_id\": null}, \"priority\": \"high\", \"created_at\": \"2026-07-18T11:25:15.000000Z\", \"created_by\": 21, \"deleted_at\": null, \"project_id\": 1, \"start_date\": \"2026-07-19T18:30:00.000000Z\", \"updated_at\": \"2026-07-18T11:25:15.000000Z\", \"assigned_to\": \"19\", \"description\": \"wqewwewe\", \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": \"21\", \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": \"18.00\", \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 1, \"submitted_for_review_at\": null}', '2026-07-18 11:26:27', '2026-07-18 11:26:27'),
(14, 1, 21, 'project_updated', 'App\\Modules\\Project\\Models\\Project', 1, 'Project PRJ-2026-0001 updated.', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": \"6\", \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '2026-07-18 11:26:37', '2026-07-18 11:26:37'),
(15, 1, 19, 'task_comment_added', 'App\\Modules\\Task\\Models\\TaskComment', 2, 'Comment added to task design landing page.', NULL, NULL, '2026-07-18 11:28:11', '2026-07-18 11:28:11'),
(16, 1, 19, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 1, 'Task design landing page moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-18 11:28:48', '2026-07-18 11:28:48'),
(17, 1, 19, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 1, 'Task design landing page submitted for review.', NULL, NULL, '2026-07-18 11:28:58', '2026-07-18 11:28:58'),
(18, 1, 19, 'task_attachment_uploaded', 'App\\Modules\\Task\\Models\\TaskAttachment', 2, 'Attachment ChatGPT Image Jun 19, 2026, 03_55_29 PM.png uploaded.', NULL, NULL, '2026-07-18 11:29:12', '2026-07-18 11:29:12'),
(19, 1, 21, 'project_updated', 'App\\Modules\\Project\\Models\\Project', 1, 'Project PRJ-2026-0001 updated.', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": \"6\", \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '2026-07-18 11:29:46', '2026-07-18 11:29:46'),
(20, 1, 21, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 1, 'Task design landing page approved and completed.', NULL, NULL, '2026-07-18 11:30:18', '2026-07-18 11:30:18'),
(21, 1, 19, 'project_updated', 'App\\Modules\\Project\\Models\\Project', 1, 'Project PRJ-2026-0001 updated.', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": \"6\", \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '2026-07-18 11:38:14', '2026-07-18 11:38:14'),
(22, 1, 19, 'project_updated', 'App\\Modules\\Project\\Models\\Project', 1, 'Project PRJ-2026-0001 updated.', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"draft\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": 6, \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '{\"name\": \"Project Phoenix\", \"budget\": \"30000.00\", \"status\": \"completed\", \"due_date\": \"2026-07-22T18:30:00.000000Z\", \"priority\": \"medium\", \"client_id\": \"6\", \"start_date\": \"2026-07-17T18:30:00.000000Z\", \"project_manager_id\": null}', '2026-07-18 11:38:28', '2026-07-18 11:38:28'),
(23, 1, 19, 'member_removed', 'App\\Modules\\User\\Models\\User', 20, 'User 21 removed from project team.', NULL, NULL, '2026-07-18 11:38:41', '2026-07-18 11:38:41'),
(24, 2, 1, 'project_created', 'App\\Modules\\Project\\Models\\Project', 2, 'Project PRJ-2026-0002 created.', NULL, NULL, '2026-07-20 09:40:44', '2026-07-20 09:40:44'),
(25, 2, 1, 'member_added', 'App\\Modules\\User\\Models\\User', 36, 'Lucky sir added to project team.', NULL, NULL, '2026-07-20 09:41:28', '2026-07-20 09:41:28'),
(26, 2, 1, 'service_created', 'App\\Modules\\Project\\Models\\ProjectService', 2, 'Service lLANDING PAGE CREATE created.', NULL, NULL, '2026-07-20 09:42:32', '2026-07-20 09:42:32'),
(27, 2, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 2, 'Task CREATE LANDING PAGE CODEING PART created.', NULL, NULL, '2026-07-20 09:43:40', '2026-07-20 09:43:40'),
(28, 2, 1, 'task_comment_added', 'App\\Modules\\Task\\Models\\TaskComment', 3, 'Comment added to task CREATE LANDING PAGE CODEING PART.', NULL, NULL, '2026-07-20 09:44:28', '2026-07-20 09:44:28'),
(29, 2, 1, 'task_attachment_uploaded', 'App\\Modules\\Task\\Models\\TaskAttachment', 3, 'Attachment WhatsApp Image 2026-07-10 at 11.11.36 AM.jpeg uploaded.', NULL, NULL, '2026-07-20 09:44:44', '2026-07-20 09:44:44'),
(30, 2, 1, 'project_updated', 'App\\Modules\\Project\\Models\\Project', 2, 'Project PRJ-2026-0002 updated.', '{\"name\": \"website landing page\", \"budget\": \"10000.00\", \"status\": \"active\", \"due_date\": \"2026-07-24T18:30:00.000000Z\", \"priority\": \"urgent\", \"client_id\": 8, \"start_date\": \"2026-07-19T18:30:00.000000Z\", \"project_manager_id\": 35}', '{\"name\": \"website landing page\", \"budget\": \"10000.00\", \"status\": \"active\", \"due_date\": \"2026-07-24T18:30:00.000000Z\", \"priority\": \"urgent\", \"client_id\": \"8\", \"start_date\": \"2026-07-19T18:30:00.000000Z\", \"project_manager_id\": \"35\"}', '2026-07-20 09:45:18', '2026-07-20 09:45:18'),
(31, 2, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 3, 'Task FRONTEND DESIGN created.', NULL, NULL, '2026-07-20 09:46:08', '2026-07-20 09:46:08'),
(32, 2, 1, 'task_comment_added', 'App\\Modules\\Task\\Models\\TaskComment', 4, 'Comment added to task FRONTEND DESIGN.', NULL, NULL, '2026-07-20 09:46:37', '2026-07-20 09:46:37'),
(33, 2, 1, 'task_attachment_uploaded', 'App\\Modules\\Task\\Models\\TaskAttachment', 4, 'Attachment photo_6264676467052581143_y.jpg uploaded.', NULL, NULL, '2026-07-20 09:46:47', '2026-07-20 09:46:47'),
(34, 2, 35, 'task_comment_added', 'App\\Modules\\Task\\Models\\TaskComment', 5, 'Comment added to task CREATE LANDING PAGE CODEING PART.', NULL, NULL, '2026-07-20 09:48:25', '2026-07-20 09:48:25'),
(35, 2, 36, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 3, 'Task FRONTEND DESIGN moved from to_do to completed.', '{\"status\": \"to_do\"}', '{\"status\": \"completed\"}', '2026-07-20 09:51:04', '2026-07-20 09:51:04'),
(36, 2, 36, 'task_comment_added', 'App\\Modules\\Task\\Models\\TaskComment', 6, 'Comment added to task FRONTEND DESIGN.', NULL, NULL, '2026-07-20 09:51:54', '2026-07-20 09:51:54'),
(37, 2, 36, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 2, 'Task CREATE LANDING PAGE CODEING PART moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-20 10:00:41', '2026-07-20 10:00:41'),
(38, 2, 36, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 2, 'Task CREATE LANDING PAGE CODEING PART moved from in_progress to in_progress.', '{\"status\": \"in_progress\"}', '{\"status\": \"in_progress\"}', '2026-07-20 10:01:55', '2026-07-20 10:01:55'),
(39, 2, 36, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 2, 'Task CREATE LANDING PAGE CODEING PART moved from in_progress to in_progress.', '{\"status\": \"in_progress\"}', '{\"status\": \"in_progress\"}', '2026-07-20 10:02:06', '2026-07-20 10:02:06'),
(40, 2, 36, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 2, 'Task CREATE LANDING PAGE CODEING PART submitted for review.', NULL, NULL, '2026-07-20 10:02:13', '2026-07-20 10:02:13'),
(41, 2, 36, 'task_comment_added', 'App\\Modules\\Task\\Models\\TaskComment', 7, 'Comment added to task CREATE LANDING PAGE CODEING PART.', NULL, NULL, '2026-07-20 10:02:35', '2026-07-20 10:02:35'),
(42, 2, 35, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 2, 'Task CREATE LANDING PAGE CODEING PART approved and completed.', NULL, NULL, '2026-07-20 10:04:02', '2026-07-20 10:04:02'),
(43, 3, 35, 'project_created', 'App\\Modules\\Project\\Models\\Project', 3, 'Project PRJ-2026-0003 created.', NULL, NULL, '2026-07-20 12:57:30', '2026-07-20 12:57:30'),
(44, 3, 35, 'member_added', 'App\\Modules\\User\\Models\\User', 37, 'Amisha added to project team.', NULL, NULL, '2026-07-20 12:58:33', '2026-07-20 12:58:33'),
(45, 3, 35, 'member_added', 'App\\Modules\\User\\Models\\User', 36, 'Lucky sir added to project team.', NULL, NULL, '2026-07-20 12:58:42', '2026-07-20 12:58:42'),
(46, 3, 35, 'service_created', 'App\\Modules\\Project\\Models\\ProjectService', 3, 'Service Landing Page UI/UX Design created.', NULL, NULL, '2026-07-20 13:03:00', '2026-07-20 13:03:00'),
(47, 3, 35, 'service_created', 'App\\Modules\\Project\\Models\\ProjectService', 4, 'Service Landing Page Development created.', NULL, NULL, '2026-07-20 13:03:48', '2026-07-20 13:03:48'),
(48, 3, 35, 'task_created', 'App\\Modules\\Task\\Models\\Task', 4, 'Task design header created.', NULL, NULL, '2026-07-20 13:05:44', '2026-07-20 13:05:44'),
(49, 3, 35, 'task_created', 'App\\Modules\\Task\\Models\\Task', 5, 'Task design body created.', NULL, NULL, '2026-07-20 13:06:55', '2026-07-20 13:06:55'),
(50, 3, 35, 'task_created', 'App\\Modules\\Task\\Models\\Task', 6, 'Task design footer created.', NULL, NULL, '2026-07-20 13:07:50', '2026-07-20 13:07:50'),
(51, 3, 35, 'task_created', 'App\\Modules\\Task\\Models\\Task', 7, 'Task develop header created.', NULL, NULL, '2026-07-20 13:08:28', '2026-07-20 13:08:28'),
(52, 3, 35, 'task_created', 'App\\Modules\\Task\\Models\\Task', 8, 'Task develop body created.', NULL, NULL, '2026-07-20 13:09:01', '2026-07-20 13:09:01'),
(53, 3, 35, 'task_created', 'App\\Modules\\Task\\Models\\Task', 9, 'Task develop footer created.', NULL, NULL, '2026-07-20 13:09:43', '2026-07-20 13:09:43'),
(54, 3, 35, 'task_dependency_added', 'App\\Modules\\Task\\Models\\Task', 7, 'Task develop header now depends on design header.', NULL, '{\"depends_on_task_id\": 4, \"depends_on_task_title\": \"design header\"}', '2026-07-20 13:21:53', '2026-07-20 13:21:53'),
(55, 3, 35, 'task_blocked_by_dependency', 'App\\Modules\\Task\\Models\\Task', 7, 'Task develop header was blocked because prerequisite tasks are incomplete.', '{\"status\": \"to_do\"}', '{\"status\": \"blocked\"}', '2026-07-20 13:21:53', '2026-07-20 13:21:53'),
(56, 3, 35, 'task_dependency_added', 'App\\Modules\\Task\\Models\\Task', 8, 'Task develop body now depends on design body.', NULL, '{\"depends_on_task_id\": 5, \"depends_on_task_title\": \"design body\"}', '2026-07-20 13:22:36', '2026-07-20 13:22:36'),
(57, 3, 35, 'task_blocked_by_dependency', 'App\\Modules\\Task\\Models\\Task', 8, 'Task develop body was blocked because prerequisite tasks are incomplete.', '{\"status\": \"to_do\"}', '{\"status\": \"blocked\"}', '2026-07-20 13:22:36', '2026-07-20 13:22:36'),
(58, 3, 35, 'task_dependency_added', 'App\\Modules\\Task\\Models\\Task', 9, 'Task develop footer now depends on design footer.', NULL, '{\"depends_on_task_id\": 6, \"depends_on_task_title\": \"design footer\"}', '2026-07-20 13:22:50', '2026-07-20 13:22:50'),
(59, 3, 35, 'task_blocked_by_dependency', 'App\\Modules\\Task\\Models\\Task', 9, 'Task develop footer was blocked because prerequisite tasks are incomplete.', '{\"status\": \"to_do\"}', '{\"status\": \"blocked\"}', '2026-07-20 13:22:50', '2026-07-20 13:22:50'),
(60, 3, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 4, 'Task design header moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-20 13:24:40', '2026-07-20 13:24:40'),
(61, 3, 35, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 4, 'Task design header submitted for review.', NULL, NULL, '2026-07-20 13:25:13', '2026-07-20 13:25:13'),
(62, 3, 35, 'task_unblocked', 'App\\Modules\\Task\\Models\\Task', 7, 'Task develop header was automatically unblocked.', '{\"status\": \"blocked\"}', '{\"status\": \"to_do\"}', '2026-07-20 13:25:22', '2026-07-20 13:25:22'),
(63, 3, 35, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 4, 'Task design header approved and completed.', NULL, NULL, '2026-07-20 13:25:22', '2026-07-20 13:25:22'),
(64, 3, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 5, 'Task design body moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-20 13:26:13', '2026-07-20 13:26:13'),
(65, 3, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 5, 'Task design body moved from in_progress to in_progress.', '{\"status\": \"in_progress\"}', '{\"status\": \"in_progress\"}', '2026-07-20 13:26:21', '2026-07-20 13:26:21'),
(66, 3, 36, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 7, 'Task develop header moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-20 13:27:10', '2026-07-20 13:27:10'),
(67, 3, 35, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 5, 'Task design body submitted for review.', NULL, NULL, '2026-07-20 13:27:38', '2026-07-20 13:27:38'),
(68, 3, 35, 'task_unblocked', 'App\\Modules\\Task\\Models\\Task', 8, 'Task develop body was automatically unblocked.', '{\"status\": \"blocked\"}', '{\"status\": \"to_do\"}', '2026-07-20 13:27:43', '2026-07-20 13:27:43'),
(69, 3, 35, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 5, 'Task design body approved and completed.', NULL, NULL, '2026-07-20 13:27:43', '2026-07-20 13:27:43'),
(70, 3, 35, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 7, 'Task develop header submitted for review.', NULL, NULL, '2026-07-20 13:28:11', '2026-07-20 13:28:11'),
(71, 3, 35, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 7, 'Task develop header approved and completed.', NULL, NULL, '2026-07-20 13:28:15', '2026-07-20 13:28:15'),
(72, 3, 36, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 8, 'Task develop body moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-21 07:20:20', '2026-07-21 07:20:20'),
(73, 3, 36, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 8, 'Task develop body submitted for review.', NULL, NULL, '2026-07-21 07:20:29', '2026-07-21 07:20:29'),
(74, 3, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 6, 'Task design footer moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-21 07:21:37', '2026-07-21 07:21:37'),
(75, 3, 37, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 6, 'Task design footer submitted for review.', NULL, NULL, '2026-07-21 07:21:42', '2026-07-21 07:21:42'),
(76, 3, 35, 'task_unblocked', 'App\\Modules\\Task\\Models\\Task', 9, 'Task develop footer was automatically unblocked.', '{\"status\": \"blocked\"}', '{\"status\": \"to_do\"}', '2026-07-21 07:22:52', '2026-07-21 07:22:52'),
(77, 3, 35, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 6, 'Task design footer approved and completed.', NULL, NULL, '2026-07-21 07:22:52', '2026-07-21 07:22:52'),
(78, 3, 36, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 9, 'Task develop footer moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-21 07:23:25', '2026-07-21 07:23:25'),
(79, 3, 36, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 9, 'Task develop footer submitted for review.', NULL, NULL, '2026-07-21 07:23:28', '2026-07-21 07:23:28'),
(80, 3, 1, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 8, 'Task develop body approved and completed.', NULL, NULL, '2026-07-21 07:23:55', '2026-07-21 07:23:55'),
(81, 3, 1, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 9, 'Task develop footer approved and completed.', NULL, NULL, '2026-07-21 07:24:06', '2026-07-21 07:24:06'),
(82, 3, 1, 'project_completed', 'App\\Modules\\Project\\Models\\Project', 3, 'Project PRJ-2026-0003 completed.', NULL, NULL, '2026-07-21 07:24:19', '2026-07-21 07:24:19'),
(83, 2, 1, 'project_completed', 'App\\Modules\\Project\\Models\\Project', 2, 'Project PRJ-2026-0002 completed.', NULL, NULL, '2026-07-21 07:24:50', '2026-07-21 07:24:50'),
(84, 4, 1, 'project_created', 'App\\Modules\\Project\\Models\\Project', 4, 'Project PRJ-2026-0004 created.', NULL, NULL, '2026-07-21 12:19:27', '2026-07-21 12:19:27'),
(85, 4, 1, 'service_created', 'App\\Modules\\Project\\Models\\ProjectService', 5, 'Service xv created.', NULL, NULL, '2026-07-21 12:19:40', '2026-07-21 12:19:40'),
(86, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 10, 'Task task A created.', NULL, NULL, '2026-07-21 12:19:58', '2026-07-21 12:19:58'),
(87, 4, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 10, 'Task task A moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-21 13:06:18', '2026-07-21 13:06:18'),
(88, 4, 37, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 10, 'Task task A submitted for review.', NULL, NULL, '2026-07-21 13:06:23', '2026-07-21 13:06:23'),
(89, 4, 1, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 10, 'Task task A approved and completed.', NULL, NULL, '2026-07-21 13:07:02', '2026-07-21 13:07:02'),
(90, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 11, 'Task task B created.', NULL, NULL, '2026-07-21 13:10:39', '2026-07-21 13:10:39'),
(91, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 12, 'Task task 5 created.', NULL, NULL, '2026-07-21 13:11:36', '2026-07-21 13:11:36'),
(92, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 13, 'Task design A created.', NULL, NULL, '2026-07-22 08:31:00', '2026-07-22 08:31:00'),
(93, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 14, 'Task task B created.', NULL, NULL, '2026-07-22 08:31:58', '2026-07-22 08:31:58'),
(94, 4, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 14, 'Task task B moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-23 06:06:04', '2026-07-23 06:06:04'),
(95, 4, 37, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 14, 'Task task B submitted for review.', NULL, NULL, '2026-07-23 06:06:09', '2026-07-23 06:06:09'),
(96, 4, 1, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 14, 'Task task B approved and completed.', NULL, NULL, '2026-07-23 06:06:43', '2026-07-23 06:06:43'),
(97, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 15, 'Task check all commands created.', NULL, NULL, '2026-07-23 06:07:17', '2026-07-23 06:07:17'),
(98, 4, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 15, 'Task check all commands moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-23 06:07:57', '2026-07-23 06:07:57'),
(99, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 16, 'Task task xt created.', NULL, NULL, '2026-07-23 09:51:04', '2026-07-23 09:51:04'),
(100, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 17, 'Task task 34 created.', NULL, NULL, '2026-07-23 09:51:58', '2026-07-23 09:51:58'),
(101, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 18, 'Task task 55 created.', NULL, NULL, '2026-07-23 09:52:43', '2026-07-23 09:52:43'),
(102, 4, 1, 'task_deleted', 'App\\Modules\\Task\\Models\\Task', 18, 'Task task 55 deleted.', NULL, NULL, '2026-07-23 10:22:45', '2026-07-23 10:22:45'),
(103, 4, 1, 'task_deleted', 'App\\Modules\\Task\\Models\\Task', 17, 'Task task 34 deleted.', NULL, NULL, '2026-07-23 10:22:58', '2026-07-23 10:22:58'),
(104, 4, 1, 'task_deleted', 'App\\Modules\\Task\\Models\\Task', 16, 'Task task xt deleted.', NULL, NULL, '2026-07-23 10:23:03', '2026-07-23 10:23:03'),
(105, 4, 1, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 15, 'Task check all commands moved from in_progress to completed.', '{\"status\": \"in_progress\"}', '{\"status\": \"completed\"}', '2026-07-23 10:23:32', '2026-07-23 10:23:32'),
(106, 4, 1, 'task_deleted', 'App\\Modules\\Task\\Models\\Task', 11, 'Task task B deleted.', NULL, NULL, '2026-07-23 10:24:19', '2026-07-23 10:24:19'),
(107, 4, 1, 'task_updated', 'App\\Modules\\Task\\Models\\Task', 13, 'Task design A_editing updated.', '{\"id\": 13, \"title\": \"design A\", \"due_at\": \"2026-07-22T08:30:00.000000Z\", \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"draft\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"high\", \"created_at\": \"2026-07-22T08:31:00.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": \"2026-07-21T18:30:00.000000Z\", \"updated_at\": \"2026-07-22T08:31:00.000000Z\", \"assigned_to\": 37, \"description\": \"weewwe\", \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": 37, \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": \"2.00\", \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '{\"id\": 13, \"title\": \"design A_editing\", \"due_at\": \"2026-07-22T08:30:00.000000Z\", \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"draft\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"high\", \"created_at\": \"2026-07-22T08:31:00.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": \"2026-07-21T18:30:00.000000Z\", \"updated_at\": \"2026-07-23T10:25:00.000000Z\", \"assigned_to\": \"37\", \"description\": \"weewwe\", \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": \"37\", \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": \"2.00\", \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '2026-07-23 10:25:00', '2026-07-23 10:25:00'),
(108, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 19, 'Task qqqq created.', NULL, NULL, '2026-07-23 10:38:48', '2026-07-23 10:38:48'),
(109, 4, 1, 'service_created', 'App\\Modules\\Project\\Models\\ProjectService', 6, 'Service Project Phoenix qw121 created.', NULL, NULL, '2026-07-23 10:39:29', '2026-07-23 10:39:29'),
(110, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 20, 'Task 23eee created.', NULL, NULL, '2026-07-23 10:39:47', '2026-07-23 10:39:47'),
(111, 4, 1, 'task_deleted', 'App\\Modules\\Task\\Models\\Task', 20, 'Task 23eee deleted.', NULL, NULL, '2026-07-23 10:40:25', '2026-07-23 10:40:25'),
(112, 4, 1, 'service_deleted', 'App\\Modules\\Project\\Models\\ProjectService', 6, 'Service Project Phoenix qw121 deleted.', NULL, NULL, '2026-07-23 10:58:29', '2026-07-23 10:58:29'),
(113, 4, 1, 'service_updated', 'App\\Modules\\Project\\Models\\ProjectService', 5, 'Service fisrt services updated.', '{\"id\": 5, \"name\": \"xv\", \"status\": \"in_progress\", \"due_date\": null, \"priority\": \"low\", \"created_at\": \"2026-07-21T12:19:40.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"sort_order\": 0, \"start_date\": null, \"updated_at\": \"2026-07-23T06:06:04.000000Z\", \"assigned_to\": 37, \"description\": null, \"completed_at\": null}', '{\"id\": 5, \"name\": \"fisrt services\", \"status\": \"in_progress\", \"due_date\": null, \"priority\": \"low\", \"created_at\": \"2026-07-21T12:19:40.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"sort_order\": \"0\", \"start_date\": null, \"updated_at\": \"2026-07-23T10:59:00.000000Z\", \"assigned_to\": \"37\", \"description\": null, \"completed_at\": null}', '2026-07-23 10:59:00', '2026-07-23 10:59:00'),
(114, 4, 1, 'task_created', 'App\\Modules\\Task\\Models\\Task', 21, 'Task task 24 created.', NULL, NULL, '2026-07-25 11:28:17', '2026-07-25 11:28:17'),
(115, 4, 1, 'task_updated', 'App\\Modules\\Task\\Models\\Task', 21, 'Task task 24 updated.', '{\"id\": 21, \"title\": \"task 24\", \"due_at\": \"2026-07-25T11:28:00.000000Z\", \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"draft\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"high\", \"created_at\": \"2026-07-25T11:28:17.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": \"2026-07-24T18:30:00.000000Z\", \"updated_at\": \"2026-07-25T11:28:17.000000Z\", \"assigned_to\": 37, \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": 37, \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": \"1.00\", \"requires_review\": false, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '{\"id\": 21, \"title\": \"task 24\", \"due_at\": \"2026-07-28T11:28:00.000000Z\", \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"draft\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"high\", \"created_at\": \"2026-07-25T11:28:17.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": \"2026-07-24T18:30:00.000000Z\", \"updated_at\": \"2026-07-25T11:30:15.000000Z\", \"assigned_to\": \"37\", \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": \"37\", \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": \"18.00\", \"requires_review\": false, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '2026-07-25 11:30:15', '2026-07-25 11:30:15'),
(116, 4, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 21, 'Task task 24 moved from to_do to in_progress.', '{\"status\": \"to_do\"}', '{\"status\": \"in_progress\"}', '2026-07-25 11:31:32', '2026-07-25 11:31:32'),
(117, 4, 37, 'time_tracking_started', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 1, 'Amisha started work on task task 24.', NULL, NULL, '2026-07-25 11:37:21', '2026-07-25 11:37:21'),
(118, 4, 37, 'time_tracking_paused', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 1, 'Amisha paused work on task task 24.', NULL, NULL, '2026-07-25 11:37:55', '2026-07-25 11:37:55'),
(119, 4, 37, 'time_tracking_resumed', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 1, 'Amisha resumed work on task task 24.', NULL, NULL, '2026-07-25 11:38:06', '2026-07-25 11:38:06'),
(120, 4, 37, 'time_tracking_stopped', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 1, 'Amisha ended work on task task 24. Tracked time: 00:02:45.', NULL, NULL, '2026-07-25 11:40:17', '2026-07-25 11:40:17'),
(121, 4, 37, 'time_tracking_started', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 2, 'Amisha started work on task task 24.', NULL, NULL, '2026-07-25 11:40:38', '2026-07-25 11:40:38'),
(122, 4, 37, 'time_tracking_stopped', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 2, 'Amisha ended work on task task 24. Tracked time: 00:00:08.', NULL, NULL, '2026-07-25 11:40:46', '2026-07-25 11:40:46'),
(123, 4, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 21, 'Task task 24 moved from in_progress to completed.', '{\"status\": \"in_progress\"}', '{\"status\": \"completed\"}', '2026-07-25 11:40:59', '2026-07-25 11:40:59'),
(124, 4, 37, 'time_tracking_started', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 3, 'Amisha started work on task qqqq.', NULL, NULL, '2026-07-25 11:56:50', '2026-07-25 11:56:50'),
(125, 4, 37, 'time_tracking_stopped', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 3, 'Amisha ended work on task qqqq. Tracked time: 00:00:57.', NULL, NULL, '2026-07-25 11:57:47', '2026-07-25 11:57:47'),
(126, 4, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 19, 'Task qqqq moved from in_progress to in_progress.', '{\"status\": \"in_progress\"}', '{\"status\": \"in_progress\"}', '2026-07-25 11:57:47', '2026-07-25 11:57:47'),
(127, 4, 37, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 19, 'Task qqqq submitted for review.', NULL, NULL, '2026-07-25 11:57:53', '2026-07-25 11:57:53'),
(128, 4, 1, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 19, 'Task qqqq approved and completed.', NULL, NULL, '2026-07-25 11:58:59', '2026-07-25 11:58:59'),
(129, 4, 37, 'time_tracking_started', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 4, 'Amisha started work on task design A_editing.', NULL, NULL, '2026-07-25 12:17:38', '2026-07-25 12:17:38'),
(130, 4, 37, 'time_tracking_paused', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 4, 'Amisha paused work on task design A_editing.', NULL, NULL, '2026-07-25 12:18:00', '2026-07-25 12:18:00'),
(131, 4, 37, 'time_tracking_resumed', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 4, 'Amisha resumed work on task design A_editing.', NULL, NULL, '2026-07-25 12:18:07', '2026-07-25 12:18:07'),
(132, 4, 37, 'time_tracking_stopped', 'App\\Modules\\TimeTracking\\Models\\TimeEntry', 4, 'Amisha ended work on task design A_editing. Tracked time: 00:01:09.', NULL, NULL, '2026-07-25 12:18:54', '2026-07-25 12:18:54'),
(133, 4, 37, 'task_status_updated', 'App\\Modules\\Task\\Models\\Task', 13, 'Task design A_editing moved from in_progress to in_progress.', '{\"status\": \"in_progress\"}', '{\"status\": \"in_progress\"}', '2026-07-25 12:18:54', '2026-07-25 12:18:54'),
(134, 4, 37, 'task_submitted_for_review', 'App\\Modules\\Task\\Models\\Task', 13, 'Task design A_editing submitted for review.', NULL, NULL, '2026-07-25 12:19:05', '2026-07-25 12:19:05'),
(135, 4, 1, 'task_approved', 'App\\Modules\\Task\\Models\\Task', 13, 'Task design A_editing approved and completed.', NULL, NULL, '2026-07-25 12:19:47', '2026-07-25 12:19:47'),
(136, 4, 1, 'task_excel_imported', 'App\\Modules\\Task\\Models\\Task', 22, 'Task Design Homepage Layout created through Excel import.', NULL, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08'),
(137, 4, 1, 'task_excel_imported', 'App\\Modules\\Task\\Models\\Task', 23, 'Task Develop Contact Form created through Excel import.', NULL, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08'),
(138, 4, 1, 'task_excel_imported', 'App\\Modules\\Task\\Models\\Task', 24, 'Task Mobile Responsive Testing created through Excel import.', NULL, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08'),
(139, 4, 1, 'task_excel_imported', 'App\\Modules\\Task\\Models\\Task', 25, 'Task Optimize Page Loading Speed created through Excel import.', NULL, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08'),
(140, 4, 1, 'task_excel_imported', 'App\\Modules\\Task\\Models\\Task', 26, 'Task Final Quality Assurance created through Excel import.', NULL, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08'),
(141, 4, 1, 'task_updated', 'App\\Modules\\Task\\Models\\Task', 12, 'Task task 5 updated.', '{\"id\": 12, \"title\": \"task 5\", \"due_at\": null, \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"active\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-25T11:37:21.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"medium\", \"created_at\": \"2026-07-21T13:11:36.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": null, \"updated_at\": \"2026-07-21T13:11:36.000000Z\", \"assigned_to\": 37, \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": 37, \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": null, \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '{\"id\": 12, \"title\": \"task 5\", \"due_at\": null, \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"active\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-25T11:37:21.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"low\", \"created_at\": \"2026-07-21T13:11:36.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": null, \"updated_at\": \"2026-07-31T10:17:17.000000Z\", \"assigned_to\": \"37\", \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": \"37\", \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": null, \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '2026-07-31 10:17:17', '2026-07-31 10:17:17'),
(142, 4, 1, 'task_updated', 'App\\Modules\\Task\\Models\\Task', 12, 'Task task 5 updated.', '{\"id\": 12, \"title\": \"task 5\", \"due_at\": null, \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"active\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-25T11:37:21.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"low\", \"created_at\": \"2026-07-21T13:11:36.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": null, \"updated_at\": \"2026-07-31T10:17:17.000000Z\", \"assigned_to\": 37, \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": 37, \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": null, \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '{\"id\": 12, \"title\": \"task 5\", \"due_at\": null, \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"active\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-25T11:37:21.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"high\", \"created_at\": \"2026-07-21T13:11:36.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": null, \"updated_at\": \"2026-07-31T10:18:10.000000Z\", \"assigned_to\": \"37\", \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": \"37\", \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": null, \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '2026-07-31 10:18:10', '2026-07-31 10:18:10');
INSERT INTO `project_activities` (`id`, `project_id`, `user_id`, `action`, `subject_type`, `subject_id`, `description`, `old_values`, `new_values`, `created_at`, `updated_at`) VALUES
(143, 4, 1, 'task_updated', 'App\\Modules\\Task\\Models\\Task', 12, 'Task task 5 updated.', '{\"id\": 12, \"title\": \"task 5\", \"due_at\": null, \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"active\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-25T11:37:21.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"high\", \"created_at\": \"2026-07-21T13:11:36.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": null, \"updated_at\": \"2026-07-31T10:18:10.000000Z\", \"assigned_to\": 37, \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": 37, \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": null, \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '{\"id\": 12, \"title\": \"task 5\", \"due_at\": null, \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"active\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-25T11:37:21.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"immedately\", \"created_at\": \"2026-07-21T13:11:36.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": null, \"updated_at\": \"2026-07-31T10:20:41.000000Z\", \"assigned_to\": \"37\", \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": \"37\", \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": null, \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '2026-07-31 10:20:41', '2026-07-31 10:20:41'),
(144, 4, 1, 'task_updated', 'App\\Modules\\Task\\Models\\Task', 12, 'Task task 5 updated.', '{\"id\": 12, \"title\": \"task 5\", \"due_at\": null, \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"active\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-25T11:37:21.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"immedately\", \"created_at\": \"2026-07-21T13:11:36.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": null, \"updated_at\": \"2026-07-31T10:20:41.000000Z\", \"assigned_to\": 37, \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": 37, \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": null, \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '{\"id\": 12, \"title\": \"task 5\", \"due_at\": null, \"status\": \"to_do\", \"project\": {\"id\": 4, \"name\": \"Project Phoenix22\", \"budget\": null, \"status\": \"active\", \"members\": [{\"id\": 37, \"name\": \"Amisha\", \"email\": \"amisha@gmail.com\", \"pivot\": {\"user_id\": 37, \"added_by\": 1, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"project_id\": 4, \"updated_at\": \"2026-07-21T12:19:27.000000Z\", \"member_role\": \"Project Manager\"}, \"role_id\": 7, \"is_active\": true, \"created_at\": \"2026-07-20T12:01:04.000000Z\", \"updated_at\": \"2026-07-20T12:01:04.000000Z\", \"email_verified_at\": null}], \"due_date\": null, \"priority\": \"medium\", \"client_id\": 11, \"created_at\": \"2026-07-21T12:19:27.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"start_date\": null, \"updated_at\": \"2026-07-25T11:37:21.000000Z\", \"description\": null, \"completed_at\": null, \"project_code\": \"PRJ-2026-0004\", \"project_manager_id\": 37}, \"priority\": \"high\", \"created_at\": \"2026-07-21T13:11:36.000000Z\", \"created_by\": 1, \"deleted_at\": null, \"project_id\": 4, \"start_date\": null, \"updated_at\": \"2026-07-31T10:21:41.000000Z\", \"assigned_to\": \"37\", \"description\": null, \"review_note\": null, \"reviewed_at\": null, \"reviewer_id\": \"37\", \"completed_at\": null, \"parent_task_id\": null, \"estimated_hours\": null, \"requires_review\": true, \"progress_percent\": 0, \"project_service_id\": 5, \"submitted_for_review_at\": null}', '2026-07-31 10:21:41', '2026-07-31 10:21:41');

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `member_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `user_id`, `member_role`, `added_by`, `created_at`, `updated_at`) VALUES
(1, 1, 19, NULL, 1, '2026-07-18 10:48:01', '2026-07-18 10:48:01'),
(3, 1, 21, 'manager', 1, '2026-07-18 10:50:38', '2026-07-18 10:50:38'),
(4, 2, 35, 'Project Manager', 1, '2026-07-20 09:40:43', '2026-07-20 09:45:18'),
(5, 2, 36, 'developer', 1, '2026-07-20 09:41:28', '2026-07-20 09:41:28'),
(6, 3, 35, 'Project Manager', 35, '2026-07-20 12:57:30', '2026-07-20 12:57:30'),
(7, 3, 37, 'designer', 35, '2026-07-20 12:58:33', '2026-07-20 12:58:33'),
(8, 3, 36, 'developer', 35, '2026-07-20 12:58:42', '2026-07-20 12:58:42'),
(9, 4, 37, 'Project Manager', 1, '2026-07-21 12:19:27', '2026-07-21 12:19:27');

-- --------------------------------------------------------

--
-- Table structure for table `project_services`
--

CREATE TABLE `project_services` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `priority` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `completed_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_services`
--

INSERT INTO `project_services` (`id`, `project_id`, `name`, `description`, `assigned_to`, `priority`, `status`, `start_date`, `due_date`, `sort_order`, `completed_at`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Landing page coding part', 'landing page coding part.', 20, 'urgent', 'completed', '2026-07-18', '2026-07-19', 0, '2026-07-18 17:00:18', 1, '2026-07-18 10:53:19', '2026-07-18 11:30:18', NULL),
(2, 2, 'lLANDING PAGE CREATE', 'LANDING PAGE CREATE.', 36, 'low', 'completed', '2026-07-20', '2026-07-25', 0, '2026-07-20 15:34:02', 1, '2026-07-20 09:42:32', '2026-07-20 10:04:02', NULL),
(3, 3, 'Landing Page UI/UX Design', 'Create desktop and mobile landing page design.', 37, 'high', 'completed', '2026-07-20', '2026-07-23', 0, '2026-07-21 12:52:52', 35, '2026-07-20 13:03:00', '2026-07-21 07:22:52', NULL),
(4, 3, 'Landing Page Development', 'Convert approved design into a responsive landing page.', 36, 'high', 'completed', '2026-07-20', '2026-07-23', 0, '2026-07-21 12:54:06', 35, '2026-07-20 13:03:48', '2026-07-21 07:24:06', NULL),
(5, 4, 'fisrt services', NULL, 37, 'low', 'in_progress', NULL, NULL, 0, NULL, 1, '2026-07-21 12:19:40', '2026-07-23 10:59:00', NULL),
(6, 4, 'Project Phoenix qw121', 'eewewweeew', 37, 'high', 'in_progress', '2026-07-23', '2026-07-23', 0, NULL, 1, '2026-07-23 10:39:29', '2026-07-23 10:58:29', '2026-07-23 10:58:29');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_protected` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `is_default`, `is_protected`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super-admin', 0, 1, '2026-07-10 04:38:10', '2026-07-10 04:38:10'),
(2, 'Admin', 'admin', 0, 1, '2026-07-10 04:38:10', '2026-07-10 04:38:10'),
(3, 'Manager', 'manager', 0, 0, '2026-07-10 04:38:10', '2026-07-10 04:38:10'),
(4, 'Sales Executive', 'sales-executive', 0, 0, '2026-07-10 04:38:10', '2026-07-10 04:38:10'),
(5, 'User', 'user', 1, 0, '2026-07-10 04:38:10', '2026-07-10 04:38:10'),
(6, 'developer', 'developer', 0, 0, '2026-07-18 13:12:06', '2026-07-18 13:12:06'),
(7, 'designer', 'designer', 0, 0, '2026-07-20 11:57:12', '2026-07-20 11:57:12');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('VbtHcNL7U603yT65P6EY67m0hEtspSGycOFTbXtn', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJuYzVYSlJOeFdxMjF3Ynp6Z2JrYnRib0hvalA3Y3hMZWJIZ2wxR0dlIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvcHJvX2NybXMudGVzdFwvbGVhZCIsInJvdXRlIjoibGVhZC5pbmRleCJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1785501594);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `group_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` longtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `group_name`, `setting_key`, `setting_value`, `type`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'general', 'company_name', 'Oracle CRM', 'text', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(2, 'general', 'company_email', 'info@oraclegmail.com', 'email', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(3, 'general', 'company_phone', '+91980624794', 'text', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(4, 'general', 'company_website', 'https://www.w3schools.com/', 'url', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(5, 'general', 'company_address', 'Sirsa Gate Square, 3, GE Road, Bajrang para', 'textarea', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(6, 'regional', 'timezone', 'Asia/Kolkata', 'select', 0, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(7, 'regional', 'date_format', 'd-m-Y', 'select', 0, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(8, 'regional', 'time_format', 'h:i A', 'select', 0, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(9, 'regional', 'currency_code', 'INR', 'text', 0, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(10, 'regional', 'currency_symbol', '₹', 'text', 0, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(11, 'branding', 'company_logo', '', 'file', 1, '2026-07-30 10:32:45', '2026-07-30 10:33:49'),
(12, 'branding', 'favicon', '', 'file', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(13, 'branding', 'primary_color', '#2543DA', 'color', 1, '2026-07-30 10:32:45', '2026-07-30 10:33:49'),
(14, 'branding', 'secondary_color', '#0F172A', 'color', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(15, 'branding', 'show_company_logo', '1', 'boolean', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(16, 'branding', 'sidebar_subtitle', 'Admin Panel', 'text', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(17, 'branding', 'footer_text', 'Powered by PRO CRM', 'text', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(18, 'login', 'login_heading', 'Welcome Back, Admin', 'text', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45'),
(19, 'login', 'login_description', 'Login to manage your leads, clients, projects, tasks and CRM settings from one secure dashboard.', 'textarea', 1, '2026-07-30 10:32:45', '2026-07-30 10:32:45');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `project_service_id` bigint UNSIGNED NOT NULL,
  `parent_task_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `priority` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'to_do',
  `progress_percent` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `requires_review` tinyint(1) NOT NULL DEFAULT '1',
  `reviewer_id` bigint UNSIGNED DEFAULT NULL,
  `submitted_for_review_at` datetime DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `project_service_id`, `parent_task_id`, `title`, `description`, `assigned_to`, `created_by`, `priority`, `status`, `progress_percent`, `requires_review`, `reviewer_id`, `submitted_for_review_at`, `reviewed_at`, `review_note`, `start_date`, `due_at`, `estimated_hours`, `completed_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, 'design landing page', 'wqewwewe', 19, 21, 'high', 'completed', 100, 1, 21, '2026-07-18 16:58:58', '2026-07-18 17:00:18', 'worl completed', '2026-07-20', '2026-07-25 16:54:00', 18.00, '2026-07-18 17:00:18', '2026-07-18 11:25:15', '2026-07-18 11:30:18', NULL),
(2, 2, 2, NULL, 'CREATE LANDING PAGE CODEING PART', NULL, 36, 1, 'high', 'completed', 100, 1, 35, '2026-07-20 15:32:13', '2026-07-20 15:34:02', 'project done.', '2026-07-20', '2026-07-25 15:13:00', 40.00, '2026-07-20 15:34:02', '2026-07-20 09:43:40', '2026-07-20 10:04:02', NULL),
(3, 2, 2, NULL, 'FRONTEND DESIGN', NULL, 36, 1, 'medium', 'completed', 100, 0, 35, NULL, NULL, NULL, '2026-07-20', '2026-07-24 15:15:00', 24.00, '2026-07-20 15:21:04', '2026-07-20 09:46:08', '2026-07-20 09:51:04', NULL),
(4, 3, 3, NULL, 'design header', 'design header', 37, 35, 'medium', 'completed', 100, 1, 35, '2026-07-20 18:55:13', '2026-07-20 18:55:22', NULL, '2026-07-20', '2026-07-20 18:35:00', 2.00, '2026-07-20 18:55:22', '2026-07-20 13:05:44', '2026-07-20 13:25:22', NULL),
(5, 3, 3, NULL, 'design body', 'design body', 37, 35, 'high', 'completed', 100, 1, 35, '2026-07-20 18:57:38', '2026-07-20 18:57:43', NULL, '2026-07-20', '2026-07-22 18:36:00', 3.00, '2026-07-20 18:57:43', '2026-07-20 13:06:55', '2026-07-20 13:27:43', NULL),
(6, 3, 3, NULL, 'design footer', 'design footer', 37, 35, 'high', 'completed', 100, 1, 35, '2026-07-21 12:51:42', '2026-07-21 12:52:52', NULL, '2026-07-20', '2026-07-21 18:37:00', 4.00, '2026-07-21 12:52:52', '2026-07-20 13:07:50', '2026-07-21 07:22:52', NULL),
(7, 3, 4, NULL, 'develop header', 'develop header', 36, 35, 'high', 'completed', 100, 1, 35, '2026-07-20 18:58:11', '2026-07-20 18:58:15', NULL, '2026-07-20', '2026-07-23 18:38:00', 3.00, '2026-07-20 18:58:15', '2026-07-20 13:08:28', '2026-07-20 13:28:15', NULL),
(8, 3, 4, NULL, 'develop body', 'develop header', 36, 35, 'high', 'completed', 100, 1, 1, '2026-07-21 12:50:29', '2026-07-21 12:53:55', NULL, '2026-07-20', '2026-07-22 18:38:00', 4.00, '2026-07-21 12:53:55', '2026-07-20 13:09:01', '2026-07-21 07:23:55', NULL),
(9, 3, 4, NULL, 'develop footer', 'develop footer', 36, 35, 'high', 'completed', 100, 1, 1, '2026-07-21 12:53:28', '2026-07-21 12:54:06', NULL, '2026-07-20', '2026-07-21 18:39:00', 5.00, '2026-07-21 12:54:06', '2026-07-20 13:09:43', '2026-07-21 07:24:06', NULL),
(10, 4, 5, NULL, 'task A', NULL, 37, 1, 'medium', 'completed', 100, 1, 1, '2026-07-21 18:36:23', '2026-07-21 18:37:02', NULL, NULL, NULL, NULL, '2026-07-21 18:37:02', '2026-07-21 12:19:58', '2026-07-21 13:07:02', NULL),
(11, 4, 5, NULL, 'task B', NULL, 37, 1, 'high', 'to_do', 0, 0, 37, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-21 13:10:39', '2026-07-23 10:24:19', '2026-07-23 10:24:19'),
(12, 4, 5, NULL, 'task 5', NULL, 37, 1, 'high', 'to_do', 0, 1, 37, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-21 13:11:36', '2026-07-31 10:21:41', NULL),
(13, 4, 5, NULL, 'design A_editing', 'weewwe', 37, 1, 'high', 'completed', 100, 1, 1, '2026-07-25 17:49:05', '2026-07-25 17:49:47', 'fdfdfdfd', '2026-07-22', '2026-07-22 14:00:00', 2.00, '2026-07-25 17:49:47', '2026-07-22 08:31:00', '2026-07-25 12:19:47', NULL),
(14, 4, 5, NULL, 'task B', 'dsds', 37, 1, 'urgent', 'completed', 100, 1, 1, '2026-07-23 11:36:09', '2026-07-23 11:36:43', NULL, '2026-07-22', '2026-07-22 14:01:00', 4.00, '2026-07-23 11:36:43', '2026-07-22 08:31:58', '2026-07-23 06:06:43', NULL),
(15, 4, 5, NULL, 'check all commands', NULL, 37, 1, 'medium', 'completed', 100, 0, 37, NULL, NULL, NULL, '2026-07-23', '2026-07-23 11:37:00', 1.00, '2026-07-23 15:53:32', '2026-07-23 06:07:17', '2026-07-23 10:23:32', NULL),
(16, 4, 5, NULL, 'task xt', '3wwweewweew', 37, 1, 'high', 'to_do', 0, 1, 37, NULL, NULL, NULL, '2026-07-23', '2026-07-23 15:20:00', 3.00, NULL, '2026-07-23 09:51:04', '2026-07-23 10:23:03', '2026-07-23 10:23:03'),
(17, 4, 5, NULL, 'task 34', NULL, 37, 1, 'high', 'to_do', 0, 0, 37, NULL, NULL, NULL, '2026-07-31', '2026-07-23 15:21:00', 23.00, NULL, '2026-07-23 09:51:58', '2026-07-23 10:22:58', '2026-07-23 10:22:58'),
(18, 4, 5, NULL, 'task 55', 'eewweew', 37, 1, 'high', 'to_do', 0, 1, 37, NULL, NULL, NULL, '2026-07-23', '2026-07-23 15:22:00', 3.00, NULL, '2026-07-23 09:52:43', '2026-07-23 10:22:45', '2026-07-23 10:22:45'),
(19, 4, 5, NULL, 'qqqq', NULL, 37, 1, 'high', 'completed', 100, 1, 1, '2026-07-25 17:27:53', '2026-07-25 17:28:59', NULL, '2026-07-16', NULL, NULL, '2026-07-25 17:28:59', '2026-07-23 10:38:48', '2026-07-25 11:58:59', NULL),
(20, 4, 6, NULL, '23eee', NULL, 37, 1, 'medium', 'to_do', 0, 1, 37, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 10:39:47', '2026-07-23 10:40:25', '2026-07-23 10:40:25'),
(21, 4, 5, NULL, 'task 24', NULL, 37, 1, 'high', 'completed', 100, 0, 37, NULL, NULL, NULL, '2026-07-25', '2026-07-28 16:58:00', 18.00, '2026-07-25 17:10:59', '2026-07-25 11:28:17', '2026-07-25 11:40:59', NULL),
(22, 4, 5, NULL, 'Design Homepage Layout', 'Create a clean and responsive homepage layout according to the approved design.', NULL, 1, 'high', 'to_do', 0, 1, NULL, NULL, NULL, NULL, '2026-08-01', '2026-08-03 18:00:00', 8.00, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08', NULL),
(23, 4, 5, NULL, 'Develop Contact Form', 'Build the contact form with validation and save submitted inquiries in the CRM.', NULL, 1, 'medium', 'to_do', 0, 1, NULL, NULL, NULL, NULL, '2026-08-02', '2026-08-05 18:00:00', 6.00, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08', NULL),
(24, 4, 5, NULL, 'Mobile Responsive Testing', 'Test the website on common mobile and tablet screen sizes and fix layout issues.', NULL, 1, 'medium', 'to_do', 0, 0, NULL, NULL, NULL, NULL, '2026-08-04', '2026-08-06 17:00:00', 4.00, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08', NULL),
(25, 4, 5, NULL, 'Optimize Page Loading Speed', 'Compress assets, optimize images and reduce unnecessary scripts to improve performance.', NULL, 1, 'high', 'to_do', 0, 1, NULL, NULL, NULL, NULL, '2026-08-05', '2026-08-08 18:00:00', 5.00, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08', NULL),
(26, 4, 5, NULL, 'Final Quality Assurance', 'Check links, forms, browser compatibility and final content before client delivery.', NULL, 1, 'urgent', 'to_do', 0, 1, NULL, NULL, NULL, NULL, '2026-08-08', '2026-08-10 17:00:00', 6.00, NULL, '2026-07-29 10:47:08', '2026-07-29 10:47:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `uploaded_by` bigint UNSIGNED DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_attachments`
--

INSERT INTO `task_attachments` (`id`, `task_id`, `uploaded_by`, `original_name`, `stored_name`, `file_path`, `mime_type`, `file_size`, `created_at`, `updated_at`) VALUES
(1, 1, 21, 'final 2.png', 'f46e4d6c-f745-4055-92e9-4edd1c34476c.png', 'task-attachments/1/f46e4d6c-f745-4055-92e9-4edd1c34476c.png', 'image/png', 769817, '2026-07-18 11:26:00', '2026-07-18 11:26:00'),
(2, 1, 19, 'ChatGPT Image Jun 19, 2026, 03_55_29 PM.png', 'b44cb5e6-be00-4c03-9b3e-818cc0b93286.png', 'task-attachments/1/b44cb5e6-be00-4c03-9b3e-818cc0b93286.png', 'image/png', 1894103, '2026-07-18 11:29:12', '2026-07-18 11:29:12'),
(3, 2, 1, 'WhatsApp Image 2026-07-10 at 11.11.36 AM.jpeg', '40b8d60e-b625-4feb-bd07-33391bbf1421.jpeg', 'task-attachments/2/40b8d60e-b625-4feb-bd07-33391bbf1421.jpeg', 'image/jpeg', 305421, '2026-07-20 09:44:44', '2026-07-20 09:44:44'),
(4, 3, 1, 'photo_6264676467052581143_y.jpg', 'aacf7e27-790b-473f-8fd3-f1193f6653f8.jpg', 'task-attachments/3/aacf7e27-790b-473f-8fd3-f1193f6653f8.jpg', 'image/jpeg', 261040, '2026-07-20 09:46:47', '2026-07-20 09:46:47');

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `edited_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_comments`
--

INSERT INTO `task_comments` (`id`, `task_id`, `user_id`, `comment`, `edited_at`, `created_at`, `updated_at`) VALUES
(1, 1, 21, 'work fast.', NULL, '2026-07-18 11:26:13', '2026-07-18 11:26:13'),
(2, 1, 19, 'comkpleted', NULL, '2026-07-18 11:28:11', '2026-07-18 11:28:11'),
(3, 2, 1, 'WORK FAST BRO.', NULL, '2026-07-20 09:44:28', '2026-07-20 09:44:28'),
(4, 3, 1, 'WORK FAST.', NULL, '2026-07-20 09:46:37', '2026-07-20 09:46:37'),
(5, 2, 35, 'WORK START KRO LUCKY.', NULL, '2026-07-20 09:48:25', '2026-07-20 09:48:25'),
(6, 3, 36, 'https://suchisemicon.com/', NULL, '2026-07-20 09:51:54', '2026-07-20 09:51:54'),
(7, 2, 36, 'pitamber sir review the project.', NULL, '2026-07-20 10:02:35', '2026-07-20 10:02:35');

-- --------------------------------------------------------

--
-- Table structure for table `task_dependencies`
--

CREATE TABLE `task_dependencies` (
  `id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `depends_on_task_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_dependencies`
--

INSERT INTO `task_dependencies` (`id`, `task_id`, `depends_on_task_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 7, 4, 35, '2026-07-20 13:21:53', '2026-07-20 13:21:53'),
(2, 8, 5, 35, '2026-07-20 13:22:36', '2026-07-20 13:22:36'),
(3, 9, 6, 35, '2026-07-20 13:22:50', '2026-07-20 13:22:50');

-- --------------------------------------------------------

--
-- Table structure for table `task_priorities`
--

CREATE TABLE `task_priorities` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#64748B',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_priorities`
--

INSERT INTO `task_priorities` (`id`, `name`, `slug`, `color`, `is_default`, `is_active`, `is_system`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Low', 'low', '#64748B', 0, 1, 0, 10, '2026-07-31 10:13:36', '2026-07-31 10:19:18'),
(2, 'Medium', 'medium', '#2563EB', 1, 1, 1, 20, '2026-07-31 10:13:36', '2026-07-31 10:19:18'),
(3, 'High', 'high', '#EA580C', 0, 1, 0, 30, '2026-07-31 10:13:36', '2026-07-31 10:19:18'),
(4, 'Urgent', 'urgent', '#DC2626', 0, 1, 0, 40, '2026-07-31 10:13:36', '2026-07-31 10:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `task_statuses`
--

CREATE TABLE `task_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `system_key` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#64748B',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `is_manual_selectable` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_statuses`
--

INSERT INTO `task_statuses` (`id`, `name`, `slug`, `system_key`, `color`, `is_default`, `is_active`, `is_closed`, `is_manual_selectable`, `is_system`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'To Do', 'to_do', 'to_do', '#64748B', 1, 1, 0, 1, 1, 10, '2026-07-31 10:13:36', '2026-07-31 10:13:36'),
(2, 'In Progress', 'in_progress', 'in_progress', '#2563EB', 0, 1, 0, 1, 1, 20, '2026-07-31 10:13:36', '2026-07-31 10:13:36'),
(3, 'In Review', 'in_review', 'in_review', '#7C3AED', 0, 1, 0, 0, 1, 30, '2026-07-31 10:13:36', '2026-07-31 10:13:36'),
(4, 'Blocked', 'blocked', 'blocked', '#EA580C', 0, 1, 0, 0, 1, 40, '2026-07-31 10:13:36', '2026-07-31 10:13:36'),
(5, 'Completed', 'completed', 'completed', '#059669', 0, 1, 1, 1, 1, 50, '2026-07-31 10:13:36', '2026-07-31 10:13:36'),
(6, 'Cancelled', 'cancelled', 'cancelled', '#DC2626', 0, 1, 1, 1, 1, 60, '2026-07-31 10:13:36', '2026-07-31 10:13:36');

-- --------------------------------------------------------

--
-- Table structure for table `time_entries`
--

CREATE TABLE `time_entries` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `task_id` bigint UNSIGNED DEFAULT NULL,
  `project_id` bigint UNSIGNED DEFAULT NULL,
  `project_service_id` bigint UNSIGNED DEFAULT NULL,
  `active_key` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'running',
  `started_at` datetime NOT NULL,
  `last_started_at` datetime DEFAULT NULL,
  `paused_at` datetime DEFAULT NULL,
  `stopped_at` datetime DEFAULT NULL,
  `total_seconds` bigint UNSIGNED NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_name_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_name_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `member_role_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `stopped_by` bigint UNSIGNED DEFAULT NULL,
  `stop_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_entries`
--

INSERT INTO `time_entries` (`id`, `user_id`, `role_id`, `task_id`, `project_id`, `project_service_id`, `active_key`, `status`, `started_at`, `last_started_at`, `paused_at`, `stopped_at`, `total_seconds`, `notes`, `user_name_snapshot`, `role_name_snapshot`, `member_role_snapshot`, `created_by`, `stopped_by`, `stop_reason`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 37, 7, 21, 4, 5, NULL, 'stopped', '2026-07-25 17:07:21', NULL, NULL, '2026-07-25 17:10:17', 165, NULL, 'Amisha', 'designer', 'Project Manager', 37, 37, 'User ended work', '2026-07-25 11:37:21', '2026-07-25 11:40:17', NULL),
(2, 37, 7, 21, 4, 5, NULL, 'stopped', '2026-07-25 17:10:38', NULL, NULL, '2026-07-25 17:10:46', 8, NULL, 'Amisha', 'designer', 'Project Manager', 37, 37, 'User ended work', '2026-07-25 11:40:38', '2026-07-25 11:40:46', NULL),
(3, 37, 7, 19, 4, 5, NULL, 'stopped', '2026-07-25 17:26:50', NULL, NULL, '2026-07-25 17:27:47', 57, NULL, 'Amisha', 'designer', 'Project Manager', 37, 37, 'Task submitted for review', '2026-07-25 11:56:50', '2026-07-25 11:57:47', NULL),
(4, 37, 7, 13, 4, 5, NULL, 'stopped', '2026-07-25 17:47:38', NULL, NULL, '2026-07-25 17:48:54', 69, NULL, 'Amisha', 'designer', 'Project Manager', 37, 37, 'Task submitted for review', '2026-07-25 12:17:38', '2026-07-25 12:18:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `time_entry_breaks`
--

CREATE TABLE `time_entry_breaks` (
  `id` bigint UNSIGNED NOT NULL,
  `time_entry_id` bigint UNSIGNED NOT NULL,
  `paused_at` datetime NOT NULL,
  `resumed_at` datetime DEFAULT NULL,
  `break_seconds` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_entry_breaks`
--

INSERT INTO `time_entry_breaks` (`id`, `time_entry_id`, `paused_at`, `resumed_at`, `break_seconds`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-07-25 17:07:55', '2026-07-25 17:08:06', 11, '2026-07-25 11:37:55', '2026-07-25 11:38:06'),
(2, 4, '2026-07-25 17:48:00', '2026-07-25 17:48:07', 7, '2026-07-25 12:18:00', '2026-07-25 12:18:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 1, 'Admin', 'admin@gmail.com', NULL, '$2y$12$X/XeOB1Nmx8myAz9Yn9cKOClSVbJ.HRVyxxkN6KIg7fCWEuNWBXYK', NULL, '2026-07-09 02:15:27', '2026-07-09 02:15:27', 1),
(2, 3, 'Manager', 'user1@gmail.com', NULL, '$2y$12$k1uIoM539UhO5ZuXV3JPAeMvc/hhdo6.NzNYJ6uy9FxbO0bI1i26O', NULL, '2026-07-09 13:28:18', '2026-07-09 13:28:18', 1),
(3, 4, 'Sales Executive', 'user2@gmail.com', NULL, '$2y$12$k1uIoM539UhO5ZuXV3JPAeMvc/hhdo6.NzNYJ6uy9FxbO0bI1i26O', NULL, '2026-07-09 13:28:18', '2026-07-09 13:28:18', 1),
(4, 5, 'User Three', 'user3@gmail.com', NULL, '$2y$12$k1uIoM539UhO5ZuXV3JPAeMvc/hhdo6.NzNYJ6uy9FxbO0bI1i26O', NULL, '2026-07-09 13:28:18', '2026-07-09 13:28:18', 1),
(5, 5, 'User Four', 'user4@gmail.com', NULL, '$2y$12$k1uIoM539UhO5ZuXV3JPAeMvc/hhdo6.NzNYJ6uy9FxbO0bI1i26O', NULL, '2026-07-09 13:28:18', '2026-07-09 13:28:18', 1),
(6, 5, 'User Five', 'user5@gmail.com', NULL, '$2y$12$k1uIoM539UhO5ZuXV3JPAeMvc/hhdo6.NzNYJ6uy9FxbO0bI1i26O', NULL, '2026-07-09 13:28:18', '2026-07-09 13:28:18', 1),
(7, 5, 'User Six', 'user6@gmail.com', NULL, '$2y$12$k1uIoM539UhO5ZuXV3JPAeMvc/hhdo6.NzNYJ6uy9FxbO0bI1i26O', NULL, '2026-07-09 13:28:18', '2026-07-09 13:28:18', 1),
(8, 5, 'User Seven', 'user7@gmail.com', NULL, '$2y$12$k1uIoM539UhO5ZuXV3JPAeMvc/hhdo6.NzNYJ6uy9FxbO0bI1i26O', NULL, '2026-07-09 13:28:18', '2026-07-09 13:28:18', 1),
(9, 5, 'User Eight', 'user8@gmail.com', NULL, '$2y$12$k1uIoM539UhO5ZuXV3JPAeMvc/hhdo6.NzNYJ6uy9FxbO0bI1i26O', NULL, '2026-07-09 13:28:18', '2026-07-09 13:28:18', 1),
(10, 5, 'User Nine', 'user9@gmail.com', NULL, '$2y$12$k1uIoM539UhO5ZuXV3JPAeMvc/hhdo6.NzNYJ6uy9FxbO0bI1i26O', NULL, '2026-07-09 13:28:18', '2026-07-09 13:28:18', 1),
(14, 3, 'User 15', 'user15@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-16 01:50:42', 1),
(15, 4, 'User 16', 'user16@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(16, 5, 'User 17', 'user17@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(17, 5, 'User 18', 'user18@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(18, 3, 'User 19', 'user19@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(19, 3, 'User 20', 'user20@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(20, 3, 'User 21', 'user21@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(21, 3, 'User 22', 'user22@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(22, 5, 'User 23', 'user23@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(23, 5, 'User 24', 'user24@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(24, 5, 'User 25', 'user25@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(25, 5, 'User 26', 'user26@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(26, 5, 'User 27', 'user27@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(27, 5, 'User 28', 'user28@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(28, 5, 'User 29', 'user29@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(29, 5, 'User 30', 'user30@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(30, 5, 'User 31', 'user31@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(31, 5, 'User 32', 'user32@gmail.com', NULL, '$2y$12$hewSo1iqUbJ./iYHinlIreFZeHLOwKGVWhzWzhKk1sRBWGzL0VzFy', NULL, '2026-07-10 06:14:20', '2026-07-10 06:14:20', 1),
(32, 5, 'salman', 'salman@gmail.com', NULL, '$2y$12$T/d/OEjp.NWIvxnZM.gTFOQ3cGdNtnW2TVvcNAu2Nf9W6MNMBiQXS', NULL, '2026-07-10 02:38:44', '2026-07-10 02:38:44', 1),
(33, 5, 'Rajni', 'rajani@gmail.com', NULL, '$2y$12$hPSb2EOsNeGQ/FuDG4hVUe0z17khHZJRVDyJw/shF8OinUzmG5RgW', NULL, '2026-07-10 05:25:47', '2026-07-10 05:25:47', 1),
(34, 4, 'aditya sir', 'aditya@gmail.com', NULL, '$2y$12$P0tcK/1pkf9yD25xFtETZeSz/FWbjUQUOzzwN1f..b4PPedGLDZrq', NULL, '2026-07-18 12:36:26', '2026-07-18 12:36:26', 1),
(35, 3, 'pitamber sir', 'pitamber@gmail.com', NULL, '$2y$12$RSKlyQ/nibjVBzNWQDqbs.eSo6vX3vhEq96CmT6Q4JOv./xNZENqS', NULL, '2026-07-18 12:38:48', '2026-07-18 12:38:48', 1),
(36, 6, 'Lucky sir', 'lucky@gmail.com', NULL, '$2y$12$Pp5oT/p6S4mnH/HNHdrdz.z2iapsMJtvYthWmgurEHdMEHU1QPMWK', NULL, '2026-07-18 12:42:33', '2026-07-20 09:35:41', 1),
(37, 7, 'Amisha', 'amisha@gmail.com', NULL, '$2y$12$BvtwmxUlm/S00AWKHPPrYuxPMZkX8gyHQQeJntV4UGk8QChWf5yhO', NULL, '2026-07-20 12:01:04', '2026-07-20 12:01:04', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_lead_id_unique` (`lead_id`),
  ADD KEY `clients_assigned_to_foreign` (`assigned_to`),
  ADD KEY `clients_created_by_foreign` (`created_by`),
  ADD KEY `clients_status_index` (`status`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leads_assigned_to_foreign` (`assigned_to`),
  ADD KEY `leads_created_by_foreign` (`created_by`),
  ADD KEY `leads_source_index` (`source`),
  ADD KEY `leads_status_index` (`status`),
  ADD KEY `leads_priority_index` (`priority`),
  ADD KEY `leads_next_follow_up_at_index` (`next_follow_up_at`),
  ADD KEY `leads_converted_by_foreign` (`converted_by`),
  ADD KEY `leads_converted_at_index` (`converted_at`);

--
-- Indexes for table `lead_follow_ups`
--
ALTER TABLE `lead_follow_ups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_follow_ups_lead_id_foreign` (`lead_id`),
  ADD KEY `lead_follow_ups_user_id_foreign` (`user_id`),
  ADD KEY `lead_follow_ups_type_index` (`type`),
  ADD KEY `lead_follow_ups_followed_up_at_index` (`followed_up_at`),
  ADD KEY `lead_follow_ups_outcome_index` (`outcome`),
  ADD KEY `lead_follow_ups_next_follow_up_at_index` (`next_follow_up_at`);

--
-- Indexes for table `lead_priorities`
--
ALTER TABLE `lead_priorities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_priorities_slug_unique` (`slug`),
  ADD KEY `lead_priorities_is_active_sort_order_index` (`is_active`,`sort_order`);

--
-- Indexes for table `lead_statuses`
--
ALTER TABLE `lead_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_statuses_slug_unique` (`slug`),
  ADD UNIQUE KEY `lead_statuses_system_key_unique` (`system_key`),
  ADD KEY `lead_statuses_is_active_sort_order_index` (`is_active`,`sort_order`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`),
  ADD KEY `permissions_group_index` (`group`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_role_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `permission_role_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `projects_project_code_unique` (`project_code`),
  ADD KEY `projects_created_by_foreign` (`created_by`),
  ADD KEY `projects_client_id_status_index` (`client_id`,`status`),
  ADD KEY `projects_project_manager_id_status_index` (`project_manager_id`,`status`),
  ADD KEY `projects_priority_index` (`priority`),
  ADD KEY `projects_status_index` (`status`),
  ADD KEY `projects_due_date_index` (`due_date`);

--
-- Indexes for table `project_activities`
--
ALTER TABLE `project_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_activities_project_id_foreign` (`project_id`),
  ADD KEY `project_activities_user_id_foreign` (`user_id`),
  ADD KEY `project_activities_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  ADD KEY `project_activities_action_index` (`action`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_members_project_id_user_id_unique` (`project_id`,`user_id`),
  ADD KEY `project_members_user_id_foreign` (`user_id`),
  ADD KEY `project_members_added_by_foreign` (`added_by`);

--
-- Indexes for table `project_services`
--
ALTER TABLE `project_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_services_assigned_to_foreign` (`assigned_to`),
  ADD KEY `project_services_created_by_foreign` (`created_by`),
  ADD KEY `project_services_project_id_status_index` (`project_id`,`status`),
  ADD KEY `project_services_priority_index` (`priority`),
  ADD KEY `project_services_status_index` (`status`),
  ADD KEY `project_services_due_date_index` (`due_date`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_setting_key_unique` (`setting_key`),
  ADD KEY `settings_group_name_setting_key_index` (`group_name`,`setting_key`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_project_service_id_foreign` (`project_service_id`),
  ADD KEY `tasks_parent_task_id_foreign` (`parent_task_id`),
  ADD KEY `tasks_created_by_foreign` (`created_by`),
  ADD KEY `tasks_reviewer_id_foreign` (`reviewer_id`),
  ADD KEY `tasks_assigned_to_status_due_at_index` (`assigned_to`,`status`,`due_at`),
  ADD KEY `tasks_project_id_status_index` (`project_id`,`status`),
  ADD KEY `tasks_priority_index` (`priority`),
  ADD KEY `tasks_status_index` (`status`),
  ADD KEY `tasks_due_at_index` (`due_at`);

--
-- Indexes for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_attachments_task_id_foreign` (`task_id`),
  ADD KEY `task_attachments_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_comments_task_id_foreign` (`task_id`),
  ADD KEY `task_comments_user_id_foreign` (`user_id`);

--
-- Indexes for table `task_dependencies`
--
ALTER TABLE `task_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_dependencies_task_id_depends_on_task_id_unique` (`task_id`,`depends_on_task_id`),
  ADD KEY `task_dependencies_created_by_foreign` (`created_by`),
  ADD KEY `task_dependencies_depends_on_task_id_task_id_index` (`depends_on_task_id`,`task_id`);

--
-- Indexes for table `task_priorities`
--
ALTER TABLE `task_priorities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_priorities_slug_unique` (`slug`),
  ADD KEY `task_priorities_is_active_sort_order_index` (`is_active`,`sort_order`);

--
-- Indexes for table `task_statuses`
--
ALTER TABLE `task_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_statuses_slug_unique` (`slug`),
  ADD UNIQUE KEY `task_statuses_system_key_unique` (`system_key`),
  ADD KEY `task_statuses_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `task_statuses_is_closed_is_manual_selectable_index` (`is_closed`,`is_manual_selectable`);

--
-- Indexes for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `time_entries_active_key_unique` (`active_key`),
  ADD KEY `time_entries_project_service_id_foreign` (`project_service_id`),
  ADD KEY `time_entries_created_by_foreign` (`created_by`),
  ADD KEY `time_entries_stopped_by_foreign` (`stopped_by`),
  ADD KEY `time_entries_user_id_status_started_at_index` (`user_id`,`status`,`started_at`),
  ADD KEY `time_entries_project_id_started_at_index` (`project_id`,`started_at`),
  ADD KEY `time_entries_task_id_started_at_index` (`task_id`,`started_at`),
  ADD KEY `time_entries_role_id_started_at_index` (`role_id`,`started_at`),
  ADD KEY `time_entries_status_index` (`status`),
  ADD KEY `time_entries_started_at_index` (`started_at`),
  ADD KEY `time_entries_stopped_at_index` (`stopped_at`);

--
-- Indexes for table `time_entry_breaks`
--
ALTER TABLE `time_entry_breaks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `time_entry_breaks_time_entry_id_resumed_at_index` (`time_entry_id`,`resumed_at`),
  ADD KEY `time_entry_breaks_paused_at_index` (`paused_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`),
  ADD KEY `users_is_active_index` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `lead_follow_ups`
--
ALTER TABLE `lead_follow_ups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `lead_priorities`
--
ALTER TABLE `lead_priorities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `lead_statuses`
--
ALTER TABLE `lead_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `permission_role`
--
ALTER TABLE `permission_role`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=271;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_activities`
--
ALTER TABLE `project_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `project_members`
--
ALTER TABLE `project_members`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `project_services`
--
ALTER TABLE `project_services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `task_dependencies`
--
ALTER TABLE `task_dependencies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `task_priorities`
--
ALTER TABLE `task_priorities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `task_statuses`
--
ALTER TABLE `task_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `time_entries`
--
ALTER TABLE `time_entries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `time_entry_breaks`
--
ALTER TABLE `time_entry_breaks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clients_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_converted_by_foreign` FOREIGN KEY (`converted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_follow_ups`
--
ALTER TABLE `lead_follow_ups`
  ADD CONSTRAINT `lead_follow_ups_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_follow_ups_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_project_manager_id_foreign` FOREIGN KEY (`project_manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_activities`
--
ALTER TABLE `project_activities`
  ADD CONSTRAINT `project_activities_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_members_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_services`
--
ALTER TABLE `project_services`
  ADD CONSTRAINT `project_services_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_services_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_services_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_parent_task_id_foreign` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_project_service_id_foreign` FOREIGN KEY (`project_service_id`) REFERENCES `project_services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `task_attachments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `task_dependencies`
--
ALTER TABLE `task_dependencies`
  ADD CONSTRAINT `task_dependencies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `task_dependencies_depends_on_task_id_foreign` FOREIGN KEY (`depends_on_task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_dependencies_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD CONSTRAINT `time_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `time_entries_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `time_entries_project_service_id_foreign` FOREIGN KEY (`project_service_id`) REFERENCES `project_services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `time_entries_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `time_entries_stopped_by_foreign` FOREIGN KEY (`stopped_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `time_entries_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `time_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `time_entry_breaks`
--
ALTER TABLE `time_entry_breaks`
  ADD CONSTRAINT `time_entry_breaks_time_entry_id_foreign` FOREIGN KEY (`time_entry_id`) REFERENCES `time_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
