-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2026 at 01:34 PM
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
-- Database: `hospital`
--

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `bed_id` int(10) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `admission_date` datetime NOT NULL,
  `discharge_date` datetime DEFAULT NULL,
  `admission_reason` text DEFAULT NULL,
  `discharge_notes` text DEFAULT NULL,
  `status` enum('admitted','discharged','transferred') NOT NULL DEFAULT 'admitted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`id`, `patient_id`, `bed_id`, `doctor_id`, `admission_date`, `discharge_date`, `admission_reason`, `discharge_notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 1, '2026-09-04 09:00:00', NULL, 'Acute NSTEMI post-coronary care observation and continuous cardiac telemetry monitoring.', NULL, 'admitted', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(2, 5, 9, 4, '2026-09-06 14:30:00', NULL, 'Pre-operative preparation and pain management for right knee arthroplasty.', NULL, 'admitted', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, 6, 11, 1, '2026-09-07 15:28:00', NULL, NULL, NULL, 'admitted', '2026-09-07 09:59:09', '2026-09-07 09:59:09');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `time_slot` varchar(20) NOT NULL,
  `status` enum('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `reason` text DEFAULT NULL,
  `doctor_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `department_id`, `appointment_date`, `time_slot`, `status`, `reason`, `doctor_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-09-07', '09:30 AM', 'scheduled', 'Routine cardiology follow-up and blood pressure assessment.', 'Patient requested review of ACE inhibitor dosage.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(2, 2, 2, 2, '2026-09-07', '10:30 AM', 'scheduled', 'Severe episodic throbbing headache with visual aura.', 'Evaluate for prophylactic topiramate vs triptan regimen.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, 4, 3, 3, '2026-09-07', '11:00 AM', 'scheduled', 'Nocturnal cough and wheezing post viral URI.', 'Peak flow measurement and inhaler technique review.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(4, 5, 4, 4, '2026-09-07', '02:00 PM', 'scheduled', 'Right knee joint pain during stair climbing.', 'Check standing AP/lateral knee X-rays and range of motion.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(5, 6, 1, 1, '2026-09-07', '03:30 PM', 'scheduled', 'Intermittent palpitations on exertion.', 'Holter monitor requisition evaluation.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(6, 1, 1, 1, '2026-08-24', '11:30 AM', 'completed', 'Substernal chest tightness with moderate exertion.', 'ECG performed. Advised Lipid Profile panel. Prescribed Atorvastatin.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(7, 2, 2, 2, '2026-08-28', '02:30 PM', 'completed', 'Follow-up for chronic tension-type headache.', 'Neurological exam normal. Stress reduction techniques advised.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(8, 3, 1, 1, '2026-09-04', '08:00 AM', 'completed', 'Acute chest pain radiating to left shoulder - Emergency Admission.', 'Admitted to ICU Room 101 Bed B1 for continuous telemetry.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(9, 7, 1, 1, '2026-08-31', '09:00 AM', 'completed', 'Atrial Fibrillation rate control assessment.', 'Target heart rate achieved. Stable INR / Anticoagulation.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(10, 8, 3, 3, '2026-09-02', '10:00 AM', 'completed', 'General health clearance for sports tournament.', 'Normal cardiovascular and pulmonary exam. Cleared for athletics.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(11, 9, 4, 4, '2026-09-03', '03:00 PM', 'completed', 'Chronic lower back pain radiating down left posterior thigh.', 'SLR test positive at 45 degrees left. Ordered Lumbar Spine MRI.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(12, 1, 1, 1, '2026-09-14', '10:00 AM', 'scheduled', 'Post-treatment lipid profile check & medication review.', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(13, 3, 1, 1, '2026-09-12', '11:00 AM', 'scheduled', 'Post-discharge recovery check & cardiac rehab orientation.', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(14, 5, 4, 4, '2026-09-10', '02:30 PM', 'scheduled', 'Intra-articular hyaluronic acid injection follow-up.', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `ip_address`, `details`, `created_at`) VALUES
(1, 1, 'LOGIN', 'users', 1, '127.0.0.1', 'Superadmin master authentication from local gateway', '2026-09-07 09:01:10'),
(2, 3, 'CREATE', 'medical_records', 1, '127.0.0.1', 'Created EMR consultation record for Patient PAT-2026-0001', '2026-09-07 09:16:10'),
(3, 1, 'ADMIT', 'admissions', 1, '127.0.0.1', 'Admitted Patient Robert Brown to ICU-101 Bed B1', '2026-09-07 09:26:10'),
(4, 9, 'DISPENSE', 'prescriptions', 1, '127.0.0.1', 'Dispensed medications for Prescription #1 (Atorvastatin, Amlodipine)', '2026-09-07 09:31:10'),
(5, 11, 'PAYMENT', 'payments', 1, '127.0.0.1', 'Collected $236.25 payment via Visa for Invoice INV-2026-0001', '2026-09-07 09:36:10'),
(6, 11, 'LOGIN', 'users', 11, '127.0.0.1', 'Demo 1-Click login as cashier', '2026-09-07 09:57:27'),
(7, 11, 'ADMIT', 'admissions', 3, '127.0.0.1', 'Admitted Patient ID 6 to Bed #B1 (Room PRV-203)', '2026-09-07 09:59:09'),
(8, 11, 'LOGOUT', 'users', 11, '127.0.0.1', 'User logged out', '2026-09-07 10:00:08'),
(9, 10, 'LOGIN', 'users', 10, '127.0.0.1', 'Demo 1-Click login as labtech', '2026-09-07 10:00:12'),
(10, 10, 'LOGOUT', 'users', 10, '127.0.0.1', 'User logged out', '2026-09-07 10:00:40'),
(11, 7, 'LOGIN', 'users', 7, '127.0.0.1', 'Demo 1-Click login as receptionist', '2026-09-07 10:00:46'),
(12, 7, 'LOGOUT', 'users', 7, '127.0.0.1', 'User logged out', '2026-09-07 10:01:15'),
(13, 2, 'LOGIN', 'users', 2, '127.0.0.1', 'Demo 1-Click login as admin', '2026-09-07 10:01:20'),
(14, 2, 'UPDATE', 'system_settings', NULL, '127.0.0.1', 'Updated hospital system configuration settings', '2026-09-07 10:01:51'),
(15, 2, 'LOGOUT', 'users', 2, '127.0.0.1', 'User logged out', '2026-09-07 10:02:08'),
(16, 1, 'LOGIN', 'users', 1, '127.0.0.1', 'Demo 1-Click login as superadmin', '2026-09-07 10:02:11');

-- --------------------------------------------------------

--
-- Table structure for table `beds`
--

CREATE TABLE `beds` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `bed_number` varchar(20) NOT NULL,
  `status` enum('available','occupied','cleaning','maintenance') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `beds`
--

INSERT INTO `beds` (`id`, `room_id`, `bed_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'B1', 'occupied', '2026-09-07 09:45:56', '2026-09-07 09:46:09'),
(2, 1, 'B2', 'cleaning', '2026-09-07 09:45:56', '2026-09-07 09:46:09'),
(3, 1, 'B3', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(4, 1, 'B4', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(5, 2, 'B1', 'maintenance', '2026-09-07 09:45:56', '2026-09-07 09:46:09'),
(6, 2, 'B2', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(7, 2, 'B3', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(8, 2, 'B4', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(9, 3, 'B1', 'occupied', '2026-09-07 09:45:56', '2026-09-07 09:46:09'),
(10, 4, 'B1', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(11, 5, 'B1', 'occupied', '2026-09-07 09:45:56', '2026-09-07 09:59:09'),
(12, 6, 'B1', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(13, 6, 'B2', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(14, 7, 'B1', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(15, 7, 'B2', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(16, 8, 'B1', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(17, 8, 'B2', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(18, 8, 'B3', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(19, 8, 'B4', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(20, 8, 'B5', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(21, 8, 'B6', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(22, 9, 'B1', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(23, 9, 'B2', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(24, 9, 'B3', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(25, 9, 'B4', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(26, 9, 'B5', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(27, 9, 'B6', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(28, 10, 'B1', 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'hospital',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `description`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'Cardiology', 'CARD', 'Comprehensive cardiac care, catheterization, and heart surgery', 'heart-pulse', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(2, 'Neurology', 'NEUR', 'Advanced neurosurgery, stroke unit, and neurological rehabilitation', 'brain', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(3, 'Pediatrics', 'PED', 'Dedicated child healthcare, neonatal ICU, and adolescent medicine', 'baby', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(4, 'Orthopedics', 'ORTH', 'Joint replacement, trauma, sports medicine, and spine care', 'bone', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(5, 'Oncology', 'ONC', 'Medical and surgical oncology, chemotherapy, and radiation therapy', 'ribbon', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(6, 'General Surgery', 'GSURG', 'Minimally invasive laparoscopic, general, and emergency surgery', 'scalpel', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(7, 'Radiology', 'RAD', 'Advanced imaging: MRI, 128-slice CT, Ultrasound, Digital X-Ray', 'x-ray', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(8, 'Emergency Medicine', 'EMER', '24/7 Level 1 Trauma center and critical emergency triage', 'truck-medical', '2026-09-07 09:45:56', '2026-09-07 09:45:56');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `specialization` varchar(100) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `consultation_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `phone` varchar(20) NOT NULL,
  `bio` text DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `specialization`, `license_number`, `consultation_fee`, `phone`, `bio`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'Senior Interventional Cardiologist', 'MED-CARD-98421', 120.00, '+1 (555) 234-5678', 'Board-certified cardiologist with 15+ years experience in complex coronary interventions and echocardiography.', 1, '2026-09-07 09:45:58', '2026-09-07 09:45:58'),
(2, 4, 2, 'Consultant Neurologist & Neurophysiologist', 'MED-NEUR-77219', 150.00, '+1 (555) 345-6789', 'Specializing in acute ischemic stroke, epilepsy management, and neuromuscular disorders.', 1, '2026-09-07 09:46:00', '2026-09-07 09:46:00'),
(3, 5, 3, 'Pediatrician & Neonatal Specialist', 'MED-PED-55102', 95.00, '+1 (555) 456-7890', 'Passionate child care specialist with focus on developmental pediatrics and newborn intensive care.', 1, '2026-09-07 09:46:01', '2026-09-07 09:46:01'),
(4, 6, 4, 'Orthopedic & Joint Reconstruction Surgeon', 'MED-ORTH-33891', 135.00, '+1 (555) 567-8901', 'Expert in arthroscopic joint reconstruction, total hip/knee arthroplasty, and trauma fixation.', 1, '2026-09-07 09:46:02', '2026-09-07 09:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_availabilities`
--

CREATE TABLE `doctor_availabilities` (
  `id` int(10) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor_availabilities`
--

INSERT INTO `doctor_availabilities` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 'Monday', '09:00:00', '17:00:00', 1, '2026-09-07 09:45:58', '2026-09-07 09:45:58'),
(2, 1, 'Tuesday', '09:00:00', '17:00:00', 1, '2026-09-07 09:45:58', '2026-09-07 09:45:58'),
(3, 1, 'Wednesday', '09:00:00', '17:00:00', 1, '2026-09-07 09:45:58', '2026-09-07 09:45:58'),
(4, 1, 'Thursday', '09:00:00', '17:00:00', 1, '2026-09-07 09:45:58', '2026-09-07 09:45:58'),
(5, 1, 'Friday', '09:00:00', '17:00:00', 1, '2026-09-07 09:45:58', '2026-09-07 09:45:58'),
(6, 2, 'Monday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:00', '2026-09-07 09:46:00'),
(7, 2, 'Tuesday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:00', '2026-09-07 09:46:00'),
(8, 2, 'Wednesday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:00', '2026-09-07 09:46:00'),
(9, 2, 'Thursday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:00', '2026-09-07 09:46:00'),
(10, 2, 'Friday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:00', '2026-09-07 09:46:00'),
(11, 3, 'Monday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:01', '2026-09-07 09:46:01'),
(12, 3, 'Tuesday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:01', '2026-09-07 09:46:01'),
(13, 3, 'Wednesday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:01', '2026-09-07 09:46:01'),
(14, 3, 'Thursday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:01', '2026-09-07 09:46:01'),
(15, 3, 'Friday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:01', '2026-09-07 09:46:01'),
(16, 4, 'Monday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:02', '2026-09-07 09:46:02'),
(17, 4, 'Tuesday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:02', '2026-09-07 09:46:02'),
(18, 4, 'Wednesday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:02', '2026-09-07 09:46:02'),
(19, 4, 'Thursday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:02', '2026-09-07 09:46:02'),
(20, 4, 'Friday', '09:00:00', '17:00:00', 1, '2026-09-07 09:46:02', '2026-09-07 09:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `alt_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `emergency_contacts`
--

INSERT INTO `emergency_contacts` (`id`, `patient_id`, `contact_name`, `relationship`, `phone`, `alt_phone`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mary Doe', 'Spouse', '+1 (555) 789-0124', '+1 (555) 789-0125', '2026-09-07 09:46:08', '2026-09-07 09:46:08'),
(2, 2, 'Chris Watson', 'Brother', '+1 (555) 890-1235', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, 3, 'Laura Brown', 'Daughter', '+1 (555) 901-2346', '+1 (555) 901-2347', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(4, 4, 'Marcus Davis', 'Father', '+1 (555) 345-6712', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(5, 5, 'Sarah Taylor', 'Spouse', '+1 (555) 456-7823', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(6, 6, 'Carlos Martinez', 'Brother', '+1 (555) 567-8934', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(7, 7, 'Judith Clark', 'Spouse', '+1 (555) 678-9045', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(8, 8, 'Grace Lee', 'Mother', '+1 (555) 789-0156', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(9, 9, 'Elena Rodriguez', 'Spouse', '+1 (555) 890-1267', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(10, 10, 'Hanna Kim', 'Mother', '+1 (555) 901-2378', NULL, '2026-09-07 09:46:09', '2026-09-07 09:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(30) NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `admission_id` bigint(20) UNSIGNED DEFAULT NULL,
  `appointment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('unpaid','partially_paid','paid','cancelled') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `patient_id`, `admission_id`, `appointment_id`, `invoice_date`, `due_date`, `total_amount`, `discount_amount`, `tax_amount`, `net_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'INV-2026-0001', 1, NULL, 6, '2026-08-24', '2026-08-31', 225.00, 0.00, 11.25, 236.25, 'paid', 'Cardiology Consultation + Lipid Profile + 12-Lead ECG Package.', '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(2, 'INV-2026-0002', 3, 1, NULL, '2026-09-06', '2026-09-14', 1575.00, 100.00, 73.75, 1548.75, 'partially_paid', 'Inpatient ICU 101 telemetry care, Troponin I assay, Digital Chest X-Ray.', '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(3, 'INV-2026-0003', 2, NULL, 7, '2026-08-28', '2026-09-11', 150.00, 0.00, 7.50, 157.50, 'unpaid', 'Neurology Specialist Consultation - Dr. James Wilson.', '2026-09-07 09:46:10', '2026-09-07 09:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_description`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 'Doctor Consultation - Dr. Sarah Jenkins (Cardiology)', 1, 120.00, 120.00, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(2, 1, 'Diagnostic Lab: Lipid Profile Panel', 1, 45.00, 45.00, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(3, 1, 'Diagnostic: 12-Lead Electrocardiogram (ECG)', 1, 60.00, 60.00, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(4, 2, 'ICU Room 101 - Bed B1 [3 Days @ $450.00/day]', 3, 450.00, 1350.00, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(5, 2, 'Diagnostic Lab: High-Sensitivity Troponin I', 1, 75.00, 75.00, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(6, 2, 'Diagnostic Radiology: Digital Chest X-Ray (PA)', 1, 80.00, 80.00, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(7, 2, 'Attending Physician Specialist Rounding', 1, 70.00, 70.00, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(8, 3, 'Neurology Specialist Consultation - Dr. James Wilson', 1, 150.00, 150.00, '2026-09-07 09:46:10', '2026-09-07 09:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_reports`
--

CREATE TABLE `lab_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `medical_record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `lab_test_id` int(10) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `technician_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('requested','in_progress','completed','cancelled') NOT NULL DEFAULT 'requested',
  `result_summary` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `report_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lab_reports`
--

INSERT INTO `lab_reports` (`id`, `medical_record_id`, `patient_id`, `lab_test_id`, `doctor_id`, `technician_id`, `status`, `result_summary`, `file_path`, `report_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, 1, 10, 'completed', 'Total Cholesterol: 215 mg/dL (Borderline High), HDL: 48 mg/dL (Optimal >40), LDL: 135 mg/dL (Elevated, Target <100), Triglycerides: 160 mg/dL (Borderline High).', NULL, '2026-08-26 15:00:00', '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(2, 1, 1, 4, 1, 10, 'completed', 'Normal sinus rhythm, HR 72 bpm. PR interval 160ms, QRS 88ms, QTc 415ms. Normal axis (+60°). No ST-segment elevation, pathological Q waves, or T-wave inversions.', NULL, '2026-08-25 10:30:00', '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(3, 3, 3, 9, 1, 10, 'completed', 'High-Sensitivity Troponin I: 1450 ng/L (Ref: <14 ng/L). Significant myocardial necrosis marker elevation consistent with acute NSTEMI.', NULL, '2026-09-04 09:15:00', '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(4, 3, 3, 5, 1, 10, 'completed', 'PA Chest View: Normal cardiothoracic ratio (<0.50). Lung fields clear with no focal consolidation, pneumothorax, or pleural effusion. Mediastinal contours unremarkable.', NULL, '2026-09-04 10:00:00', '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(5, 5, 9, 7, 4, 10, 'in_progress', 'MRI Lumbar Spine scan acquisition completed. Senior Radiologist review in progress.', NULL, NULL, '2026-09-07 09:46:10', '2026-09-07 09:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `lab_tests`
--

CREATE TABLE `lab_tests` (
  `id` int(10) UNSIGNED NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lab_tests`
--

INSERT INTO `lab_tests` (`id`, `test_name`, `code`, `description`, `cost`, `created_at`, `updated_at`) VALUES
(1, 'Complete Blood Count (CBC)', 'LAB-CBC', 'Evaluates overall health and detects wide range of disorders including anemia, infection and leukemia.', 35.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(2, 'Comprehensive Metabolic Panel (CMP)', 'LAB-CMP', 'Measures 14 substances in blood for kidney/liver status, electrolyte and fluid balance.', 55.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, 'Lipid Profile Panel', 'LAB-LIPID', 'Measures Total Cholesterol, HDL, LDL, and Triglycerides to assess cardiovascular risk.', 45.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(4, '12-Lead Electrocardiogram (ECG)', 'RAD-ECG', 'Records electrical signals from the heart to detect arrhythmias, ischemia, and infarction.', 60.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(5, 'Digital Chest X-Ray (PA View)', 'RAD-CXR', 'High-resolution radiograph of chest cavity, cardiac silhouette, and lung fields.', 80.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(6, 'MRI Brain with Contrast', 'RAD-MRI-BR', 'Magnetic Resonance Imaging of cranial structures for neurological diagnostics.', 450.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(7, 'MRI Lumbar Spine', 'RAD-MRI-LSP', 'Detailed imaging of lumbar intervertebral discs, spinal canal, and nerve roots.', 420.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(8, 'HbA1c Glycated Hemoglobin', 'LAB-HBA1C', 'Assesses 3-month average plasma glucose concentration for diabetes management.', 40.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(9, 'High-Sensitivity Cardiac Troponin I', 'LAB-TROP-I', 'Gold-standard biomarker for myocardial injury and acute coronary syndromes.', 75.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(10, 'Urinalysis with Microscopic Examination', 'LAB-UA', 'Screening for urinary tract infections, kidney disease, and diabetes mellitus.', 25.00, '2026-09-07 09:46:09', '2026-09-07 09:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`id`, `user_id`, `department_id`, `title`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Chief Operations Officer', '2026-09-07 09:45:58', '2026-09-07 09:45:58');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `appointment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `visit_date` datetime NOT NULL,
  `diagnosis` text NOT NULL,
  `symptoms` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`id`, `patient_id`, `doctor_id`, `appointment_id`, `visit_date`, `diagnosis`, `symptoms`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 6, '2026-08-24 11:30:00', 'Essential Hypertension with Stable Angina Pectoris (CCS Class I)', 'Patient reports substernal tightness on stair climbing lasting < 3 mins, relieved by rest. No radiation, no diaphoresis, no dyspnea at rest.', 'SOAP Assessment: Cardiovascular exam reveals S1, S2 normal, no S3/S4, no murmurs. JVP normal. Lungs clear to auscultation bilaterally. Plan: Start Atorvastatin 20mg QHS, Amlodipine 5mg QAM. Requisitioned 12-lead ECG and Lipid Profile Panel.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(2, 2, 2, 7, '2026-08-28 14:30:00', 'Migraine with Visual Aura (ICD-10 G43.109) & Tension Headache', 'Unilateral throbbing frontotemporal pain preceded by scintillating scotoma. Accompanied by photophobia and mild nausea.', 'SOAP Assessment: Cranial nerves II-XII intact. Fundoscopy shows no papilledema. Motor strength 5/5 all extremities. Sensation intact. Plan: Acute abortive Sumatriptan 50mg PRN. Prophylactic magnesium & riboflavin. Headache diary initiated.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, 3, 1, 8, '2026-09-04 08:30:00', 'Non-ST-Elevation Myocardial Infarction (NSTEMI) & T2DM', 'Acute onset crushing retrosternal chest pain radiating to left jaw, onset 2 hours prior to presentation with diaphoresis and nausea.', 'SOAP Assessment: High-sensitivity Troponin I elevated (1450 ng/L). ECG showed ST depressions in V4-V6. Direct admission to ICU Bed 101-B1. Started dual antiplatelet therapy (DAPT), IV heparin protocol, and high-intensity statin.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(4, 7, 1, 9, '2026-08-31 09:30:00', 'Paroxysmal Atrial Fibrillation on Anticoagulation (CHA2DS2-VASc = 3)', 'Occasional fluttering sensation in chest, no syncopal episodes, good exercise tolerance.', 'SOAP Assessment: Heart sounds irregularly irregular. Controlled ventricular response rate. Advised continuation of Eliquis 5mg BID and Metoprolol Succinate 50mg daily.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(5, 9, 4, 11, '2026-09-03 15:30:00', 'Lumbar Radiculopathy secondary to L4-L5 Disc Herniation', 'Sharp burning pain down left lateral thigh and calf with numbness over L5 dermatome.', 'SOAP Assessment: Straight Leg Raise positive at 45 degrees on left. EHL weakness 4/5. Patellar and Achilles reflexes 2+ symmetrical. Prescribed Pregabalin 75mg QHS and physical therapy protocol. Requisitioned MRI Spine.', '2026-09-07 09:46:09', '2026-09-07 09:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `medical_record_details`
--

CREATE TABLE `medical_record_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `medical_record_id` bigint(20) UNSIGNED NOT NULL,
  `vital_sign_name` varchar(50) NOT NULL,
  `vital_sign_value` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medical_record_details`
--

INSERT INTO `medical_record_details` (`id`, `medical_record_id`, `vital_sign_name`, `vital_sign_value`, `created_at`, `updated_at`) VALUES
(1, 1, 'Blood Pressure', '138/86 mmHg', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(2, 1, 'Heart Rate', '74 bpm', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, 1, 'SpO2', '98% on Room Air', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(4, 1, 'Temperature', '98.4 °F', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(5, 1, 'Respiratory Rate', '16 breaths/min', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(6, 1, 'Body Weight', '82.5 kg', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(7, 2, 'Blood Pressure', '118/76 mmHg', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(8, 2, 'Heart Rate', '68 bpm', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(9, 2, 'SpO2', '99% on Room Air', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(10, 2, 'Temperature', '98.6 °F', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(11, 2, 'Respiratory Rate', '14 breaths/min', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(12, 2, 'Body Weight', '58.0 kg', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(13, 3, 'Blood Pressure', '152/94 mmHg', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(14, 3, 'Heart Rate', '92 bpm', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(15, 3, 'SpO2', '95% on 2L Nasal Cannula', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(16, 3, 'Temperature', '99.1 °F', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(17, 3, 'Respiratory Rate', '20 breaths/min', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(18, 3, 'Body Weight', '89.0 kg', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(19, 4, 'Blood Pressure', '126/80 mmHg', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(20, 4, 'Heart Rate', '76 bpm (irregular)', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(21, 4, 'SpO2', '98% on Room Air', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(22, 4, 'Temperature', '98.2 °F', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(23, 4, 'Respiratory Rate', '16 breaths/min', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(24, 4, 'Body Weight', '77.0 kg', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(25, 5, 'Blood Pressure', '128/82 mmHg', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(26, 5, 'Heart Rate', '72 bpm', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(27, 5, 'SpO2', '99% on Room Air', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(28, 5, 'Temperature', '98.4 °F', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(29, 5, 'Respiratory Rate', '15 breaths/min', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(30, 5, 'Body Weight', '84.0 kg', '2026-09-07 09:46:09', '2026-09-07 09:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `generic_name` varchar(100) DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 10,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `name`, `generic_name`, `category`, `unit_price`, `stock_quantity`, `reorder_level`, `expiry_date`, `created_at`, `updated_at`) VALUES
(1, 'Augmentin 625mg', 'Amoxicillin + Clavulanic Acid', 'Antibiotic', 18.50, 150, 30, '2027-06-30', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(2, 'Lipitor 20mg', 'Atorvastatin Calcium', 'Cardiovascular', 24.00, 220, 40, '2028-01-15', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, 'Glucophage 500mg', 'Metformin Hydrochloride', 'Antidiabetic', 12.00, 300, 50, '2027-11-20', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(4, 'Norvasc 5mg', 'Amlodipine Besylate', 'Antihypertensive', 15.00, 180, 30, '2027-09-10', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(5, 'Tylenol Extra 500mg', 'Paracetamol / Acetaminophen', 'Analgesic', 8.00, 500, 50, '2028-05-01', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(6, 'Prilosec 20mg', 'Omeprazole Delayed-Release', 'Gastrointestinal', 16.50, 120, 25, '2027-08-18', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(7, 'Zithromax 500mg', 'Azithromycin Dihydrate', 'Antibiotic', 28.00, 8, 20, '2026-12-31', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(8, 'Ventolin HFA 100mcg', 'Salbutamol Inhalation Aerosol', 'Respiratory', 35.00, 45, 15, '2027-10-05', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(9, 'Rocephin 1g Vial', 'Ceftriaxone Sodium Injection', 'Antibiotic', 42.00, 60, 15, '2027-03-25', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(10, 'Advil 400mg', 'Ibuprofen Liqui-Gels', 'NSAID', 9.50, 250, 40, '2028-02-14', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(11, 'Eliquis 5mg', 'Apixaban', 'Cardiovascular', 65.00, 110, 25, '2028-04-10', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(12, 'Lyrica 75mg', 'Pregabalin', 'Neurology', 38.00, 90, 20, '2027-12-15', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(13, 'Imitrex 50mg', 'Sumatriptan Succinate', 'Neurology', 45.00, 70, 15, '2027-07-20', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(14, 'Synthroid 75mcg', 'Levothyroxine Sodium', 'Endocrinology', 14.00, 210, 35, '2028-06-30', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(15, 'Plavix 75mg', 'Clopidogrel Bisulfate', 'Cardiovascular', 29.50, 12, 30, '2027-05-15', '2026-09-07 09:46:09', '2026-09-07 09:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `message_body` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message_body`, `sent_at`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'Good morning Dr. Vance. Telemetry monitoring equipment in ICU-101 has been calibrated for Patient Robert Brown.', '2026-09-07 03:15:00', 1, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(2, 1, 3, 'Thank you Dr. Sarah. The clinical operations team is briefed on the telemetry monitoring protocol.', '2026-09-07 03:40:00', 0, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(3, 8, 3, 'Dr. Jenkins, morning vital signs for Robert Brown in ICU-101 have been recorded: BP 152/94, HR 92 bpm, SpO2 95% on 2L.', '2026-09-07 03:00:00', 1, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(4, 11, 1, 'hi sir', '2026-09-07 10:00:01', 1, '2026-09-07 10:00:01', '2026-09-07 10:02:25');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_01_000001_create_roles_and_permissions_tables', 1),
(5, '2026_09_01_000002_create_hospital_organization_tables', 1),
(6, '2026_09_01_000003_create_patients_and_emergency_tables', 1),
(7, '2026_09_01_000004_create_appointments_and_availabilities_tables', 1),
(8, '2026_09_01_000005_create_medical_records_and_pharmacy_tables', 1),
(9, '2026_09_01_000006_create_facilities_and_admissions_tables', 1),
(10, '2026_09_01_000007_create_billing_and_invoices_tables', 1),
(11, '2026_09_01_000008_create_communication_and_system_tables', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `type` enum('appointment','billing','lab_result','system') NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 1, 'Daily System & Audit Backup Verified', 'Automated relational database integrity check and RBAC compliance stream verified.', 'system', 0, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(2, 1, 'Low Stock Warning: Zithromax 500mg', 'Pharmacy inventory for Zithromax 500mg has reached 8 units (Threshold: 20 units).', 'system', 0, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(3, 1, 'Low Stock Warning: Plavix 75mg', 'Pharmacy inventory for Plavix 75mg has reached 12 units (Threshold: 30 units).', 'system', 0, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(4, 3, 'Clinical Queue Ready: 5 Consultations Today', 'You have 5 scheduled patient appointments today in Cardiology Outpatient Clinic.', 'appointment', 0, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(5, 3, 'Critical Lab Result Published: Troponin I', 'Troponin I lab report for Inpatient Robert Brown (ICU-101) has been published: 1450 ng/L.', 'lab_result', 0, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(6, 12, 'Diagnostic Results Ready: Lipid Profile & ECG', 'Your diagnostic lab results for Lipid Profile Panel and 12-lead ECG are verified and ready in your portal.', 'lab_result', 0, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(7, 12, 'Appointment Reminder: Today at 09:30 AM', 'Your cardiology follow-up consultation with Dr. Sarah Jenkins is scheduled for today at 09:30 AM.', 'appointment', 0, '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(8, 1, 'New Internal Message', 'Message from Sophia Patel (Cashier & Billing): hi sir...', 'system', 0, '2026-09-07 10:00:01', '2026-09-07 10:00:01');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `patient_code` varchar(30) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `patient_code`, `first_name`, `last_name`, `dob`, `gender`, `blood_type`, `phone`, `email`, `address`, `medical_history`, `created_at`, `updated_at`) VALUES
(1, 12, 'PAT-2026-0001', 'Johnathan', 'Doe', '1988-05-14', 'Male', 'O+', '+1 (555) 789-0123', 'patient.john@hospital.test', '742 Evergreen Terrace, Springfield, IL 62704', 'Hypertension Stage 1 diagnosed in 2021. Mild penicillin allergy. Non-smoker.', '2026-09-07 09:46:08', '2026-09-07 09:46:08'),
(2, 13, 'PAT-2026-0002', 'Emma', 'Watson', '1995-11-20', 'Female', 'A+', '+1 (555) 890-1234', 'patient.emma@hospital.test', '12 Grimmauld Place, Boston, MA 02108', 'Seasonal allergic rhinitis. Chronic migraine aura. No prior surgeries.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, NULL, 'PAT-2026-0003', 'Robert', 'Brown', '1965-03-08', 'Male', 'B+', '+1 (555) 901-2345', 'robert.brown@example.com', '404 Baker Street, New York, NY 10001', 'Type 2 Diabetes Mellitus on Metformin. Coronary artery stent (LAD) in 2019.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(4, NULL, 'PAT-2026-0004', 'Olivia', 'Davis', '2018-07-22', 'Female', 'O-', '+1 (555) 345-6711', 'olivia.davis@example.com', '88 Willow Creek Lane, Cambridge, MA 02138', 'Childhood bronchial asthma. Fully vaccinated per CDC schedule. Peanut allergy.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(5, NULL, 'PAT-2026-0005', 'William', 'Taylor', '1976-09-12', 'Male', 'AB+', '+1 (555) 456-7822', 'william.taylor@example.com', '312 Beacon Hill Road, Boston, MA 02116', 'Right knee osteoarthritis Grade 3. Post-arthroscopy status (2022).', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(6, NULL, 'PAT-2026-0006', 'Sophia', 'Martinez', '1992-04-30', 'Female', 'A-', '+1 (555) 567-8933', 'sophia.martinez@example.com', '540 Commonwealth Ave, Boston, MA 02215', 'Hypothyroidism on Levothyroxine 75mcg daily. Sulfa drugs intolerance.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(7, NULL, 'PAT-2026-0007', 'David', 'Clark', '1958-12-05', 'Male', 'O+', '+1 (555) 678-9044', 'david.clark@example.com', '71 Harvard Yard Way, Cambridge, MA 02139', 'Atrial Fibrillation on Apixaban. Hyperlipidemia on Rosuvastatin.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(8, NULL, 'PAT-2026-0008', 'Charlotte', 'Lee', '2001-08-19', 'Female', 'B-', '+1 (555) 789-0155', 'charlotte.lee@example.com', '190 Tremont St, Boston, MA 02111', 'Anxiety disorder. Iron deficiency anemia managed with oral supplements.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(9, NULL, 'PAT-2026-0009', 'James', 'Rodriguez', '1984-02-17', 'Male', 'A+', '+1 (555) 890-1266', 'james.rodriguez@example.com', '224 Newbury St, Boston, MA 02116', 'Lumbar disc herniation L4-L5. NSAID gastritis history.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(10, NULL, 'PAT-2026-0010', 'Grace', 'Kim', '2020-10-10', 'Female', 'O+', '+1 (555) 901-2377', 'grace.kim@example.com', '45 Boylston St, Chestnut Hill, MA 02467', 'Recurrent acute otitis media. No known drug allergies.', '2026-09-07 09:46:09', '2026-09-07 09:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `patient_documents`
--

CREATE TABLE `patient_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payment_number` varchar(30) NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','insurance','upi','bank_transfer') NOT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('completed','failed','refunded') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_number`, `invoice_id`, `payment_date`, `amount_paid`, `payment_method`, `transaction_reference`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PAY-2026-0001', 1, '2026-08-24 12:15:00', 236.25, 'credit_card', 'TXN-VISA-904812', 'Settled in full via contactless Visa card at front desk.', 'completed', '2026-09-07 09:46:10', '2026-09-07 09:46:10'),
(2, 'PAY-2026-0002', 2, '2026-09-06 16:00:00', 1000.00, 'insurance', 'INS-BLUECROSS-CLM-88421', 'BlueCross Primary Insurance pre-authorized advance payment.', 'completed', '2026-09-07 09:46:10', '2026-09-07 09:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Manage Settings', 'manage_settings', 'Configure hospital system parameters', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(2, 'Manage Users', 'manage_users', 'Create, edit, and manage user accounts and roles', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(3, 'View Audit Logs', 'view_audit_logs', 'View system activity and security audit trail', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(4, 'Manage Departments', 'manage_departments', 'Setup and edit hospital departments', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(5, 'Manage Facilities', 'manage_facilities', 'Manage rooms, beds, and ward units', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(6, 'Register Patients', 'register_patients', 'Register new patients and emergency contacts', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(7, 'View Patients', 'view_patients', 'View patient profiles and medical records', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(8, 'Manage Appointments', 'manage_appointments', 'Book, reschedule, and cancel appointments', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(9, 'Set Doctor Schedule', 'set_doctor_schedule', 'Manage doctor shifts and availability', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(10, 'Create Medical Records', 'create_medical_records', 'Create consultation SOAP notes and diagnoses', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(11, 'Prescribe Medicines', 'prescribe_medicines', 'Issue digital prescriptions to patients', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(12, 'Order Lab Tests', 'order_lab_tests', 'Order diagnostic and laboratory tests', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(13, 'Process Lab Reports', 'process_lab_reports', 'Input lab test results and attach reports', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(14, 'Admit Patients', 'admit_patients', 'Authorize and assign beds for inpatient admissions', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(15, 'Dispense Medicines', 'dispense_medicines', 'Fulfill prescriptions and manage pharmacy stock', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(16, 'Manage Pharmacy Stock', 'manage_pharmacy_stock', 'Add and adjust medicine inventory', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(17, 'Manage Billing', 'manage_billing', 'Create invoices and collect payments', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(18, 'View Reports', 'view_reports', 'View clinical and financial analytics reports', '2026-09-07 09:45:56', '2026-09-07 09:45:56');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_role`
--

INSERT INTO `permission_role` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(4, 1),
(4, 2),
(5, 1),
(5, 2),
(6, 1),
(6, 2),
(6, 4),
(7, 1),
(7, 2),
(7, 3),
(7, 4),
(8, 1),
(8, 2),
(8, 3),
(8, 4),
(9, 1),
(9, 3),
(10, 1),
(10, 3),
(11, 1),
(11, 3),
(12, 1),
(12, 3),
(13, 1),
(13, 4),
(14, 1),
(14, 2),
(14, 3),
(14, 4),
(15, 1),
(15, 2),
(15, 4),
(16, 1),
(16, 2),
(16, 4),
(17, 1),
(17, 2),
(17, 4),
(18, 1),
(18, 2);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `medical_record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `doctor_id` int(10) UNSIGNED NOT NULL,
  `prescribed_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','dispensed','cancelled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `medical_record_id`, `patient_id`, `doctor_id`, `prescribed_date`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-08-24', 'Take Atorvastatin daily at bedtime. Continue low-sodium dietary modifications.', 'dispensed', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(2, 2, 2, 2, '2026-08-28', 'Use Sumatriptan at first onset of migraine aura. Do not exceed 200mg in 24 hours.', 'active', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, 3, 3, 1, '2026-09-04', 'Inpatient telemetry acute NSTEMI regimen: DAPT + Statin + Glycemic control.', 'dispensed', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(4, 5, 9, 4, '2026-09-03', 'Neuropathic pain relief for L5 radiculopathy.', 'active', '2026-09-07 09:46:09', '2026-09-07 09:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prescription_id` bigint(20) UNSIGNED NOT NULL,
  `medicine_id` int(10) UNSIGNED NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 1,
  `quantity_prescribed` int(11) NOT NULL DEFAULT 1,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescription_items`
--

INSERT INTO `prescription_items` (`id`, `prescription_id`, `medicine_id`, `dosage`, `frequency`, `duration_days`, `quantity_prescribed`, `instructions`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '20mg', 'Once daily (Night)', 30, 30, 'Take 1 tablet every evening after dinner with water.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(2, 1, 4, '5mg', 'Once daily (Morning)', 30, 30, 'Take 1 tablet every morning with breakfast.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(3, 2, 13, '50mg', 'As needed for acute attack', 30, 6, 'Take 1 tablet at onset of headache. May repeat in 2 hours if headache recurs.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(4, 3, 15, '75mg', 'Once daily', 30, 30, 'Take 1 tablet daily with or without food.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(5, 3, 3, '500mg', 'Twice daily with meals', 30, 60, 'Take 1 tablet with breakfast and 1 with dinner.', '2026-09-07 09:46:09', '2026-09-07 09:46:09'),
(6, 4, 12, '75mg', 'Once daily at bedtime', 14, 14, 'Take 1 capsule at bedtime. May cause drowsiness.', '2026-09-07 09:46:09', '2026-09-07 09:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'Super Administrator', 'Full system access, security governance, and configurations', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(2, 'admin', 'Hospital Admin', 'Hospital manager and operational administrator', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(3, 'doctor', 'Doctor / Physician', 'Clinical diagnosis, prescribing, and patient care', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(4, 'staff', 'Hospital Staff', 'Support staff (Reception, Nursing, Lab, Pharmacy, Billing)', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(5, 'patient', 'Patient', 'Patient portal user for appointments, records, and billing', '2026-09-07 09:45:55', '2026-09-07 09:45:55'),
(6, 'user', 'General / Guest User', 'Unverified or registered visitor on public portal', '2026-09-07 09:45:55', '2026-09-07 09:45:55');

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 3),
(5, 3),
(6, 3),
(7, 4),
(8, 4),
(9, 4),
(10, 4),
(11, 4),
(12, 5),
(13, 5);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `room_type` enum('ICU','Private','Semi-Private','General Ward','Operating Theater') NOT NULL,
  `department_id` int(10) UNSIGNED NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `status` enum('available','full','maintenance') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `room_type`, `department_id`, `daily_rate`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ICU-101', 'ICU', 8, 450.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(2, 'ICU-102', 'ICU', 1, 500.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(3, 'PRV-201', 'Private', 1, 250.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(4, 'PRV-202', 'Private', 2, 250.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(5, 'PRV-203', 'Private', 4, 250.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(6, 'SEMI-301', 'Semi-Private', 3, 160.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(7, 'SEMI-302', 'Semi-Private', 6, 160.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(8, 'GEN-401', 'General Ward', 6, 80.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(9, 'GEN-402', 'General Ward', 4, 80.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56'),
(10, 'OT-01', 'Operating Theater', 6, 600.00, 'available', '2026-09-07 09:45:56', '2026-09-07 09:45:56');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('CyEAtUTGXQxMj2TXNxJzsslNtifQWp8dQwRMcJzl', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNmNRaGNnTUE2RUxsMkdOYXFBS2ZoUUlyRGFuQzBreThBTjFnWGdjdyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9tZXNzYWdlcz91c2VyX2lkPTExIjtzOjU6InJvdXRlIjtzOjIyOiJjb21tdW5pY2F0aW9uLm1lc3NhZ2VzIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1788795145);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `job_title` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `hire_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `user_id`, `department_id`, `job_title`, `phone`, `hire_date`, `created_at`, `updated_at`) VALUES
(1, 7, 8, 'Chief Receptionist / Front Desk Officer', '+1 (555) 601-1122', '2024-09-07', '2026-09-07 09:46:03', '2026-09-07 09:46:03'),
(2, 8, 1, 'Senior Head Nurse - Inpatient Care', '+1 (555) 602-2233', '2024-09-07', '2026-09-07 09:46:04', '2026-09-07 09:46:04'),
(3, 9, 8, 'Lead Hospital Pharmacist', '+1 (555) 603-3344', '2024-09-07', '2026-09-07 09:46:05', '2026-09-07 09:46:05'),
(4, 10, 7, 'Senior Laboratory Technologist', '+1 (555) 604-4455', '2024-09-07', '2026-09-07 09:46:06', '2026-09-07 09:46:06'),
(5, 11, 8, 'Billing Officer & Cashier', '+1 (555) 605-5566', '2024-09-07', '2026-09-07 09:46:07', '2026-09-07 09:46:07');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'hospital_name', 'Apex Horizon International Center', NULL, '2026-09-07 09:46:10', '2026-09-07 10:01:51'),
(2, 'hospital_phone', '+1 (800) 555-APEX / +1 (555) 019-9000', NULL, '2026-09-07 09:46:10', '2026-09-07 10:01:51'),
(3, 'hospital_email', 'contact@apexmedical.test', NULL, '2026-09-07 09:46:10', '2026-09-07 10:01:51'),
(4, 'hospital_address', '500 Health Sciences Blvd, Medical District, Boston MA 02115', NULL, '2026-09-07 09:46:10', '2026-09-07 10:01:51'),
(5, 'tax_rate_percent', '5.0', NULL, '2026-09-07 09:46:10', '2026-09-07 10:01:51'),
(6, 'currency_symbol', '$', NULL, '2026-09-07 09:46:10', '2026-09-07 10:01:51'),
(7, 'appointment_slot_duration_minutes', '30', NULL, '2026-09-07 09:46:10', '2026-09-07 10:01:51'),
(8, 'emergency_contact_number', '911 / +1 (555) 911-0000', NULL, '2026-09-07 09:46:10', '2026-09-07 10:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `email_verified_at`, `password`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'Dr. Alexander Vance (Superadmin)', 'superadmin@hospital.test', NULL, '$2y$12$HuX.h9cgmN435xCTrWT/7.V.cetTq68VhSPuDueL1.6iZrt9rHZBG', 'active', NULL, '2026-09-07 09:45:57', '2026-09-07 09:45:57'),
(2, 'admin', 'Elena Rostova (Admin)', 'admin@hospital.test', NULL, '$2y$12$w6Y3zcfGLaVZePneIQA1q.fD6RetT7x7u5oxLPp94xNpD6wglnNRO', 'active', NULL, '2026-09-07 09:45:57', '2026-09-07 09:45:57'),
(3, 'dr_sarah', 'Dr. Sarah Jenkins', 'dr.sarah@hospital.test', NULL, '$2y$12$DhP.00pNAurB2ZFkFKAvAeo3jyoIY91zSMw6C9NHI46AP7hpsmey.', 'active', NULL, '2026-09-07 09:45:58', '2026-09-07 09:45:58'),
(4, 'dr_james', 'Dr. James Wilson', 'dr.james@hospital.test', NULL, '$2y$12$ZvQhXbpT7IxRgCS.5wsKLebHW/pSoHXmCnRh1tb5upn.LRXKRNrNa', 'active', NULL, '2026-09-07 09:45:59', '2026-09-07 09:45:59'),
(5, 'dr_emily', 'Dr. Emily Chang', 'dr.emily@hospital.test', NULL, '$2y$12$Aw0w6DfnZdORKm/JEVeEQeDyZr32NnjQz7Jlt2wb/0mocfS6Snyjm', 'active', NULL, '2026-09-07 09:46:00', '2026-09-07 09:46:00'),
(6, 'dr_robert', 'Dr. Robert Taylor', 'dr.robert@hospital.test', NULL, '$2y$12$SKyQNnJdTqTFzAbPGZiakO2BgwEYeFKHFNDbfzC1dzuYT0QWrIbMi', 'active', NULL, '2026-09-07 09:46:01', '2026-09-07 09:46:01'),
(7, 'receptionist', 'Rachel Adams (Receptionist)', 'receptionist@hospital.test', NULL, '$2y$12$SnjjhR3SXmbNKB1JrmRDeOCbikgLI57Cvpw9mVobfKd.J2rD9wFbm', 'active', NULL, '2026-09-07 09:46:02', '2026-09-07 09:46:02'),
(8, 'nurse', 'Nurse Clara Nightingale', 'nurse@hospital.test', NULL, '$2y$12$ljQ8ntVOvP43inFASngPVu2HUSuEL8tiinpWFUEaE5hcFUc2e7cdC', 'active', NULL, '2026-09-07 09:46:04', '2026-09-07 09:46:04'),
(9, 'pharmacist', 'Michael Chen (Pharmacist)', 'pharmacist@hospital.test', NULL, '$2y$12$pbdZM47koR3Iu.QQYNBhUOZOCLbzYM1qS/yZAndSbFrcmKzycm.kK', 'active', NULL, '2026-09-07 09:46:05', '2026-09-07 09:46:05'),
(10, 'labtech', 'David Miller (Lab Tech)', 'labtech@hospital.test', NULL, '$2y$12$lceD8z7VLL2wZHqQEnsi4O7SOBQsvc6FjIjXeGr4k/D0twyNvt/96', 'active', NULL, '2026-09-07 09:46:06', '2026-09-07 09:46:06'),
(11, 'cashier', 'Sophia Patel (Cashier & Billing)', 'cashier@hospital.test', NULL, '$2y$12$kza4Afdht443m8ewhAhrr.nFacVX3msUHulGmKTGFLArQmsNJek4.', 'active', NULL, '2026-09-07 09:46:07', '2026-09-07 09:46:07'),
(12, 'patient_john', 'Johnathan Doe', 'patient.john@hospital.test', NULL, '$2y$12$qd8nOz0jNNbsdCPvkxECzOT9Fkgezy2bjN9UlPyxyBiad6nP6iBk.', 'active', NULL, '2026-09-07 09:46:07', '2026-09-07 09:46:07'),
(13, 'patient_emma', 'Emma Watson', 'patient.emma@hospital.test', NULL, '$2y$12$iTirtHGkU78r5xGYVTMpNu8a8dLyiIs140SzCtKIR1Yn2Sot3WbIa', 'active', NULL, '2026-09-07 09:46:08', '2026-09-07 09:46:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admissions_patient_id_foreign` (`patient_id`),
  ADD KEY `admissions_bed_id_foreign` (`bed_id`),
  ADD KEY `admissions_doctor_id_foreign` (`doctor_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointments_patient_id_foreign` (`patient_id`),
  ADD KEY `appointments_doctor_id_foreign` (`doctor_id`),
  ADD KEY `appointments_department_id_foreign` (`department_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `beds`
--
ALTER TABLE `beds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beds_room_id_foreign` (`room_id`);

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
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_code_unique` (`code`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `doctors_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `doctors_license_number_unique` (`license_number`),
  ADD KEY `doctors_department_id_foreign` (`department_id`);

--
-- Indexes for table `doctor_availabilities`
--
ALTER TABLE `doctor_availabilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_availabilities_doctor_id_foreign` (`doctor_id`);

--
-- Indexes for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emergency_contacts_patient_id_foreign` (`patient_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_patient_id_foreign` (`patient_id`),
  ADD KEY `invoices_admission_id_foreign` (`admission_id`),
  ADD KEY `invoices_appointment_id_foreign` (`appointment_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_foreign` (`invoice_id`);

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
-- Indexes for table `lab_reports`
--
ALTER TABLE `lab_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_reports_medical_record_id_foreign` (`medical_record_id`),
  ADD KEY `lab_reports_patient_id_foreign` (`patient_id`),
  ADD KEY `lab_reports_lab_test_id_foreign` (`lab_test_id`),
  ADD KEY `lab_reports_doctor_id_foreign` (`doctor_id`),
  ADD KEY `lab_reports_technician_id_foreign` (`technician_id`);

--
-- Indexes for table `lab_tests`
--
ALTER TABLE `lab_tests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_tests_code_unique` (`code`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `managers_user_id_unique` (`user_id`),
  ADD KEY `managers_department_id_foreign` (`department_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medical_records_patient_id_foreign` (`patient_id`),
  ADD KEY `medical_records_doctor_id_foreign` (`doctor_id`),
  ADD KEY `medical_records_appointment_id_foreign` (`appointment_id`);

--
-- Indexes for table `medical_record_details`
--
ALTER TABLE `medical_record_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medical_record_details_medical_record_id_foreign` (`medical_record_id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_sender_id_foreign` (`sender_id`),
  ADD KEY `messages_receiver_id_foreign` (`receiver_id`);

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
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patients_patient_code_unique` (`patient_code`),
  ADD UNIQUE KEY `patients_user_id_unique` (`user_id`);

--
-- Indexes for table `patient_documents`
--
ALTER TABLE `patient_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_documents_patient_id_foreign` (`patient_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
  ADD KEY `payments_invoice_id_foreign` (`invoice_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescriptions_medical_record_id_foreign` (`medical_record_id`),
  ADD KEY `prescriptions_patient_id_foreign` (`patient_id`),
  ADD KEY `prescriptions_doctor_id_foreign` (`doctor_id`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescription_items_prescription_id_foreign` (`prescription_id`),
  ADD KEY `prescription_items_medicine_id_foreign` (`medicine_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_user_role_id_foreign` (`role_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rooms_room_number_unique` (`room_number`),
  ADD KEY `rooms_department_id_foreign` (`department_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_user_id_unique` (`user_id`),
  ADD KEY `staff_department_id_foreign` (`department_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_setting_key_unique` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `beds`
--
ALTER TABLE `beds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `doctor_availabilities`
--
ALTER TABLE `doctor_availabilities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_reports`
--
ALTER TABLE `lab_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lab_tests`
--
ALTER TABLE `lab_tests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medical_record_details`
--
ALTER TABLE `medical_record_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `patient_documents`
--
ALTER TABLE `patient_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admissions`
--
ALTER TABLE `admissions`
  ADD CONSTRAINT `admissions_bed_id_foreign` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`id`),
  ADD CONSTRAINT `admissions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admissions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `beds`
--
ALTER TABLE `beds`
  ADD CONSTRAINT `beds_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doctors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_availabilities`
--
ALTER TABLE `doctor_availabilities`
  ADD CONSTRAINT `doctor_availabilities_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `emergency_contacts_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_admission_id_foreign` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lab_reports`
--
ALTER TABLE `lab_reports`
  ADD CONSTRAINT `lab_reports_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lab_reports_lab_test_id_foreign` FOREIGN KEY (`lab_test_id`) REFERENCES `lab_tests` (`id`),
  ADD CONSTRAINT `lab_reports_medical_record_id_foreign` FOREIGN KEY (`medical_record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lab_reports_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lab_reports_technician_id_foreign` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `managers`
--
ALTER TABLE `managers`
  ADD CONSTRAINT `managers_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `managers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `medical_records_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_record_details`
--
ALTER TABLE `medical_record_details`
  ADD CONSTRAINT `medical_record_details_medical_record_id_foreign` FOREIGN KEY (`medical_record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `patient_documents`
--
ALTER TABLE `patient_documents`
  ADD CONSTRAINT `patient_documents_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_medical_record_id_foreign` FOREIGN KEY (`medical_record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `prescription_items_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`),
  ADD CONSTRAINT `prescription_items_prescription_id_foreign` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
