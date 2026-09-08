-- ========================================================
-- Hospital Management System (HMS) - Full Database Dump
-- Apex Horizon International Medical Center
-- Compatible with MySQL / MariaDB / XAMPP phpMyAdmin
-- Generated: 2026-09-07 14:34:13
-- ========================================================

CREATE DATABASE IF NOT EXISTS `hospital` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hospital`;

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table structure for table `admissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admissions`;
CREATE TABLE `admissions` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `patient_id` BIGINT not null, `bed_id` BIGINT not null, `doctor_id` BIGINT not null, `admission_date` DATETIME not null, `discharge_date` DATETIME, `admission_reason` text, `discharge_notes` text, `status` VARCHAR(255)  not null default 'admitted', `created_at` DATETIME, `updated_at` DATETIME, foreign key(`patient_id`) references `patients`(`id`) on delete cascade, foreign key(`bed_id`) references `beds`(`id`) on delete restrict, foreign key(`doctor_id`) references `doctors`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `admissions`
INSERT INTO `admissions` (`id`, `patient_id`, `bed_id`, `doctor_id`, `admission_date`, `discharge_date`, `admission_reason`, `discharge_notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 1, '2026-09-04 09:00:00', NULL, 'Acute NSTEMI post-coronary care observation and continuous cardiac telemetry monitoring.', NULL, 'admitted', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(2, 5, 9, 4, '2026-09-06 14:30:00', NULL, 'Pre-operative preparation and pain management for right knee arthroplasty.', NULL, 'admitted', '2026-09-07 14:32:23', '2026-09-07 14:32:23');

-- --------------------------------------------------------
-- Table structure for table `appointments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `patient_id` BIGINT not null, `doctor_id` BIGINT not null, `department_id` BIGINT, `appointment_date` date not null, `time_slot` VARCHAR(255) not null, `status` VARCHAR(255)  not null default 'scheduled', `reason` text, `doctor_notes` text, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`patient_id`) references `patients`(`id`) on delete cascade, foreign key(`doctor_id`) references `doctors`(`id`) on delete cascade, foreign key(`department_id`) references `departments`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `appointments`
INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `department_id`, `appointment_date`, `time_slot`, `status`, `reason`, `doctor_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-09-07 00:00:00', '09:30 AM', 'scheduled', 'Routine cardiology follow-up and blood pressure assessment.', 'Patient requested review of ACE inhibitor dosage.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(2, 2, 2, 2, '2026-09-07 00:00:00', '10:30 AM', 'scheduled', 'Severe episodic throbbing headache with visual aura.', 'Evaluate for prophylactic topiramate vs triptan regimen.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(3, 4, 3, 3, '2026-09-07 00:00:00', '11:00 AM', 'scheduled', 'Nocturnal cough and wheezing post viral URI.', 'Peak flow measurement and inhaler technique review.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(4, 5, 4, 4, '2026-09-07 00:00:00', '02:00 PM', 'scheduled', 'Right knee joint pain during stair climbing.', 'Check standing AP/lateral knee X-rays and range of motion.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(5, 6, 1, 1, '2026-09-07 00:00:00', '03:30 PM', 'scheduled', 'Intermittent palpitations on exertion.', 'Holter monitor requisition evaluation.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(6, 1, 1, 1, '2026-08-24 00:00:00', '11:30 AM', 'completed', 'Substernal chest tightness with moderate exertion.', 'ECG performed. Advised Lipid Profile panel. Prescribed Atorvastatin.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(7, 2, 2, 2, '2026-08-28 00:00:00', '02:30 PM', 'completed', 'Follow-up for chronic tension-type headache.', 'Neurological exam normal. Stress reduction techniques advised.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(8, 3, 1, 1, '2026-09-04 00:00:00', '08:00 AM', 'completed', 'Acute chest pain radiating to left shoulder - Emergency Admission.', 'Admitted to ICU Room 101 Bed B1 for continuous telemetry.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(9, 7, 1, 1, '2026-08-31 00:00:00', '09:00 AM', 'completed', 'Atrial Fibrillation rate control assessment.', 'Target heart rate achieved. Stable INR / Anticoagulation.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(10, 8, 3, 3, '2026-09-02 00:00:00', '10:00 AM', 'completed', 'General health clearance for sports tournament.', 'Normal cardiovascular and pulmonary exam. Cleared for athletics.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(11, 9, 4, 4, '2026-09-03 00:00:00', '03:00 PM', 'completed', 'Chronic lower back pain radiating down left posterior thigh.', 'SLR test positive at 45 degrees left. Ordered Lumbar Spine MRI.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(12, 1, 1, 1, '2026-09-14 00:00:00', '10:00 AM', 'scheduled', 'Post-treatment lipid profile check & medication review.', NULL, '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(13, 3, 1, 1, '2026-09-12 00:00:00', '11:00 AM', 'scheduled', 'Post-discharge recovery check & cardiac rehab orientation.', NULL, '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(14, 5, 4, 4, '2026-09-10 00:00:00', '02:30 PM', 'scheduled', 'Intra-articular hyaluronic acid injection follow-up.', NULL, '2026-09-07 14:32:23', '2026-09-07 14:32:23');

-- --------------------------------------------------------
-- Table structure for table `audit_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT, `action` VARCHAR(255) not null, `table_name` VARCHAR(255), `record_id` BIGINT, `ip_address` VARCHAR(255), `details` text, `created_at` DATETIME not null default CURRENT_TIMESTAMP, foreign key(`user_id`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `audit_logs`
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `ip_address`, `details`, `created_at`) VALUES
(1, 1, 'LOGIN', 'users', 1, '127.0.0.1', 'Superadmin master authentication from local gateway', '2026-09-07 13:47:25'),
(2, 3, 'CREATE', 'medical_records', 1, '127.0.0.1', 'Created EMR consultation record for Patient PAT-2026-0001', '2026-09-07 14:02:25'),
(3, 1, 'ADMIT', 'admissions', 1, '127.0.0.1', 'Admitted Patient Robert Brown to ICU-101 Bed B1', '2026-09-07 14:12:25'),
(4, 9, 'DISPENSE', 'prescriptions', 1, '127.0.0.1', 'Dispensed medications for Prescription #1 (Atorvastatin, Amlodipine)', '2026-09-07 14:17:25'),
(5, 11, 'PAYMENT', 'payments', 1, '127.0.0.1', 'Collected $236.25 payment via Visa for Invoice INV-2026-0001', '2026-09-07 14:22:25');

-- --------------------------------------------------------
-- Table structure for table `beds`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `beds`;
CREATE TABLE `beds` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `room_id` BIGINT not null, `bed_number` VARCHAR(255) not null, `status` VARCHAR(255)  not null default 'available', `created_at` DATETIME, `updated_at` DATETIME, foreign key(`room_id`) references `rooms`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `beds`
INSERT INTO `beds` (`id`, `room_id`, `bed_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'B1', 'occupied', '2026-09-07 14:32:17', '2026-09-07 14:32:23'),
(2, 1, 'B2', 'cleaning', '2026-09-07 14:32:17', '2026-09-07 14:32:23'),
(3, 1, 'B3', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(4, 1, 'B4', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(5, 2, 'B1', 'maintenance', '2026-09-07 14:32:17', '2026-09-07 14:32:23'),
(6, 2, 'B2', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(7, 2, 'B3', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(8, 2, 'B4', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(9, 3, 'B1', 'occupied', '2026-09-07 14:32:17', '2026-09-07 14:32:23'),
(10, 4, 'B1', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(11, 5, 'B1', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(12, 6, 'B1', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(13, 6, 'B2', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(14, 7, 'B1', 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(15, 7, 'B2', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(16, 8, 'B1', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(17, 8, 'B2', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(18, 8, 'B3', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(19, 8, 'B4', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(20, 8, 'B5', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(21, 8, 'B6', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(22, 9, 'B1', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(23, 9, 'B2', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(24, 9, 'B3', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(25, 9, 'B4', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(26, 9, 'B5', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(27, 9, 'B6', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(28, 10, 'B1', 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18');

-- --------------------------------------------------------
-- Table structure for table `cache`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (`key` VARCHAR(255) not null, `value` text not null, `expiration` BIGINT not null, primary key (`key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cache_locks`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (`key` VARCHAR(255) not null, `owner` VARCHAR(255) not null, `expiration` BIGINT not null, primary key (`key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `departments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) not null, `code` VARCHAR(255) not null, `description` text, `icon` VARCHAR(255) default 'hospital', `created_at` DATETIME, `updated_at` DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `departments`
INSERT INTO `departments` (`id`, `name`, `code`, `description`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'Cardiology', 'CARD', 'Comprehensive cardiac care, catheterization, and heart surgery', 'heart-pulse', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(2, 'Neurology', 'NEUR', 'Advanced neurosurgery, stroke unit, and neurological rehabilitation', 'brain', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(3, 'Pediatrics', 'PED', 'Dedicated child healthcare, neonatal ICU, and adolescent medicine', 'baby', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(4, 'Orthopedics', 'ORTH', 'Joint replacement, trauma, sports medicine, and spine care', 'bone', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(5, 'Oncology', 'ONC', 'Medical and surgical oncology, chemotherapy, and radiation therapy', 'ribbon', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(6, 'General Surgery', 'GSURG', 'Minimally invasive laparoscopic, general, and emergency surgery', 'scalpel', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(7, 'Radiology', 'RAD', 'Advanced imaging: MRI, 128-slice CT, Ultrasound, Digital X-Ray', 'x-ray', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(8, 'Emergency Medicine', 'EMER', '24/7 Level 1 Trauma center and critical emergency triage', 'truck-medical', '2026-09-07 14:32:17', '2026-09-07 14:32:17');

-- --------------------------------------------------------
-- Table structure for table `doctor_availabilities`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `doctor_availabilities`;
CREATE TABLE `doctor_availabilities` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `doctor_id` BIGINT not null, `day_of_week` VARCHAR(255)  not null, `start_time` time not null, `end_time` time not null, `is_available` tinyint(1) not null default '1', `created_at` DATETIME, `updated_at` DATETIME, foreign key(`doctor_id`) references `doctors`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `doctor_availabilities`
INSERT INTO `doctor_availabilities` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 'Monday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(2, 1, 'Tuesday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(3, 1, 'Wednesday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(4, 1, 'Thursday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(5, 1, 'Friday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(6, 2, 'Monday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(7, 2, 'Tuesday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(8, 2, 'Wednesday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(9, 2, 'Thursday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(10, 2, 'Friday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(11, 3, 'Monday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(12, 3, 'Tuesday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(13, 3, 'Wednesday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(14, 3, 'Thursday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(15, 3, 'Friday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(16, 4, 'Monday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(17, 4, 'Tuesday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(18, 4, 'Wednesday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(19, 4, 'Thursday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(20, 4, 'Friday', '09:00:00', '17:00:00', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20');

-- --------------------------------------------------------
-- Table structure for table `doctors`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `doctors`;
CREATE TABLE `doctors` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT not null, `department_id` BIGINT, `specialization` VARCHAR(255) not null, `license_number` VARCHAR(255) not null, `consultation_fee` numeric not null default '0', `phone` VARCHAR(255) not null, `bio` text, `is_available` tinyint(1) not null default '1', `created_at` DATETIME, `updated_at` DATETIME, foreign key(`user_id`) references `users`(`id`) on delete cascade, foreign key(`department_id`) references `departments`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `doctors`
INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `specialization`, `license_number`, `consultation_fee`, `phone`, `bio`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'Senior Interventional Cardiologist', 'MED-CARD-98421', 120, '+1 (555) 234-5678', 'Board-certified cardiologist with 15+ years experience in complex coronary interventions and echocardiography.', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(2, 4, 2, 'Consultant Neurologist & Neurophysiologist', 'MED-NEUR-77219', 150, '+1 (555) 345-6789', 'Specializing in acute ischemic stroke, epilepsy management, and neuromuscular disorders.', 1, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(3, 5, 3, 'Pediatrician & Neonatal Specialist', 'MED-PED-55102', 95, '+1 (555) 456-7890', 'Passionate child care specialist with focus on developmental pediatrics and newborn intensive care.', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(4, 6, 4, 'Orthopedic & Joint Reconstruction Surgeon', 'MED-ORTH-33891', 135, '+1 (555) 567-8901', 'Expert in arthroscopic joint reconstruction, total hip/knee arthroplasty, and trauma fixation.', 1, '2026-09-07 14:32:20', '2026-09-07 14:32:20');

-- --------------------------------------------------------
-- Table structure for table `emergency_contacts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `emergency_contacts`;
CREATE TABLE `emergency_contacts` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `patient_id` BIGINT not null, `contact_name` VARCHAR(255) not null, `relationship` VARCHAR(255) not null, `phone` VARCHAR(255) not null, `alt_phone` VARCHAR(255), `created_at` DATETIME, `updated_at` DATETIME, foreign key(`patient_id`) references `patients`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `emergency_contacts`
INSERT INTO `emergency_contacts` (`id`, `patient_id`, `contact_name`, `relationship`, `phone`, `alt_phone`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mary Doe', 'Spouse', '+1 (555) 789-0124', '+1 (555) 789-0125', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(2, 2, 'Chris Watson', 'Brother', '+1 (555) 890-1235', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(3, 3, 'Laura Brown', 'Daughter', '+1 (555) 901-2346', '+1 (555) 901-2347', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(4, 4, 'Marcus Davis', 'Father', '+1 (555) 345-6712', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(5, 5, 'Sarah Taylor', 'Spouse', '+1 (555) 456-7823', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(6, 6, 'Carlos Martinez', 'Brother', '+1 (555) 567-8934', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(7, 7, 'Judith Clark', 'Spouse', '+1 (555) 678-9045', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(8, 8, 'Grace Lee', 'Mother', '+1 (555) 789-0156', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(9, 9, 'Elena Rodriguez', 'Spouse', '+1 (555) 890-1267', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(10, 10, 'Hanna Kim', 'Mother', '+1 (555) 901-2378', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22');

-- --------------------------------------------------------
-- Table structure for table `failed_jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `uuid` VARCHAR(255) not null, `connection` text not null, `queue` text not null, `payload` text not null, `exception` text not null, `failed_at` DATETIME not null default CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `invoice_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `invoice_id` BIGINT not null, `item_description` VARCHAR(255) not null, `quantity` BIGINT not null default '1', `unit_price` numeric not null, `subtotal` numeric not null, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`invoice_id`) references `invoices`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `invoice_items`
INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_description`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 'Doctor Consultation - Dr. Sarah Jenkins (Cardiology)', 1, 120, 120, '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(2, 1, 'Diagnostic Lab: Lipid Profile Panel', 1, 45, 45, '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(3, 1, 'Diagnostic: 12-Lead Electrocardiogram (ECG)', 1, 60, 60, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(4, 2, 'ICU Room 101 - Bed B1 [3 Days @ $450.00/day]', 3, 450, 1350, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(5, 2, 'Diagnostic Lab: High-Sensitivity Troponin I', 1, 75, 75, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(6, 2, 'Diagnostic Radiology: Digital Chest X-Ray (PA)', 1, 80, 80, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(7, 2, 'Attending Physician Specialist Rounding', 1, 70, 70, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(8, 3, 'Neurology Specialist Consultation - Dr. James Wilson', 1, 150, 150, '2026-09-07 14:32:25', '2026-09-07 14:32:25');

-- --------------------------------------------------------
-- Table structure for table `invoices`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `invoice_number` VARCHAR(255) not null, `patient_id` BIGINT not null, `admission_id` BIGINT, `appointment_id` BIGINT, `invoice_date` date not null, `due_date` date not null, `total_amount` numeric not null default '0', `discount_amount` numeric not null default '0', `tax_amount` numeric not null default '0', `net_amount` numeric not null default '0', `status` VARCHAR(255)  not null default 'unpaid', `notes` text, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`patient_id`) references `patients`(`id`) on delete cascade, foreign key(`admission_id`) references `admissions`(`id`) on delete set null, foreign key(`appointment_id`) references `appointments`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `invoices`
INSERT INTO `invoices` (`id`, `invoice_number`, `patient_id`, `admission_id`, `appointment_id`, `invoice_date`, `due_date`, `total_amount`, `discount_amount`, `tax_amount`, `net_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'INV-2026-0001', 1, NULL, 6, '2026-08-24 00:00:00', '2026-08-31 00:00:00', 225, 0, 11.25, 236.25, 'paid', 'Cardiology Consultation + Lipid Profile + 12-Lead ECG Package.', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(2, 'INV-2026-0002', 3, 1, NULL, '2026-09-06 00:00:00', '2026-09-14 00:00:00', 1575, 100, 73.75, 1548.75, 'partially_paid', 'Inpatient ICU 101 telemetry care, Troponin I assay, Digital Chest X-Ray.', '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(3, 'INV-2026-0003', 2, NULL, 7, '2026-08-28 00:00:00', '2026-09-11 00:00:00', 150, 0, 7.5, 157.5, 'unpaid', 'Neurology Specialist Consultation - Dr. James Wilson.', '2026-09-07 14:32:25', '2026-09-07 14:32:25');

-- --------------------------------------------------------
-- Table structure for table `job_batches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (`id` VARCHAR(255) not null, `name` VARCHAR(255) not null, `total_jobs` BIGINT not null, `pending_jobs` BIGINT not null, `failed_jobs` BIGINT not null, `failed_job_ids` text not null, `options` text, `cancelled_at` BIGINT, `created_at` BIGINT not null, `finished_at` BIGINT, primary key (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `queue` VARCHAR(255) not null, `payload` text not null, `attempts` BIGINT not null, `reserved_at` BIGINT, `available_at` BIGINT not null, `created_at` BIGINT not null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `lab_reports`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lab_reports`;
CREATE TABLE `lab_reports` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `medical_record_id` BIGINT, `patient_id` BIGINT not null, `lab_test_id` BIGINT not null, `doctor_id` BIGINT not null, `technician_id` BIGINT, `status` VARCHAR(255)  not null default 'requested', `result_summary` text, `file_path` VARCHAR(255), `report_date` DATETIME, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`medical_record_id`) references `medical_records`(`id`) on delete cascade, foreign key(`patient_id`) references `patients`(`id`) on delete cascade, foreign key(`lab_test_id`) references `lab_tests`(`id`) on delete restrict, foreign key(`doctor_id`) references `doctors`(`id`) on delete cascade, foreign key(`technician_id`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `lab_reports`
INSERT INTO `lab_reports` (`id`, `medical_record_id`, `patient_id`, `lab_test_id`, `doctor_id`, `technician_id`, `status`, `result_summary`, `file_path`, `report_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, 1, 10, 'completed', 'Total Cholesterol: 215 mg/dL (Borderline High), HDL: 48 mg/dL (Optimal >40), LDL: 135 mg/dL (Elevated, Target <100), Triglycerides: 160 mg/dL (Borderline High).', NULL, '2026-08-26 15:00:00', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(2, 1, 1, 4, 1, 10, 'completed', 'Normal sinus rhythm, HR 72 bpm. PR interval 160ms, QRS 88ms, QTc 415ms. Normal axis (+60°). No ST-segment elevation, pathological Q waves, or T-wave inversions.', NULL, '2026-08-25 10:30:00', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(3, 3, 3, 9, 1, 10, 'completed', 'High-Sensitivity Troponin I: 1450 ng/L (Ref: <14 ng/L). Significant myocardial necrosis marker elevation consistent with acute NSTEMI.', NULL, '2026-09-04 09:15:00', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(4, 3, 3, 5, 1, 10, 'completed', 'PA Chest View: Normal cardiothoracic ratio (<0.50). Lung fields clear with no focal consolidation, pneumothorax, or pleural effusion. Mediastinal contours unremarkable.', NULL, '2026-09-04 10:00:00', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(5, 5, 9, 7, 4, 10, 'in_progress', 'MRI Lumbar Spine scan acquisition completed. Senior Radiologist review in progress.', NULL, NULL, '2026-09-07 14:32:24', '2026-09-07 14:32:24');

-- --------------------------------------------------------
-- Table structure for table `lab_tests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lab_tests`;
CREATE TABLE `lab_tests` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `test_name` VARCHAR(255) not null, `code` VARCHAR(255) not null, `description` text, `cost` numeric not null, `created_at` DATETIME, `updated_at` DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `lab_tests`
INSERT INTO `lab_tests` (`id`, `test_name`, `code`, `description`, `cost`, `created_at`, `updated_at`) VALUES
(1, 'Complete Blood Count (CBC)', 'LAB-CBC', 'Evaluates overall health and detects wide range of disorders including anemia, infection and leukemia.', 35, '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(2, 'Comprehensive Metabolic Panel (CMP)', 'LAB-CMP', 'Measures 14 substances in blood for kidney/liver status, electrolyte and fluid balance.', 55, '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(3, 'Lipid Profile Panel', 'LAB-LIPID', 'Measures Total Cholesterol, HDL, LDL, and Triglycerides to assess cardiovascular risk.', 45, '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(4, '12-Lead Electrocardiogram (ECG)', 'RAD-ECG', 'Records electrical signals from the heart to detect arrhythmias, ischemia, and infarction.', 60, '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(5, 'Digital Chest X-Ray (PA View)', 'RAD-CXR', 'High-resolution radiograph of chest cavity, cardiac silhouette, and lung fields.', 80, '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(6, 'MRI Brain with Contrast', 'RAD-MRI-BR', 'Magnetic Resonance Imaging of cranial structures for neurological diagnostics.', 450, '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(7, 'MRI Lumbar Spine', 'RAD-MRI-LSP', 'Detailed imaging of lumbar intervertebral discs, spinal canal, and nerve roots.', 420, '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(8, 'HbA1c Glycated Hemoglobin', 'LAB-HBA1C', 'Assesses 3-month average plasma glucose concentration for diabetes management.', 40, '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(9, 'High-Sensitivity Cardiac Troponin I', 'LAB-TROP-I', 'Gold-standard biomarker for myocardial injury and acute coronary syndromes.', 75, '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(10, 'Urinalysis with Microscopic Examination', 'LAB-UA', 'Screening for urinary tract infections, kidney disease, and diabetes mellitus.', 25, '2026-09-07 14:32:24', '2026-09-07 14:32:24');

-- --------------------------------------------------------
-- Table structure for table `managers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `managers`;
CREATE TABLE `managers` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT not null, `department_id` BIGINT, `title` VARCHAR(255) not null, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`user_id`) references `users`(`id`) on delete cascade, foreign key(`department_id`) references `departments`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `managers`
INSERT INTO `managers` (`id`, `user_id`, `department_id`, `title`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Chief Operations Officer', '2026-09-07 14:32:18', '2026-09-07 14:32:18');

-- --------------------------------------------------------
-- Table structure for table `medical_record_details`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `medical_record_details`;
CREATE TABLE `medical_record_details` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `medical_record_id` BIGINT not null, `vital_sign_name` VARCHAR(255) not null, `vital_sign_value` VARCHAR(255) not null, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`medical_record_id`) references `medical_records`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `medical_record_details`
INSERT INTO `medical_record_details` (`id`, `medical_record_id`, `vital_sign_name`, `vital_sign_value`, `created_at`, `updated_at`) VALUES
(1, 1, 'Blood Pressure', '138/86 mmHg', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(2, 1, 'Heart Rate', '74 bpm', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(3, 1, 'SpO2', '98% on Room Air', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(4, 1, 'Temperature', '98.4 °F', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(5, 1, 'Respiratory Rate', '16 breaths/min', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(6, 1, 'Body Weight', '82.5 kg', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(7, 2, 'Blood Pressure', '118/76 mmHg', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(8, 2, 'Heart Rate', '68 bpm', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(9, 2, 'SpO2', '99% on Room Air', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(10, 2, 'Temperature', '98.6 °F', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(11, 2, 'Respiratory Rate', '14 breaths/min', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(12, 2, 'Body Weight', '58.0 kg', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(13, 3, 'Blood Pressure', '152/94 mmHg', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(14, 3, 'Heart Rate', '92 bpm', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(15, 3, 'SpO2', '95% on 2L Nasal Cannula', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(16, 3, 'Temperature', '99.1 °F', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(17, 3, 'Respiratory Rate', '20 breaths/min', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(18, 3, 'Body Weight', '89.0 kg', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(19, 4, 'Blood Pressure', '126/80 mmHg', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(20, 4, 'Heart Rate', '76 bpm (irregular)', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(21, 4, 'SpO2', '98% on Room Air', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(22, 4, 'Temperature', '98.2 °F', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(23, 4, 'Respiratory Rate', '16 breaths/min', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(24, 4, 'Body Weight', '77.0 kg', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(25, 5, 'Blood Pressure', '128/82 mmHg', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(26, 5, 'Heart Rate', '72 bpm', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(27, 5, 'SpO2', '99% on Room Air', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(28, 5, 'Temperature', '98.4 °F', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(29, 5, 'Respiratory Rate', '15 breaths/min', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(30, 5, 'Body Weight', '84.0 kg', '2026-09-07 14:32:23', '2026-09-07 14:32:23');

-- --------------------------------------------------------
-- Table structure for table `medical_records`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `medical_records`;
CREATE TABLE `medical_records` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `patient_id` BIGINT not null, `doctor_id` BIGINT not null, `appointment_id` BIGINT, `visit_date` DATETIME not null, `diagnosis` text not null, `symptoms` text, `notes` text, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`patient_id`) references `patients`(`id`) on delete cascade, foreign key(`doctor_id`) references `doctors`(`id`) on delete cascade, foreign key(`appointment_id`) references `appointments`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `medical_records`
INSERT INTO `medical_records` (`id`, `patient_id`, `doctor_id`, `appointment_id`, `visit_date`, `diagnosis`, `symptoms`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 6, '2026-08-24 11:30:00', 'Essential Hypertension with Stable Angina Pectoris (CCS Class I)', 'Patient reports substernal tightness on stair climbing lasting < 3 mins, relieved by rest. No radiation, no diaphoresis, no dyspnea at rest.', 'SOAP Assessment: Cardiovascular exam reveals S1, S2 normal, no S3/S4, no murmurs. JVP normal. Lungs clear to auscultation bilaterally. Plan: Start Atorvastatin 20mg QHS, Amlodipine 5mg QAM. Requisitioned 12-lead ECG and Lipid Profile Panel.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(2, 2, 2, 7, '2026-08-28 14:30:00', 'Migraine with Visual Aura (ICD-10 G43.109) & Tension Headache', 'Unilateral throbbing frontotemporal pain preceded by scintillating scotoma. Accompanied by photophobia and mild nausea.', 'SOAP Assessment: Cranial nerves II-XII intact. Fundoscopy shows no papilledema. Motor strength 5/5 all extremities. Sensation intact. Plan: Acute abortive Sumatriptan 50mg PRN. Prophylactic magnesium & riboflavin. Headache diary initiated.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(3, 3, 1, 8, '2026-09-04 08:30:00', 'Non-ST-Elevation Myocardial Infarction (NSTEMI) & T2DM', 'Acute onset crushing retrosternal chest pain radiating to left jaw, onset 2 hours prior to presentation with diaphoresis and nausea.', 'SOAP Assessment: High-sensitivity Troponin I elevated (1450 ng/L). ECG showed ST depressions in V4-V6. Direct admission to ICU Bed 101-B1. Started dual antiplatelet therapy (DAPT), IV heparin protocol, and high-intensity statin.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(4, 7, 1, 9, '2026-08-31 09:30:00', 'Paroxysmal Atrial Fibrillation on Anticoagulation (CHA2DS2-VASc = 3)', 'Occasional fluttering sensation in chest, no syncopal episodes, good exercise tolerance.', 'SOAP Assessment: Heart sounds irregularly irregular. Controlled ventricular response rate. Advised continuation of Eliquis 5mg BID and Metoprolol Succinate 50mg daily.', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(5, 9, 4, 11, '2026-09-03 15:30:00', 'Lumbar Radiculopathy secondary to L4-L5 Disc Herniation', 'Sharp burning pain down left lateral thigh and calf with numbness over L5 dermatome.', 'SOAP Assessment: Straight Leg Raise positive at 45 degrees on left. EHL weakness 4/5. Patellar and Achilles reflexes 2+ symmetrical. Prescribed Pregabalin 75mg QHS and physical therapy protocol. Requisitioned MRI Spine.', '2026-09-07 14:32:23', '2026-09-07 14:32:23');

-- --------------------------------------------------------
-- Table structure for table `medicines`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `medicines`;
CREATE TABLE `medicines` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) not null, `generic_name` VARCHAR(255), `category` VARCHAR(255) not null, `unit_price` numeric not null, `stock_quantity` BIGINT not null default '0', `reorder_level` BIGINT not null default '10', `expiry_date` date, `created_at` DATETIME, `updated_at` DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `medicines`
INSERT INTO `medicines` (`id`, `name`, `generic_name`, `category`, `unit_price`, `stock_quantity`, `reorder_level`, `expiry_date`, `created_at`, `updated_at`) VALUES
(1, 'Augmentin 625mg', 'Amoxicillin + Clavulanic Acid', 'Antibiotic', 18.5, 150, 30, '2027-06-30 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(2, 'Lipitor 20mg', 'Atorvastatin Calcium', 'Cardiovascular', 24, 220, 40, '2028-01-15 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(3, 'Glucophage 500mg', 'Metformin Hydrochloride', 'Antidiabetic', 12, 300, 50, '2027-11-20 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(4, 'Norvasc 5mg', 'Amlodipine Besylate', 'Antihypertensive', 15, 180, 30, '2027-09-10 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(5, 'Tylenol Extra 500mg', 'Paracetamol / Acetaminophen', 'Analgesic', 8, 500, 50, '2028-05-01 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(6, 'Prilosec 20mg', 'Omeprazole Delayed-Release', 'Gastrointestinal', 16.5, 120, 25, '2027-08-18 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(7, 'Zithromax 500mg', 'Azithromycin Dihydrate', 'Antibiotic', 28, 8, 20, '2026-12-31 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(8, 'Ventolin HFA 100mcg', 'Salbutamol Inhalation Aerosol', 'Respiratory', 35, 45, 15, '2027-10-05 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(9, 'Rocephin 1g Vial', 'Ceftriaxone Sodium Injection', 'Antibiotic', 42, 60, 15, '2027-03-25 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(10, 'Advil 400mg', 'Ibuprofen Liqui-Gels', 'NSAID', 9.5, 250, 40, '2028-02-14 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(11, 'Eliquis 5mg', 'Apixaban', 'Cardiovascular', 65, 110, 25, '2028-04-10 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(12, 'Lyrica 75mg', 'Pregabalin', 'Neurology', 38, 90, 20, '2027-12-15 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(13, 'Imitrex 50mg', 'Sumatriptan Succinate', 'Neurology', 45, 70, 15, '2027-07-20 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(14, 'Synthroid 75mcg', 'Levothyroxine Sodium', 'Endocrinology', 14, 210, 35, '2028-06-30 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23'),
(15, 'Plavix 75mg', 'Clopidogrel Bisulfate', 'Cardiovascular', 29.5, 12, 30, '2027-05-15 00:00:00', '2026-09-07 14:32:23', '2026-09-07 14:32:23');

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `sender_id` BIGINT not null, `receiver_id` BIGINT not null, `message_body` text not null, `sent_at` DATETIME not null default CURRENT_TIMESTAMP, `is_read` tinyint(1) not null default '0', `created_at` DATETIME, `updated_at` DATETIME, foreign key(`sender_id`) references `users`(`id`) on delete cascade, foreign key(`receiver_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `messages`
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message_body`, `sent_at`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'Good morning Dr. Vance. Telemetry monitoring equipment in ICU-101 has been calibrated for Patient Robert Brown.', '2026-09-07 08:45:00', 1, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(2, 1, 3, 'Thank you Dr. Sarah. The clinical operations team is briefed on the telemetry monitoring protocol.', '2026-09-07 09:10:00', 0, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(3, 8, 3, 'Dr. Jenkins, morning vital signs for Robert Brown in ICU-101 have been recorded: BP 152/94, HR 92 bpm, SpO2 95% on 2L.', '2026-09-07 08:30:00', 1, '2026-09-07 14:32:25', '2026-09-07 14:32:25');

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `migration` VARCHAR(255) not null, `batch` BIGINT not null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations`
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
-- Table structure for table `notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT not null, `title` VARCHAR(255) not null, `message` text not null, `type` VARCHAR(255)  not null, `is_read` tinyint(1) not null default '0', `created_at` DATETIME, `updated_at` DATETIME, foreign key(`user_id`) references `users`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `notifications`
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 1, 'Daily System & Audit Backup Verified', 'Automated relational database integrity check and RBAC compliance stream verified.', 'system', 0, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(2, 1, 'Low Stock Warning: Zithromax 500mg', 'Pharmacy inventory for Zithromax 500mg has reached 8 units (Threshold: 20 units).', 'system', 0, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(3, 1, 'Low Stock Warning: Plavix 75mg', 'Pharmacy inventory for Plavix 75mg has reached 12 units (Threshold: 30 units).', 'system', 0, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(4, 3, 'Clinical Queue Ready: 5 Consultations Today', 'You have 5 scheduled patient appointments today in Cardiology Outpatient Clinic.', 'appointment', 0, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(5, 3, 'Critical Lab Result Published: Troponin I', 'Troponin I lab report for Inpatient Robert Brown (ICU-101) has been published: 1450 ng/L.', 'lab_result', 0, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(6, 12, 'Diagnostic Results Ready: Lipid Profile & ECG', 'Your diagnostic lab results for Lipid Profile Panel and 12-lead ECG are verified and ready in your portal.', 'lab_result', 0, '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(7, 12, 'Appointment Reminder: Today at 09:30 AM', 'Your cardiology follow-up consultation with Dr. Sarah Jenkins is scheduled for today at 09:30 AM.', 'appointment', 0, '2026-09-07 14:32:25', '2026-09-07 14:32:25');

-- --------------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (`email` VARCHAR(255) not null, `token` VARCHAR(255) not null, `created_at` DATETIME, primary key (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `patient_documents`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `patient_documents`;
CREATE TABLE `patient_documents` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `patient_id` BIGINT not null, `document_type` VARCHAR(255) not null, `file_path` VARCHAR(255) not null, `file_name` VARCHAR(255), `uploaded_at` DATETIME not null default CURRENT_TIMESTAMP, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`patient_id`) references `patients`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `patients`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT, `patient_code` VARCHAR(255) not null, `first_name` VARCHAR(255) not null, `last_name` VARCHAR(255) not null, `dob` date not null, `gender` VARCHAR(255)  not null, `blood_type` VARCHAR(255) , `phone` VARCHAR(255) not null, `email` VARCHAR(255), `address` text, `medical_history` text, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`user_id`) references `users`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `patients`
INSERT INTO `patients` (`id`, `user_id`, `patient_code`, `first_name`, `last_name`, `dob`, `gender`, `blood_type`, `phone`, `email`, `address`, `medical_history`, `created_at`, `updated_at`) VALUES
(1, 12, 'PAT-2026-0001', 'Johnathan', 'Doe', '1988-05-14 00:00:00', 'Male', 'O+', '+1 (555) 789-0123', 'patient.john@hospital.test', '742 Evergreen Terrace, Springfield, IL 62704', 'Hypertension Stage 1 diagnosed in 2021. Mild penicillin allergy. Non-smoker.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(2, 13, 'PAT-2026-0002', 'Emma', 'Watson', '1995-11-20 00:00:00', 'Female', 'A+', '+1 (555) 890-1234', 'patient.emma@hospital.test', '12 Grimmauld Place, Boston, MA 02108', 'Seasonal allergic rhinitis. Chronic migraine aura. No prior surgeries.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(3, NULL, 'PAT-2026-0003', 'Robert', 'Brown', '1965-03-08 00:00:00', 'Male', 'B+', '+1 (555) 901-2345', 'robert.brown@example.com', '404 Baker Street, New York, NY 10001', 'Type 2 Diabetes Mellitus on Metformin. Coronary artery stent (LAD) in 2019.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(4, NULL, 'PAT-2026-0004', 'Olivia', 'Davis', '2018-07-22 00:00:00', 'Female', 'O-', '+1 (555) 345-6711', 'olivia.davis@example.com', '88 Willow Creek Lane, Cambridge, MA 02138', 'Childhood bronchial asthma. Fully vaccinated per CDC schedule. Peanut allergy.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(5, NULL, 'PAT-2026-0005', 'William', 'Taylor', '1976-09-12 00:00:00', 'Male', 'AB+', '+1 (555) 456-7822', 'william.taylor@example.com', '312 Beacon Hill Road, Boston, MA 02116', 'Right knee osteoarthritis Grade 3. Post-arthroscopy status (2022).', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(6, NULL, 'PAT-2026-0006', 'Sophia', 'Martinez', '1992-04-30 00:00:00', 'Female', 'A-', '+1 (555) 567-8933', 'sophia.martinez@example.com', '540 Commonwealth Ave, Boston, MA 02215', 'Hypothyroidism on Levothyroxine 75mcg daily. Sulfa drugs intolerance.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(7, NULL, 'PAT-2026-0007', 'David', 'Clark', '1958-12-05 00:00:00', 'Male', 'O+', '+1 (555) 678-9044', 'david.clark@example.com', '71 Harvard Yard Way, Cambridge, MA 02139', 'Atrial Fibrillation on Apixaban. Hyperlipidemia on Rosuvastatin.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(8, NULL, 'PAT-2026-0008', 'Charlotte', 'Lee', '2001-08-19 00:00:00', 'Female', 'B-', '+1 (555) 789-0155', 'charlotte.lee@example.com', '190 Tremont St, Boston, MA 02111', 'Anxiety disorder. Iron deficiency anemia managed with oral supplements.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(9, NULL, 'PAT-2026-0009', 'James', 'Rodriguez', '1984-02-17 00:00:00', 'Male', 'A+', '+1 (555) 890-1266', 'james.rodriguez@example.com', '224 Newbury St, Boston, MA 02116', 'Lumbar disc herniation L4-L5. NSAID gastritis history.', '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(10, NULL, 'PAT-2026-0010', 'Grace', 'Kim', '2020-10-10 00:00:00', 'Female', 'O+', '+1 (555) 901-2377', 'grace.kim@example.com', '45 Boylston St, Chestnut Hill, MA 02467', 'Recurrent acute otitis media. No known drug allergies.', '2026-09-07 14:32:22', '2026-09-07 14:32:22');

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `payment_number` VARCHAR(255) not null, `invoice_id` BIGINT not null, `payment_date` DATETIME not null default CURRENT_TIMESTAMP, `amount_paid` numeric not null, `payment_method` VARCHAR(255)  not null, `transaction_reference` VARCHAR(255), `notes` text, `status` VARCHAR(255)  not null default 'completed', `created_at` DATETIME, `updated_at` DATETIME, foreign key(`invoice_id`) references `invoices`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `payments`
INSERT INTO `payments` (`id`, `payment_number`, `invoice_id`, `payment_date`, `amount_paid`, `payment_method`, `transaction_reference`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PAY-2026-0001', 1, '2026-08-24 12:15:00', 236.25, 'credit_card', 'TXN-VISA-904812', 'Settled in full via contactless Visa card at front desk.', 'completed', '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(2, 'PAY-2026-0002', 2, '2026-09-06 16:00:00', 1000, 'insurance', 'INS-BLUECROSS-CLM-88421', 'BlueCross Primary Insurance pre-authorized advance payment.', 'completed', '2026-09-07 14:32:25', '2026-09-07 14:32:25');

-- --------------------------------------------------------
-- Table structure for table `permission_role`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `permission_role`;
CREATE TABLE `permission_role` (`permission_id` BIGINT not null, `role_id` BIGINT not null, foreign key(`permission_id`) references `permissions`(`id`) on delete cascade, foreign key(`role_id`) references `roles`(`id`) on delete cascade, primary key (`permission_id`, `role_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `permission_role`
INSERT INTO `permission_role` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(14, 2),
(15, 2),
(8, 2),
(17, 2),
(4, 2),
(5, 2),
(16, 2),
(1, 2),
(2, 2),
(6, 2),
(3, 2),
(7, 2),
(18, 2),
(14, 3),
(10, 3),
(8, 3),
(12, 3),
(11, 3),
(9, 3),
(7, 3),
(14, 4),
(15, 4),
(8, 4),
(17, 4),
(16, 4),
(13, 4),
(6, 4),
(7, 4);

-- --------------------------------------------------------
-- Table structure for table `permissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) not null, `slug` VARCHAR(255) not null, `description` text, `created_at` DATETIME, `updated_at` DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `permissions`
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Manage Settings', 'manage_settings', 'Configure hospital system parameters', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(2, 'Manage Users', 'manage_users', 'Create, edit, and manage user accounts and roles', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(3, 'View Audit Logs', 'view_audit_logs', 'View system activity and security audit trail', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(4, 'Manage Departments', 'manage_departments', 'Setup and edit hospital departments', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(5, 'Manage Facilities', 'manage_facilities', 'Manage rooms, beds, and ward units', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(6, 'Register Patients', 'register_patients', 'Register new patients and emergency contacts', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(7, 'View Patients', 'view_patients', 'View patient profiles and medical records', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(8, 'Manage Appointments', 'manage_appointments', 'Book, reschedule, and cancel appointments', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(9, 'Set Doctor Schedule', 'set_doctor_schedule', 'Manage doctor shifts and availability', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(10, 'Create Medical Records', 'create_medical_records', 'Create consultation SOAP notes and diagnoses', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(11, 'Prescribe Medicines', 'prescribe_medicines', 'Issue digital prescriptions to patients', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(12, 'Order Lab Tests', 'order_lab_tests', 'Order diagnostic and laboratory tests', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(13, 'Process Lab Reports', 'process_lab_reports', 'Input lab test results and attach reports', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(14, 'Admit Patients', 'admit_patients', 'Authorize and assign beds for inpatient admissions', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(15, 'Dispense Medicines', 'dispense_medicines', 'Fulfill prescriptions and manage pharmacy stock', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(16, 'Manage Pharmacy Stock', 'manage_pharmacy_stock', 'Add and adjust medicine inventory', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(17, 'Manage Billing', 'manage_billing', 'Create invoices and collect payments', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(18, 'View Reports', 'view_reports', 'View clinical and financial analytics reports', '2026-09-07 14:32:16', '2026-09-07 14:32:16');

-- --------------------------------------------------------
-- Table structure for table `prescription_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `prescription_items`;
CREATE TABLE `prescription_items` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `prescription_id` BIGINT not null, `medicine_id` BIGINT not null, `dosage` VARCHAR(255) not null, `frequency` VARCHAR(255) not null, `duration_days` BIGINT not null default '1', `quantity_prescribed` BIGINT not null default '1', `instructions` text, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`prescription_id`) references `prescriptions`(`id`) on delete cascade, foreign key(`medicine_id`) references `medicines`(`id`) on delete restrict) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `prescription_items`
INSERT INTO `prescription_items` (`id`, `prescription_id`, `medicine_id`, `dosage`, `frequency`, `duration_days`, `quantity_prescribed`, `instructions`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '20mg', 'Once daily (Night)', 30, 30, 'Take 1 tablet every evening after dinner with water.', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(2, 1, 4, '5mg', 'Once daily (Morning)', 30, 30, 'Take 1 tablet every morning with breakfast.', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(3, 2, 13, '50mg', 'As needed for acute attack', 30, 6, 'Take 1 tablet at onset of headache. May repeat in 2 hours if headache recurs.', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(4, 3, 15, '75mg', 'Once daily', 30, 30, 'Take 1 tablet daily with or without food.', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(5, 3, 3, '500mg', 'Twice daily with meals', 30, 60, 'Take 1 tablet with breakfast and 1 with dinner.', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(6, 4, 12, '75mg', 'Once daily at bedtime', 14, 14, 'Take 1 capsule at bedtime. May cause drowsiness.', '2026-09-07 14:32:24', '2026-09-07 14:32:24');

-- --------------------------------------------------------
-- Table structure for table `prescriptions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE `prescriptions` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `medical_record_id` BIGINT, `patient_id` BIGINT not null, `doctor_id` BIGINT not null, `prescribed_date` date not null, `notes` text, `status` VARCHAR(255)  not null default 'active', `created_at` DATETIME, `updated_at` DATETIME, foreign key(`medical_record_id`) references `medical_records`(`id`) on delete cascade, foreign key(`patient_id`) references `patients`(`id`) on delete cascade, foreign key(`doctor_id`) references `doctors`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `prescriptions`
INSERT INTO `prescriptions` (`id`, `medical_record_id`, `patient_id`, `doctor_id`, `prescribed_date`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-08-24 00:00:00', 'Take Atorvastatin daily at bedtime. Continue low-sodium dietary modifications.', 'dispensed', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(2, 2, 2, 2, '2026-08-28 00:00:00', 'Use Sumatriptan at first onset of migraine aura. Do not exceed 200mg in 24 hours.', 'active', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(3, 3, 3, 1, '2026-09-04 00:00:00', 'Inpatient telemetry acute NSTEMI regimen: DAPT + Statin + Glycemic control.', 'dispensed', '2026-09-07 14:32:24', '2026-09-07 14:32:24'),
(4, 5, 9, 4, '2026-09-03 00:00:00', 'Neuropathic pain relief for L5 radiculopathy.', 'active', '2026-09-07 14:32:24', '2026-09-07 14:32:24');

-- --------------------------------------------------------
-- Table structure for table `role_user`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (`user_id` BIGINT not null, `role_id` BIGINT not null, foreign key(`user_id`) references `users`(`id`) on delete cascade, foreign key(`role_id`) references `roles`(`id`) on delete cascade, primary key (`user_id`, `role_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `role_user`
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
-- Table structure for table `roles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) not null, `display_name` VARCHAR(255), `description` text, `created_at` DATETIME, `updated_at` DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `roles`
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'Super Administrator', 'Full system access, security governance, and configurations', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(2, 'admin', 'Hospital Admin', 'Hospital manager and operational administrator', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(3, 'doctor', 'Doctor / Physician', 'Clinical diagnosis, prescribing, and patient care', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(4, 'staff', 'Hospital Staff', 'Support staff (Reception, Nursing, Lab, Pharmacy, Billing)', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(5, 'patient', 'Patient', 'Patient portal user for appointments, records, and billing', '2026-09-07 14:32:16', '2026-09-07 14:32:16'),
(6, 'user', 'General / Guest User', 'Unverified or registered visitor on public portal', '2026-09-07 14:32:16', '2026-09-07 14:32:16');

-- --------------------------------------------------------
-- Table structure for table `rooms`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `room_number` VARCHAR(255) not null, `room_type` VARCHAR(255)  not null, `department_id` BIGINT not null, `daily_rate` numeric not null, `status` VARCHAR(255)  not null default 'available', `created_at` DATETIME, `updated_at` DATETIME, foreign key(`department_id`) references `departments`(`id`) on delete cascade) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `rooms`
INSERT INTO `rooms` (`id`, `room_number`, `room_type`, `department_id`, `daily_rate`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ICU-101', 'ICU', 8, 450, 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(2, 'ICU-102', 'ICU', 1, 500, 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(3, 'PRV-201', 'Private', 1, 250, 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(4, 'PRV-202', 'Private', 2, 250, 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(5, 'PRV-203', 'Private', 4, 250, 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(6, 'SEMI-301', 'Semi-Private', 3, 160, 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(7, 'SEMI-302', 'Semi-Private', 6, 160, 'available', '2026-09-07 14:32:17', '2026-09-07 14:32:17'),
(8, 'GEN-401', 'General Ward', 6, 80, 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(9, 'GEN-402', 'General Ward', 4, 80, 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(10, 'OT-01', 'Operating Theater', 6, 600, 'available', '2026-09-07 14:32:18', '2026-09-07 14:32:18');

-- --------------------------------------------------------
-- Table structure for table `sessions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (`id` VARCHAR(255) not null, `user_id` BIGINT, `ip_address` VARCHAR(255), `user_agent` text, `payload` text not null, `last_activity` BIGINT not null, primary key (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `staff`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `staff`;
CREATE TABLE `staff` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT not null, `department_id` BIGINT, `job_title` VARCHAR(255) not null, `phone` VARCHAR(255) not null, `hire_date` date, `created_at` DATETIME, `updated_at` DATETIME, foreign key(`user_id`) references `users`(`id`) on delete cascade, foreign key(`department_id`) references `departments`(`id`) on delete set null) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `staff`
INSERT INTO `staff` (`id`, `user_id`, `department_id`, `job_title`, `phone`, `hire_date`, `created_at`, `updated_at`) VALUES
(1, 7, 8, 'Chief Receptionist / Front Desk Officer', '+1 (555) 601-1122', '2024-09-07 00:00:00', '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(2, 8, 1, 'Senior Head Nurse - Inpatient Care', '+1 (555) 602-2233', '2024-09-07 00:00:00', '2026-09-07 14:32:21', '2026-09-07 14:32:21'),
(3, 9, 8, 'Lead Hospital Pharmacist', '+1 (555) 603-3344', '2024-09-07 00:00:00', '2026-09-07 14:32:21', '2026-09-07 14:32:21'),
(4, 10, 7, 'Senior Laboratory Technologist', '+1 (555) 604-4455', '2024-09-07 00:00:00', '2026-09-07 14:32:21', '2026-09-07 14:32:21'),
(5, 11, 8, 'Billing Officer & Cashier', '+1 (555) 605-5566', '2024-09-07 00:00:00', '2026-09-07 14:32:22', '2026-09-07 14:32:22');

-- --------------------------------------------------------
-- Table structure for table `system_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `setting_key` VARCHAR(255) not null, `setting_value` text not null, `description` text, `created_at` DATETIME, `updated_at` DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `system_settings`
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'hospital_name', 'Apex Horizon International Medical Center', 'Hospital Name', '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(2, 'hospital_phone', '+1 (800) 555-APEX / +1 (555) 019-9000', 'Hospital Phone', '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(3, 'hospital_email', 'contact@apexmedical.test', 'Hospital Email', '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(4, 'hospital_address', '500 Health Sciences Blvd, Medical District, Boston MA 02115', 'Hospital Address', '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(5, 'tax_rate_percent', '5.0', 'Tax Rate Percent', '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(6, 'currency_symbol', '$', 'Currency Symbol', '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(7, 'appointment_slot_duration_minutes', '30', 'Appointment Slot Duration Minutes', '2026-09-07 14:32:25', '2026-09-07 14:32:25'),
(8, 'emergency_contact_number', '911 / +1 (555) 911-0000', 'Emergency Contact Number', '2026-09-07 14:32:25', '2026-09-07 14:32:25');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(255), `name` VARCHAR(255) not null, `email` VARCHAR(255) not null, `email_verified_at` DATETIME, `password` VARCHAR(255) not null, `status` VARCHAR(255)  not null default 'active', `remember_token` VARCHAR(255), `created_at` DATETIME, `updated_at` DATETIME) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `username`, `name`, `email`, `email_verified_at`, `password`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'Dr. Alexander Vance (Superadmin)', 'superadmin@hospital.test', NULL, '$2y$12$0Q.bZhRJrjVQDeuQHO938e9dFuM6DgZyd.peR7cEUXTzc2kDhQMU6', 'active', NULL, '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(2, 'admin', 'Elena Rostova (Admin)', 'admin@hospital.test', NULL, '$2y$12$63kUaFNVSoCWAU/hlbNP/.g0bNJQO.v0MCfuyWwHuJUY7GiUOsxFG', 'active', NULL, '2026-09-07 14:32:18', '2026-09-07 14:32:18'),
(3, 'dr_sarah', 'Dr. Sarah Jenkins', 'dr.sarah@hospital.test', NULL, '$2y$12$mrrWgMuIe7O6ABKUcH2uN.zz/srElZZYKLSl2niESyPcNwLK2ZoIS', 'active', NULL, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(4, 'dr_james', 'Dr. James Wilson', 'dr.james@hospital.test', NULL, '$2y$12$jWE1WTGOdpe9vlzWT1D2rOwn5LSSDmnp2GLWtI4XilEg4hBs/ADYi', 'active', NULL, '2026-09-07 14:32:19', '2026-09-07 14:32:19'),
(5, 'dr_emily', 'Dr. Emily Chang', 'dr.emily@hospital.test', NULL, '$2y$12$y3w7EUqJnL42kl/7HQRLDOAOHgT2CljwA9i15xBnlqMFUZXUQgdW2', 'active', NULL, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(6, 'dr_robert', 'Dr. Robert Taylor', 'dr.robert@hospital.test', NULL, '$2y$12$iIkCtQKxmWdTJtcmYqqw.uOov.pmvuPqO3qYDwJsECYERQUdaL5iy', 'active', NULL, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(7, 'receptionist', 'Rachel Adams (Receptionist)', 'receptionist@hospital.test', NULL, '$2y$12$E9gd7QT0Km6vyWEG9CqiGelTkXReZr7HG1ce0IDnM0jT.f1C8JMDK', 'active', NULL, '2026-09-07 14:32:20', '2026-09-07 14:32:20'),
(8, 'nurse', 'Nurse Clara Nightingale', 'nurse@hospital.test', NULL, '$2y$12$57rjlDmEsmcKcdTp1br4M.41xr3D0hfBCc3ZBhqMPQRoKTtJezCqW', 'active', NULL, '2026-09-07 14:32:21', '2026-09-07 14:32:21'),
(9, 'pharmacist', 'Michael Chen (Pharmacist)', 'pharmacist@hospital.test', NULL, '$2y$12$teF.IrKJb.Iu4eoLl4NHE.1e7kgMBo6JMvUjU6rAL/9ske76RKyiK', 'active', NULL, '2026-09-07 14:32:21', '2026-09-07 14:32:21'),
(10, 'labtech', 'David Miller (Lab Tech)', 'labtech@hospital.test', NULL, '$2y$12$n/Cc77B1CfQAwm2SlTQeEOn9vmURYgZfycRA5dHn6Xgc28SKN2.2a', 'active', NULL, '2026-09-07 14:32:21', '2026-09-07 14:32:21'),
(11, 'cashier', 'Sophia Patel (Cashier & Billing)', 'cashier@hospital.test', NULL, '$2y$12$uHqQdQGFMa4Q1JSs0NJSsuuHM8t6H7Rae./UxrzuVhVQTWj/rD5ji', 'active', NULL, '2026-09-07 14:32:21', '2026-09-07 14:32:21'),
(12, 'patient_john', 'Johnathan Doe', 'patient.john@hospital.test', NULL, '$2y$12$SuCm1Wbbvx7iP.wwepYcdufPXqIlp33CjqnfPuSW8t0Ex/nGZjc3K', 'active', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22'),
(13, 'patient_emma', 'Emma Watson', 'patient.emma@hospital.test', NULL, '$2y$12$1OSd6upweCY2bm0z6WMIGOC9m/YTgfTzZ3oQYHz.x2KU/Bd0IF5cK', 'active', NULL, '2026-09-07 14:32:22', '2026-09-07 14:32:22');

SET FOREIGN_KEY_CHECKS = 1;
