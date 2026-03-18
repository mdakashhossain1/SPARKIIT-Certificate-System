-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 12:09 PM
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
-- Database: `new`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `status`, `created_at`) VALUES
(116, 'Full Stack Web Development with React', 'active', '2026-03-16 16:55:53'),
(117, 'Advanced Java Programming for Software', 'active', '2026-03-16 16:55:53'),
(118, 'Introduction to Mobile App Development', 'active', '2026-03-16 16:55:53'),
(119, 'Modern Front-End Development with js', 'active', '2026-03-16 16:55:53'),
(120, 'Database Design and Management with SQL', 'active', '2026-03-16 16:55:53'),
(121, 'Building Scalable Microservices', 'active', '2026-03-16 16:55:53'),
(122, 'Cybersecurity Fundamentals for devs', 'active', '2026-03-16 16:55:53'),
(123, 'Cloud Computing with AWS: From Beginner', 'active', '2026-03-16 16:55:53'),
(124, 'Game Development with Unity and C#', 'active', '2026-03-16 16:55:53'),
(125, 'Data Analysis with R for Business Intelligence', 'active', '2026-03-16 16:55:53'),
(126, 'iOS App Development with Objective-C', 'active', '2026-03-16 16:55:53'),
(127, 'Advanced Topics in C++ Programming', 'active', '2026-03-16 16:55:53'),
(128, 'Blockchain Fundamentals: Building Decentralized', 'active', '2026-03-16 16:55:53'),
(129, 'Artificial Intelligence and Machine', 'active', '2026-03-16 16:55:53'),
(130, 'DevOps Automation with Jenkins and Ansible', 'active', '2026-03-16 16:55:53'),
(131, 'Introduction to Natural Language Processing', 'active', '2026-03-16 16:55:53'),
(132, 'Building Reactive Applications with RxJava', 'active', '2026-03-16 16:55:53'),
(133, 'UI/UX Design Principles for Developers', 'active', '2026-03-16 16:55:53'),
(134, 'Introduction to Quantum Computing', 'active', '2026-03-16 16:55:53'),
(135, 'Business Strategies for Digital Marketers', 'active', '2026-03-16 16:55:53'),
(136, 'Advanced Data Analysis for Business', 'active', '2026-03-16 16:55:53'),
(137, 'IT Security Essentials: Protecting Your', 'active', '2026-03-16 16:55:53'),
(138, 'Mastering Python for Data Science', 'active', '2026-03-16 16:55:53'),
(139, 'Personal Branding: Building Your Online', 'active', '2026-03-16 16:55:53'),
(140, 'Effective Communication Skills for Leaders', 'active', '2026-03-16 16:55:53'),
(141, 'User Interface Design Fundamentals', 'active', '2026-03-16 16:55:53'),
(142, 'Creating Engaging Content: Strategies', 'active', '2026-03-16 16:55:53'),
(143, 'Introduction to Music Production', 'active', '2026-03-16 16:55:53'),
(144, 'Financial Planning for Young Professionals', 'active', '2026-03-16 16:55:53'),
(145, 'Entrepreneurship: From Idea to Launch', 'active', '2026-03-16 16:55:53'),
(146, 'Cybersecurity Fundamentals for IT Professionals', 'active', '2026-03-16 16:55:53'),
(147, 'Self-Confidence Mastery: Overcoming', 'active', '2026-03-16 16:55:53'),
(148, 'Graphic Design Principles for Beginners', 'active', '2026-03-16 16:55:53'),
(149, 'Digital Marketing Fundamentals: SEO and SEM', 'active', '2026-03-16 16:55:53'),
(150, 'Introduction to Guitar Playing Techniques', 'active', '2026-03-16 16:55:53'),
(151, 'Investment Strategies for a Volatile Market', 'active', '2026-03-16 16:55:53'),
(152, 'Leadership Development: Inspiring', 'active', '2026-03-16 16:55:53'),
(153, 'Web Development Bootcamp: From Zero', 'active', '2026-03-16 16:55:53'),
(154, 'Mindfulness Meditation for Stress Reduction', 'active', '2026-03-16 16:55:53'),
(155, 'Strategic Brand Management in Digital', 'active', '2026-03-16 16:55:53'),
(156, 'Advanced Excel Techniques for Financial', 'active', '2026-03-16 16:55:53'),
(157, 'Effective Time Management for Busy Professionals', 'active', '2026-03-16 16:55:53'),
(158, 'UX/UI Design: Creating Intuitive User Experiences', 'active', '2026-03-16 16:55:53'),
(159, 'Content Marketing Strategy: Engaging Your Audience', 'active', '2026-03-16 16:55:53'),
(160, 'Music Theory Essentials: Understanding Harmony', 'active', '2026-03-16 16:55:53'),
(161, 'Financial Literacy: Managing Your Finances', 'active', '2026-03-16 16:55:53'),
(162, 'Project Management Fundamentals: Agile', 'active', '2026-03-16 16:55:53'),
(163, 'Entrepreneurial Mindset: Cultivating Innovation', 'active', '2026-03-16 16:55:53'),
(164, 'Cybersecurity for Small Businesses: Best Practices', 'active', '2026-03-16 16:55:53'),
(165, 'Creative Writing Workshop: Finding Your Voice', 'active', '2026-03-16 16:55:53'),
(166, 'Introduction to Digital Illustration', 'active', '2026-03-16 16:55:53'),
(167, 'Social Media Marketing: Building Brand', 'active', '2026-03-16 16:55:53'),
(168, 'Music Production Masterclass: Mixing', 'active', '2026-03-16 16:55:53'),
(169, 'Investing in Cryptocurrencies: Opportunities', 'active', '2026-03-16 16:55:53'),
(170, 'Conflict Resolution Skills for Workplace', 'active', '2026-03-16 16:55:53'),
(171, 'Design Thinking: Solving Complex Problems', 'active', '2026-03-16 16:55:53'),
(172, 'Digital Advertising Fundamentals: PPC', 'active', '2026-03-16 16:55:53'),
(173, 'Piano for Beginners: Learning Basic Techniques', 'active', '2026-03-16 16:55:53'),
(174, 'Retirement Planning: Securing Your Future', 'active', '2026-03-16 16:55:53'),
(175, 'Effective Public Speaking: Engage and Persuade', 'active', '2026-03-16 16:55:53'),
(176, 'Product Essentials: From Idea to Market', 'active', '2026-03-16 16:55:53'),
(177, 'Mindful Leadership: Leading with Compassion', 'active', '2026-03-16 16:55:53'),
(178, 'Introduction to User Experience Research', 'active', '2026-03-16 16:55:53'),
(179, 'E-commerce Strategies for Small Businesses', 'active', '2026-03-16 16:55:53'),
(180, 'Songwriting Basics: Crafting Melodies', 'active', '2026-03-16 16:55:53'),
(181, 'Introduction to Financial Markets', 'active', '2026-03-16 16:55:53'),
(182, 'Remote Work Productivity: Tips and Tools', 'active', '2026-03-16 16:55:53'),
(183, 'Artificial Intelligence in Business', 'active', '2026-03-16 16:55:53'),
(184, 'Sales', 'active', '2026-03-16 16:55:53'),
(185, 'Web Development Front-End', 'active', '2026-03-16 16:55:53'),
(186, 'ML', 'active', '2026-03-16 16:55:53'),
(187, 'Data Analytics', 'active', '2026-03-16 16:55:53'),
(188, 'Android Development', 'active', '2026-03-16 16:55:53'),
(189, 'Artificial Intelligence', 'active', '2026-03-16 16:55:53'),
(190, 'Machine Learning with python', 'active', '2026-03-16 16:55:53'),
(191, 'Thermodynamics with MATLAB', 'active', '2026-03-16 16:55:53'),
(192, 'Hybrid Electric Vehicle', 'active', '2026-03-16 16:55:53'),
(193, 'Very Large Scale Integration', 'active', '2026-03-16 16:55:53'),
(194, 'Data Science', 'active', '2026-03-16 16:55:53'),
(195, 'AutoCAD Civil', 'active', '2026-03-16 16:55:53'),
(196, 'AutoCAD CATIA', 'active', '2026-03-16 16:55:53'),
(197, 'Android App Development', 'active', '2026-03-16 16:55:53'),
(198, 'Azure Cloud Computing', 'active', '2026-03-16 16:55:53'),
(199, 'Cyber Security & Ethical Hacking', 'active', '2026-03-16 16:55:53'),
(200, 'Full-Stack Web Development', 'active', '2026-03-16 16:55:53'),
(201, 'Internet of Things', 'active', '2026-03-16 16:55:53'),
(202, 'Robotics', 'active', '2026-03-16 16:55:53'),
(203, 'Startup & Entrepreneurship', 'active', '2026-03-16 16:55:53'),
(204, 'Finance', 'active', '2026-03-16 16:55:53'),
(205, 'Human Resource Management', 'active', '2026-03-16 16:55:53'),
(206, 'Programming in Python', 'active', '2026-03-16 16:55:53'),
(207, 'Embedded Systems', 'active', '2026-03-16 16:55:53'),
(208, 'Programming in JAVA', 'active', '2026-03-16 16:55:53'),
(209, 'Digital Marketing', 'active', '2026-03-16 16:55:53'),
(210, 'Digital Marketing Fundamentals', 'active', '2026-03-16 16:55:53'),
(211, 'Artificial Intelligence [ AUGUST ]', 'active', '2026-03-16 16:55:53'),
(212, 'Product and Project Management', 'active', '2026-03-16 16:55:53'),
(213, 'DSA With Python', 'active', '2026-03-16 16:55:53'),
(214, 'Graphic Designing', 'active', '2026-03-16 16:55:53'),
(215, 'Business Analysis', 'active', '2026-03-16 16:55:53'),
(216, 'Operations & Supply Chain Management', 'active', '2026-03-16 16:55:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
