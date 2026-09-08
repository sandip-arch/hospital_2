# Rishi's Updates & Activity Log

This document tracks all changes, configurations, installations, and development updates made to the **Hospital Management System** (`php-hospital`) project.

---

## Project Overview
- **Project Path:** `c:\xampp\htdocs\php-hospital`
- **Repository:** `https://github.com/sandip-arch/hospital_2.git`
- **Stack:** PHP 8.2.12, Laravel 12.69.0, MySQL (MariaDB 10.4.32), Node v24.16.0, Vite 7.3.6, TailwindCSS v4

---

## Log of Completed Work

### 1. Repository Setup & Clone
- **Action:** Cloned the remote Git repository into the project directory.
- **Command:** `git clone https://github.com/sandip-arch/hospital_2.git .`
- **Target Folder:** `c:\xampp\htdocs\php-hospital`
- **Result:** Successfully pulled all application source files, documentation, migrations, seeders, and SQL dump.

---

### 2. Environment Configuration & App Key
- **Action:** Created the environment file from `.env.example`.
- **Command:** `Copy-Item .env.example .env`
- **Key Generation:** Generated unique Laravel encryption key via `php artisan key:generate`.
- **Result:** `APP_KEY` set and `.env` established for local configuration.

---

### 3. PHP Dependency Installation
- **Action:** Installed all backend PHP dependencies via Composer.
- **Command:** `composer install`
- **Result:** All 101 packages (Laravel 12 framework, Tinker, Pail, Sail, Carbon, etc.) installed and autoload files generated.

---

### 4. Frontend Asset Installation & Build
- **Action:** Installed Node dependencies and compiled frontend assets using Vite and Tailwind CSS.
- **Commands:**
  - `npm install`
  - `npm run build`
- **Result:** Bundled `public/build/assets/app-Cqh9rml-.css` and `public/build/assets/app-DMsN-rLE.js`.

---

### 5. Storage Symlink
- **Action:** Linked public storage to application storage.
- **Command:** `php artisan storage:link`
- **Result:** Connected `public/storage` to `storage/app/public`.

---

### 6. MySQL Database Configuration
- **Action:** Configured MySQL database credentials in both `.env` and `.env.example`.
- **Database Name:** `hospital` (imported from `hospital.sql` in phpMyAdmin)
- **Configuration Details:**
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=hospital
  DB_USERNAME=root
  DB_PASSWORD=
  ```
- **Files Modified:**
  - `.env`
  - `.env.example`

---

### 7. Database Migration Status Check
- **Action:** Verified database connection and migration status.
- **Command:** `php artisan migrate:status`
- **Result:** Successfully connected to the MySQL `hospital` database. All 11 migrations confirmed as `[1] Ran`:
  1. `0001_01_01_000000_create_users_table`
  2. `0001_01_01_000001_create_cache_table`
  3. `0001_01_01_000002_create_jobs_table`
  4. `2026_09_01_000001_create_roles_and_permissions_tables`
  5. `2026_09_01_000002_create_hospital_organization_tables`
  6. `2026_09_01_000003_create_patients_and_emergency_tables`
  7. `2026_09_01_000004_create_appointments_and_availabilities_tables`
  8. `2026_09_01_000005_create_medical_records_and_pharmacy_tables`
  9. `2026_09_01_000006_create_facilities_and_admissions_tables`
  10. `2026_09_01_000007_create_billing_and_invoices_tables`
  11. `2026_09_01_000008_create_communication_and_system_tables`

---

### 8. Web Server Launch
- **Action:** Started the local Laravel development server.
- **Command:** `php artisan serve`
- **Address:** `http://127.0.0.1:8000`
- **Verification:** Verified with `curl.exe -I http://127.0.0.1:8000`, returned `HTTP/1.1 200 OK`.

---

### 9. Documentation Log Initialization
- **Action:** Created `RIshi's updates.md` to document all ongoing modifications, tasks, and system state.

---

### 10. Web Server Stopped
- **Action:** Terminated the background `php artisan serve` development server process.
- **Port Status:** Port 8000 released.

---

### 11. Bed Discharge Architecture & Logic Analysis
- **Action:** Conducted deep-dive code analysis on the inpatient bed discharge workflow.
- **Key Modules Analyzed:**
  - Route: `POST /facilities/discharge/{id}` in [routes/web.php](file:///c:/xampp/htdocs/php-hospital/routes/web.php)
  - Controller: `FacilityController::discharge()` and `updateBedStatus()` in [app/Http/Controllers/FacilityController.php](file:///c:/xampp/htdocs/php-hospital/app/Http/Controllers/FacilityController.php)
  - Models: `Admission`, `Bed`, `Room`, `User`
  - Automated Billing: `BillingService::createForAdmission()`
  - UI / UX: [resources/views/facilities/bed-tracker.blade.php](file:///c:/xampp/htdocs/php-hospital/resources/views/facilities/bed-tracker.blade.php)
- **Summary:** Documented role permissions (who can discharge), bed lifecycle transitions (`occupied` -> `cleaning` -> `available`), automatic itemized invoice generation, and UI flow.

### 12. Resolved Bed Discharge Bug & Full Workflow Verification
- **Root Cause Identified:**
  1. **Frontend / Alpine Scope Breakage**: In `bed-tracker.blade.php`, stray duplicate closing tags (`</form></div></div>`) prematurely terminated the outer `<div x-data="...">` Alpine component scope after Modal 1. This caused Modal 2 (`dischargeModal`) to be parsed outside of Alpine's reactive scope, triggering JavaScript fatal errors (`dischargeModal is not defined`) and preventing the discharge modal from rendering when clicking "Discharge".
  2. **Controller Resolution & Graceful Failure**: `FacilityController@discharge` assumed `$id` was always a valid active admission ID without handling Bed ID fallbacks, beds held occupied without admission records, or already-discharged admission guards (preventing bed corruption on invalid/duplicate discharge).
  3. **Admissions Registry Action**: Added a direct Discharge action in `admissions.blade.php` to allow discharging admitted patients directly from the Admissions table.
- **Files Modified:**
  - `resources/views/facilities/bed-tracker.blade.php`: Removed stray closing tags, added `type="button"` to discharge trigger, added direct release option for occupied beds without linked admission records.
  - `app/Http/Controllers/FacilityController.php`: Enhanced `discharge()` method to resolve both Admission ID and Bed ID, added guard against re-discharging already completed admissions, and added direct release of occupied beds to cleaning.
  - `app/Models/Bed.php`: Added `latestOfMany()` to `currentAdmission` relationship to ensure deterministic active admission lookup.
  - `resources/views/facilities/admissions.blade.php`: Added an "Action" column with a Discharge button for active admitted inpatients.
- **Database Schema Changed:** **NO** (Strictly zero database schema or table changes).
- **Test Suite Results:**
  - Admission -> Occupied: **PASS**
  - Occupied -> Discharge: **PASS**
  - Admission -> Discharged: **PASS**
  - Bed -> Cleaning: **PASS**
  - Other Occupied Beds Unaffected: **PASS**
  - Invalid Discharge (Already Discharged) Guard: **PASS**
  - Authorization Check (Patient 403 Forbidden): **PASS**
  - End-to-End Browser UI Flow: **PASS**

---

### 13. Fine-Grained Role Authorization for Bed Discharge
- **Requirement Implemented:** Only Superadmin, Hospital Admin, the specific assigned attending Doctor, or a Receptionist (Front Desk) can discharge a patient or release a bed.
- **Rules Enforced:**
  - **Superadmin & Admin:** Full authority to discharge any inpatient admission or release any held bed.
  - **Assigned Doctor:** Can discharge inpatients admitted under their care (`$user->doctor->id === $admission->doctor_id`). Blocked from discharging patients assigned to other doctors.
  - **Receptionist:** Staff with Front Desk / Receptionist job titles can discharge any patient.
  - **Unauthorized Users (Other Doctors, Nurses, Pharmacists, Lab Techs, Cashiers, Patients):** Backend returns HTTP `403 Forbidden`; UI hides the discharge button and displays the assigned doctor's name instead.
- **Files Modified:**
  - `app/Models/User.php`: Added `isReceptionist()`, `canDischargeAdmission()`, and `canReleaseBed()` authorization helpers.
  - `app/Http/Controllers/FacilityController.php`: Added strict role-based verification in `discharge()`.
  - `resources/views/facilities/bed-tracker.blade.php`: Added conditional UI rendering for Discharge and Release buttons.
  - `resources/views/facilities/admissions.blade.php`: Restricted table inline Discharge action to authorized users.
- **Database Schema Changed:** **NO** (Strictly zero database schema or table changes).
- **Verification Results:**
  - Superadmin can discharge: **PASS**
  - Hospital Admin can discharge: **PASS**
  - Receptionist can discharge: **PASS**
  - Assigned Doctor can discharge own patient: **PASS**
  - Other Doctor blocked (HTTP 403 / button hidden): **PASS**
  - Nurse blocked (HTTP 403 / button hidden): **PASS**
  - Pharmacist blocked (HTTP 403 / button hidden): **PASS**
  - Patient blocked (HTTP 403 / button hidden): **PASS**
  - Browser UI Verification: **PASS**

---

### 14. Interactive Notification Click-to-Chat Navigation
- **Requirement Implemented:** When a user receives a message notification, clicking that notification in the dropdown or notification center should automatically open the specific chat page with the sender, and automatically mark the notification as read.
- **Root Cause of Previous Static Behavior:** Notifications in the dropdown and list were rendered as plain static `<div>` containers without `<a>` tags, click actions, or target URLs.
- **Solution Implemented:**
  - Added `target_url` accessor on the `Notification` model: parses message sender information and dynamically generates the conversation link (`/messages?user_id={sender_id}`), as well as appropriate links for appointments, lab results, and billing.
  - Added a dedicated click-handler route: `GET /notifications/{id}/open` in `CommunicationController@openNotification` which marks the notification as read (`is_read = true`) and redirects the user directly to the target conversation or module.
  - Converted static notification cards in [resources/views/layouts/app.blade.php](file:///c:/xampp/htdocs/php-hospital/resources/views/layouts/app.blade.php) and [resources/views/communication/notifications.blade.php](file:///c:/xampp/htdocs/php-hospital/resources/views/communication/notifications.blade.php) into interactive links.
- **Database Schema Changed:** **NO** (Strictly zero database schema or table changes).
- **Verification Results:**
  - Clicked "New Internal Message" from Rishi Shaw in Dr. Sarah's notifications dropdown: **PASS**
  - Successfully redirected directly to `http://127.0.0.1:8000/messages?user_id=14`: **PASS**
  - Active conversation with message history loaded on chat screen: **PASS**
  - Unread notification counter automatically decremented: **PASS**

---

### 15. Notification Dropdown Unread Filtering (Auto-Removal of Clicked Notifications)
- **Issue Reported:** When clicking a specific notification from the top bell dropdown, the user was redirected to the target conversation and the unread count decremented as expected, but the clicked notification still appeared in the dropdown list upon page refresh.
- **Root Cause:** The dropdown loop in [resources/views/layouts/app.blade.php](file:///c:/xampp/htdocs/php-hospital/resources/views/layouts/app.blade.php) originally queried `Auth::user()->notifications()->take(6)->get()`, which fetched the latest 6 notifications regardless of whether their `is_read` status was `true` or `false`.
- **Solution Implemented:**
  - Updated the dropdown loop in `app.blade.php` to filter by unread notifications: `Auth::user()->notifications()->where('is_read', false)->latest()->take(6)->get()`.
  - Once a notification is clicked, `openNotification()` sets `is_read = true`.
  - When the page loads or refreshes, the read notification is immediately excluded from the bell dropdown.
  - Added an empty state: displays *"No unread notifications"* when all notifications are read, and conditionally hides the "Mark all read" button.
  - Historical notifications (both read and unread) remain accessible at any time via *"View All Notifications &rarr;"* (`/notifications`).
- **Database Schema Changed:** **NO** (Strictly zero database schema or table changes).
- **Verification Results:**
  - Browser inspection of bell dropdown on Dashboard: **PASS**
  - Read notifications excluded from dropdown: **PASS**
  - Only active unread notifications displayed with accurate badge count: **PASS**

---

## Current Status
- **Web App Status:** Running on `http://127.0.0.1:8000`
- **Database Status:** Connected to MySQL (`hospital` database)
- **Bed Management & Discharge Authorization:** Fully Enforced, Functional & Tested
- **Notification Navigation & Dropdown Filtering:** Active, Tested & Verified
- **Quick Demo Accounts Configured:**
  - **Superadmin:** `superadmin@hospital.test`
  - **Admin:** `admin@hospital.test`
  - **Doctor (Attending):** `dr.sarah@hospital.test`
  - **Receptionist:** `receptionist@hospital.test`




