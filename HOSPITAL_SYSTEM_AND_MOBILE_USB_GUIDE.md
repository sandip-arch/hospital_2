# Hospital Management System: Technical Enhancements & Mobile USB Tethering Guide

This document provides a comprehensive technical overview of the newly implemented features, architectural updates, and step-by-step instructions for running the hospital management application on a mobile phone connected via **USB Tethering**.

---

## 1. Hospital Branding & Official Logo Management

### Overview
Administrators can now upload an official hospital emblem, logo, or icon directly in **System Settings** (`/admin/settings`). The logo automatically propagates globally across the website and all printed medical/financial records.

### Implementation Details
- **Controller**: [`SystemSettingController.php`](file:///c:/xampp/htdocs/hospital/app/Http/Controllers/Admin/SystemSettingController.php#L22-L51)
  - Added support for file upload `hospital_logo_file` (`max:4096KB`, `mimes:png,jpg,svg,webp`).
  - Automatically creates destination directory `public/uploads/branding/`, generates unique file hashes, and stores the relative asset path in `SystemSetting::set('hospital_logo', ...)`.
  - Also accepts external image URLs or custom icon paths via `hospital_logo`.
  - Added toggle to remove/clear the logo if needed.
- **Global View Sharing**: [`AppServiceProvider.php`](file:///c:/xampp/htdocs/hospital/app/Providers/AppServiceProvider.php#L24-L30)
  - `$hospitalSettings['hospital_logo']` is globally shared with all Blade views via `View::composer('*')`.
- **User Interface Integration**:
  - **System Settings**: [`admin/settings/index.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/admin/settings/index.blade.php#L66-L140) provides live image preview, file selector, URL input, and clear controls.
  - **Sidebar & Portal Header**: [`layouts/app.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/layouts/app.blade.php#L231-L245) displays the uploaded logo inside a modern container.
  - **Public Landing Navbar**: [`layouts/public.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/layouts/public.blade.php#L83-L97) displays the hospital brand identity.
  - **Login & Registration**: [`auth/login.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/auth/login.blade.php#L18-L27) and [`auth/register.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/auth/register.blade.php#L21-L32).
  - **Official Invoices**: [`billing/print.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/billing/print.blade.php#L26-L38) and [`billing/show.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/billing/show.blade.php#L108-L121) feature the hospital logo at the top left, fully optimized for `@media print`.
  - **e-Prescription Slips**: [`prescriptions/print.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/prescriptions/print.blade.php#L27-L40) and [`prescriptions/show.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/prescriptions/show.blade.php#L32-L45) feature the hospital logo in official prescription header slips.

---

## 2. WhatsApp-Style Chat Read Receipts (Double Tick)

### Overview
The internal messaging system (`/messages`) now includes WhatsApp-inspired delivery and read receipts:
- **Single Gray Tick (`✓`)**: Message sent by user and delivered to the server.
- **Double Cyan / Blue Tick (`✓✓`)**: Message opened and read by the recipient (`is_read = true`).

### Implementation Details
- **Model**: [`Message.php`](file:///c:/xampp/htdocs/hospital/app/Models/Message.php) tracks `is_read` boolean and `sent_at` timestamp.
- **Poller Endpoint**: Added `chatPoll(Request $request)` in [`CommunicationController.php`](file:///c:/xampp/htdocs/hospital/app/Http/Controllers/CommunicationController.php#L260-L310) and registered route `GET /messages/chat-poll`:
  - Automatically marks incoming messages from the active contact as `is_read = true`.
  - Returns `read_ids` of messages sent by the user that have been read by the recipient.
  - Streams any incoming real-time messages.
- **Frontend Live Ticks**: [`communication/messages.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/communication/messages.blade.php#L90-L245)
  - Renders `<i class="fa-solid fa-check text-slate-400"></i>` for unread outgoing messages.
  - Updates seamlessly to `<i class="fa-solid fa-check-double text-cyan-500"></i>` when `chatPoll` reports the message has been read.
  - Event-driven updates: checks status when outgoing messages are pending, when chat input is focused, or after sending, avoiding background polling loops.

---

## 3. Refined Pharmacy & Medicine Features

### Capabilities Implemented
1. **Stock Availability Tracking**:
   - Each medicine displays real-time stock badges:
     - `In Stock (X units)` (Green)
     - `Low Stock (X left)` (Amber) when count $\le$ reorder threshold.
     - `Out of Stock (0)` (Red) when count $\le 0$.
   - Out-of-stock items disable the "Buy / Order" button and prevent checkout.
2. **Medicine Purchase & Reservation**:
   - Patients can buy medicines directly from their doctor-prescribed list or the full hospital drug catalog.
   - Dynamic quantity picker with live total price calculation (`$quantity \times unit\_price$`).
   - Decrements stock from `medicines.stock_quantity`.
   - Records an official order in the newly created `medicine_orders` table.
3. **Order Cancellation & Stock Restoration**:
   - Patients and staff can cancel pending orders.
   - Restores the exact purchased quantity back into `medicines.stock_quantity`.
   - Records cancellation timestamp and audit log.
4. **Order History Management**:
   - **Patients**: "My Medicine Orders & Refills History" showing order ID, unit price, total, placed date, and active "Cancel Order" action.
   - **Staff / Admin**: "Pharmacy Refill & Dispense Desk" with status transition dropdown (`pending`, `confirmed`, `dispensed`, `cancelled`).

### Relevant Files
- **Model**: [`MedicineOrder.php`](file:///c:/xampp/htdocs/hospital/app/Models/MedicineOrder.php)
- **Controller**: [`PharmacyController.php`](file:///c:/xampp/htdocs/hospital/app/Http/Controllers/PharmacyController.php#L13-L240) (`buy()`, `cancelOrder()`, `updateOrderStatus()`)
- **View**: [`pharmacy/index.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/pharmacy/index.blade.php)

---

## 4. Ambulance Module Audit & GPS Diagnostics

### Why GPS / Live Location Appeared Broken
During investigation, four root causes were identified:
1. **Browser Insecure Origin Restriction**: Modern browsers (Chrome, Edge, Safari) disable `navigator.geolocation` when accessed over unencrypted HTTP on non-localhost IP addresses (such as `http://192.168.42.x`).
2. **Laptop Hardware GPS Timeouts**: Requesting `enableHighAccuracy: true` on Windows laptops causes timeouts because desktop PCs lack hardware satellite GPS chips.
3. **Unhandled Error Fallback**: When geolocation failed, the script previously left `userLocation` as null, leaving distance pills stuck at `"Calculating distance..."`.
4. **Null Pointer in Live Tracking**: `apiTrack` accessed `$amb->id` directly without checking if an ambulance was assigned to the booking.

### Fixes Applied
- **Graceful Geolocation with Fallback**: In [`ambulance/index.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/ambulance/index.blade.php#L635-L670), standard accuracy is queried first. If blocked or timed out, it falls back to IP geolocation and the Hospital Base Coordinates, ensuring distance pills immediately display exact kilometers and ETAs.
- **Null Safety in Tracking**: In [`AmbulanceController.php`](file:///c:/xampp/htdocs/hospital/app/Http/Controllers/AmbulanceController.php#L549-L565) and [`ambulance/track.blade.php`](file:///c:/xampp/htdocs/hospital/resources/views/ambulance/track.blade.php#L205-L277), added null-safe property access (`$booking->ambulance?->vehicle_number`).
- **Functionality Status Table**:

| Feature | Route | Status | Notes |
| :--- | :--- | :---: | :--- |
| **Ambulance Radar Map** | `GET /ambulance` | **Operational** | Leaflet radar, status filters, device GPS & fallback |
| **Fleet Repositioning** | `POST /ambulance/api/reposition` | **Operational** | Deploys fleet within radius of device/hospital |
| **Ambulance Booking** | `GET /ambulance/book`, `POST /ambulance/book` | **Operational** | Draggable pin, Nominatim reverse geocode |
| **Live Dispatch Tracking** | `GET /ambulance/track/{id}` | **Operational** | Zomato-style 5-step progress, 3.5s live polling |
| **Movement Simulation** | `POST /ambulance/api/simulate-step/{id}` | **Operational** | Increments vehicle position 25% closer per step |
| **Fleet Management (Admin)** | `GET /admin/ambulances` | **Operational** | Register vehicles, assign drivers, sync GPS |
| **Driver Complaints** | `GET /ambulance-complaints` | **Operational** | Maintenance logs, odometer tracking |

---

## 5. Running the Website on a Phone via USB Tethering

### Prerequisites
- Windows PC running XAMPP (Apache & MySQL).
- Smartphone (Android or iPhone) with USB Cable.

---

### Step 1: Enable USB Tethering on Phone
1. Connect phone to laptop using the USB cable.
2. On Android: Open **Settings** $\rightarrow$ **Network & Internet** (or **Connections**) $\rightarrow$ **Hotspot & Tethering** $\rightarrow$ Toggle **USB Tethering** to **ON**.
   *(On iOS: Open **Settings** $\rightarrow$ **Personal Hotspot** $\rightarrow$ Toggle **Allow Others to Join**, choose **USB Only**).*
3. Windows will recognize a new network adapter (e.g. *Remote NDIS based Internet Sharing Device*).

---

### Step 2: Find Your Laptop's USB Tethering IP Address
1. Open PowerShell or Command Prompt on your laptop.
2. Run:
   ```cmd
   ipconfig
   ```
3. Locate the adapter for the tethered connection (typically named **Ethernet 2**, **Ethernet 3**, or **Local Area Connection**).
4. Look for the **IPv4 Address**:
   - For Android USB tethering, this is usually: **`192.168.42.xxx`** (e.g. `192.168.42.129`).

---

### Step 3: Allow Incoming Traffic Through Windows Firewall
Windows Firewall often blocks incoming port 80/8000 requests from other devices. Run this one-time command in PowerShell as **Administrator**:
```powershell
New-NetFirewallRule -DisplayName "Laravel Phone Tethering (Port 8000)" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "XAMPP Apache Phone Tethering (Port 80)" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow
```

---

### Step 4: Start the Server for Network Access
You have two options:

#### Option A: Quick One-Click Host (Recommended)
Double-click the newly generated script in the project root:
```
c:\xampp\htdocs\hospital\serve_mobile.bat
```
This automatically displays your active IP addresses and starts `php artisan serve --host=0.0.0.0 --port=8000`.

#### Option B: Standard XAMPP Apache
If XAMPP Apache is running:
- Open `c:\xampp\apache\conf\httpd.conf` and confirm line `Listen 80` (or `Listen 0.0.0.0:80`) is present.
- Apache is already listening on all network interfaces.

---

### Step 5: Open on Your Phone's Browser
1. Open Chrome or Safari on your phone.
2. In the address bar, type:
   - If using **Option A (Port 8000)**:
     ```
     http://192.168.42.xxx:8000
     ```
   - If using **Option B (XAMPP Apache)**:
     ```
     http://192.168.42.xxx/hospital/public
     ```
   *(Replace `192.168.42.xxx` with the IPv4 address from Step 2).*
3. The complete hospital website will render with full mobile responsiveness, hamburger menu drawers, touch-optimized forms, and live polling features!

