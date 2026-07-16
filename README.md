---

# 🩸 BloodDonation - Automated Blood Management & Appointment System

A robust, framework-less full-stack web application designed to bridge the critical gap between blood donors, hospitals, and system administrators. The platform automates the entire lifecycle of blood donation, from finding available blood stocks to booking, tracking, and managing donation appointments through secure custom-built APIs.

---

## 🚀 Features & Core Workflows

### 🏥 Hospital Portal

* **Inventory Management:** Allows medical institutions to view, track, and update live blood group availability.
* **Appointment Processing:** Hospitals can approve, reschedule, or complete donation appointments initiated by registered donors.

### 🩸 Donor Portal

* **Profile & Health Tracking:** Donors can manage their demographic profile and track their eligibility criteria (such as the 90-day donation interval).
* **Interactive Appointment Booking:** Seamlessly book, view, and cancel donation slots at preferred hospitals, receiving instant feedback.

### 🛡️ Admin Dashboard & Governance

* **Centralized Oversight:** Full CRUD control over registered hospitals, donors, and system logs.
* **System Integrity:** Secure cascade deletions to clean up related transactions (e.g., deleting a hospital automatically archives or deletes its associated pending appointments).

---

## 🧱 Technical Architecture

| Layer | Technology |
| --- | --- |
| **Backend** | PHP 7+ (Clean Procedural & API-Driven Architecture) |
| **Database** | MySQL with PDO (PHP Data Objects) + Prepared Statements |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript, Bootstrap |
| **Asynchronous Logic** | Fetch API (AJAX) for frictionless interaction |

---

## 🔒 Security & Database Highlights

* **PDO Prepared Statements:** Absolute protection against SQL injection attacks by ensuring zero raw query concatenation.
* **API-Driven Segregation:** Separate, dedicated endpoint controllers (`donor_api.php`, `hospital_api.php`, `admin_api.php`) ensuring modular backend routing.
* **Secure Transaction Handling:** SQL transaction parameters and error boundaries (try/catch blocks) ensuring atomic updates in case of appointment cancellations or registration rollbacks.

---

## 📁 Project Structure

```
Blooddonation/
├── api/
│   ├── admin_api.php              # Administrative management endpoints
│   ├── donor_api.php              # Donor actions (book/cancel appointments)
│   └── hospital_api.php           # Hospital inventory & validation endpoints
├── db/
│   ├── blooddb.sql                # Complete database relational schema
│   └── fake_data.sql              # Mock database data for local testing
├── admin_dashboard.php            # Administrative control center
├── donor_dashboard.php            # Donor profile & booking UI
├── hospital_dashboard.php         # Hospital inventory & action board
├── db.php                         # Database connection establishment (PDO)
├── index.php                      # Main landing page
├── login.php / logout.php         # Secure session handlers
└── register.php                   # Split registration logic (Donor/Hospital)

```

---

## ⚙️ Quick Local Setup

1. **Clone the repository:**
```bash
git clone https://github.com/Ayafarhatt/Blooddonation.git

```


2. Place the folder inside your local server root directory (e.g., htdocs/ for XAMPP).
3. Import the database schema db/blooddb.sql into your MySQL server (via phpMyAdmin or CLI).
4. Configure database credentials in db.php.
5. Access the app via http://localhost/Blooddonation/ in your browser.

---
