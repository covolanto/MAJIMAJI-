# Water Billing System - Specification Document

## 1. Project Overview

**Project Name:** AquaBill - Water Billing System
**Project Type:** Full-stack Web Application
**Core Functionality:** A comprehensive water billing management system with user authentication, bill generation, payment processing, and analytics.
**Target Users:** Water utility administrators and residential/commercial customers

---

## 2. Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Backend:** PHP 8.x
- **Database:** MySQL
- **Charts:** Chart.js
- **Icons:** Font Awesome 6
- **Fonts:** Google Fonts (Poppins, Inter)

---

## 3. UI/UX Specification

### 3.1 Layout Structure

**Login/Register Pages:**

- Centered card layout
- Animated background
- Logo at top

**Admin Dashboard:**

- Sidebar navigation (collapsible on mobile)
- Top header with user info and theme toggle
- Main content area with grid cards
- Footer

**Customer Portal:**

- Simplified sidebar
- Overview cards
- Bill history table
- Payment section

### 3.2 Responsive Breakpoints

- **Mobile:** < 768px (single column, hamburger menu)
- **Tablet:** 768px - 1024px (condensed sidebar)
- **Desktop:** > 1024px (full layout)

### 3.3 Color Palette

**Light Mode:**

- Primary: #0ea5e9 (Sky Blue)
- Secondary: #0284c7
- Accent: #06b6d4 (Cyan)
- Background: #f8fafc
- Surface: #ffffff
- Text Primary: #1e293b
- Text Secondary: #64748b
- Success: #10b981
- Warning: #f59e0b
- Danger: #ef4444

**Dark Mode:**

- Primary: #38bdf8
- Secondary: #0ea5e9
- Accent: #22d3ee
- Background: #0f172a
- Surface: #1e293b
- Text Primary: #f1f5f9
- Text Secondary: #94a3b8
- Success: #34d399
- Warning: #fbbf24
- Danger: #f87171

### 3.4 Typography

- **Headings:** Poppins (600, 700)
- **Body:** Inter (400, 500, 600)
- **Sizes:**
  - H1: 2rem
  - H2: 1.5rem
  - H3: 1.25rem
  - Body: 1rem
  - Small: 0.875rem

### 3.5 Spacing System

- Base unit: 0.25rem (4px)
- xs: 0.25rem, sm: 0.5rem, md: 1rem, lg: 1.5rem, xl: 2rem, 2xl: 3rem

### 3.6 Visual Effects

- Box shadows: 0 4px 6px -1px rgba(0, 0, 0, 0.1)
- Border radius: 0.5rem (cards), 0.375rem (buttons), 0.25rem (inputs)
- Transitions: 0.3s ease for all interactive elements
- Hover effects: slight lift and glow

---

## 4. Database Schema

### 4.1 Users Table

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 4.2 Customers Table

```sql
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    meter_number VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 4.3 Bills Table

```sql
CREATE TABLE bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    billing_month VARCHAR(7) NOT NULL,
    consumption INT NOT NULL COMMENT 'Cubic meters',
    rate DECIMAL(10,2) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
```

### 4.4 Payments Table

```sql
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'online', 'bank_transfer') DEFAULT 'cash',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transaction_id VARCHAR(100),
    status ENUM('completed', 'failed', 'pending') DEFAULT 'completed',
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
);
```

---

## 5. Core Features

### 5.1 Authentication

- Login with email/username and password
- Registration with validation
- Role-based access control
- Session management
- Logout functionality

### 5.2 Admin Dashboard

- Overview statistics (total customers, revenue, pending bills)
- Quick actions panel
- Recent activity feed
- Charts for monthly revenue and consumption

### 5.3 Customer Management (Admin)

- Add/Edit/Delete customers
- Search and filter customers
- View customer details and history
- Export customer data

### 5.4 Billing Module (Admin)

- Generate monthly bills
- Calculate amount based on consumption tiers:
  - 0-10 m³: $2.50/m³
  - 11-30 m³: $3.00/m³
  - 31-50 m³: $3.50/m³
  - 50+ m³: $4.00/m³
- Edit/Delete bills
- Mark bills as paid
- Set due dates

### 5.5 Payment Module

- Record manual payments
- Simulated online payment
- Payment history
- Generate receipts

### 5.6 Customer Portal

- View own bills
- View payment history
- Make payments (simulated)
- Update profile

### 5.7 Reports & Analytics

- Monthly revenue chart
- Outstanding bills summary
- Consumption trends
- Top consumers

### 5.8 Search & Filter

- Search customers by name, meter number, email
- Filter bills by status, month
- Sort by date, amount

---

## 6. Pages Structure

### 6.1 Public Pages

- `index.php` - Landing/Login page
- `register.php` - Registration page

### 6.2 Admin Pages

- `admin/dashboard.php` - Main dashboard
- `admin/customers.php` - Customer management
- `admin/customer-add.php` - Add customer
- `admin/customer-edit.php` - Edit customer
- `admin/bills.php` - Bill management
- `admin/bill-add.php` - Generate bill
- `admin/bill-edit.php` - Edit bill
- `admin/payments.php` - Payment records
- `admin/reports.php` - Analytics & reports
- `admin/settings.php` - System settings

### 6.3 Customer Pages

- `customer/dashboard.php` - Customer dashboard
- `customer/bills.php` - View bills
- `customer/payments.php` - Payment history
- `customer/pay-bill.php` - Make payment
- `customer/profile.php` - Profile management

### 6.4 Components

- `includes/header.php` - Common header
- `includes/sidebar.php` - Navigation sidebar
- `includes/footer.php` - Common footer
- `includes/db.php` - Database connection
- `includes/auth.php` - Authentication functions
- `includes/functions.php` - Utility functions

---

## 7. Acceptance Criteria

### 7.1 Authentication

- [ ] Users can register with valid email and password
- [ ] Users can login with credentials
- [ ] Admin and customer have different dashboard access
- [ ] Sessions are properly managed

### 7.2 Admin Features

- [ ] Can view dashboard with statistics
- [ ] Can add/edit/delete customers
- [ ] Can generate monthly bills
- [ ] Can view and manage payments
- [ ] Can view reports and charts
- [ ] Can search and filter data

### 7.3 Customer Features

- [ ] Can view own bills
- [ ] Can view payment history
- [ ] Can make payments
- [ ] Can update profile

### 7.4 UI/UX

- [ ] Responsive on mobile, tablet, desktop
- [ ] Dark/Light mode toggle works
- [ ] Smooth transitions between themes
- [ ] Professional and consistent design
- [ ] Forms have client-side validation
- [ ] Success/error messages displayed

### 7.5 Security

- [ ] Passwords hashed in database
- [ ] SQL injection prevention
- [ ] XSS prevention
- [ ] Session security
- [ ] Role-based access control

---

## 8. File Structure

```
MAJIMAJI/
├── index.php
├── register.php
├── logout.php
├── SPEC.md
├── admin/
│   ├── dashboard.php
│   ├── customers.php
│   ├── customer-add.php
│   ├── customer-edit.php
│   ├── bills.php
│   ├── bill-add.php
│   ├── bill-edit.php
│   ├── payments.php
│   ├── reports.php
│   └── settings.php
├── customer/
│   ├── dashboard.php
│   ├── bills.php
│   ├── payments.php
│   ├── pay-bill.php
│   └── profile.php
├── includes/
│   ├── db.php
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
├── css/
│   └── style.css
├── js/
│   └── main.js
└── assets/
    └── logo.png
```

---

## 9. Billing Rate Structure

| Consumption (m³) | Rate per m³ |
| ---------------- | ----------- |
| 0 - 10           | $2.50       |
| 11 - 30          | $3.00       |
| 31 - 50          | $3.50       |
| 50+              | $4.00       |

Additional monthly meter charge: $5.00

---

## 10. Default Admin Credentials

- **Email:** admin@aquabill.com
- **Password:** admin123
