# CuttingMaster - Comprehensive Application Documentation

**Version:** 2025
**Last Updated:** January 1, 2026
**Application Type:** PHP Web Application
**Primary Purpose:** Bespoke Women's Pattern Making & Tailoring Platform

---

## Table of Contents

1. [Overview](#1-overview)
2. [Technology Stack](#2-technology-stack)
3. [Project Structure](#3-project-structure)
4. [Database Schema](#4-database-schema)
5. [Configuration](#5-configuration)
6. [User Types & Authentication](#6-user-types--authentication)
7. [Pages & Functionality](#7-pages--functionality)
8. [Pattern Generation System](#8-pattern-generation-system)
9. [CSS Design System](#9-css-design-system)
10. [Shared Components](#10-shared-components)
11. [Business Logic](#11-business-logic)
12. [Session Management](#12-session-management)
13. [Security Considerations](#13-security-considerations)
14. [Deployment](#14-deployment)

---

## 1. Overview

CuttingMaster is a comprehensive web platform for:
- **Pattern Making**: Generating SVG-based tailoring patterns from body measurements
- **Measurement Management**: Storing and managing body measurements for individuals and boutique customers
- **Wholesale Marketplace**: Connecting wholesalers with retailers and boutiques
- **Portfolio Showcase**: Displaying pattern designs, tailoring work, and wholesale products

### Core Features
- Multi-user system with 4 user types + admin
- Real-time SVG pattern generation for saree blouses
- Customer management for boutique owners
- Wholesale product catalog with variants
- Admin impersonation (mimic) functionality
- Anonymous public measurement collection
- Contact enquiry management

---

## 2. Technology Stack

### Backend
- **Language:** PHP 7.4+
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Database Abstraction:** PDO (PHP Data Objects)
- **Session Management:** PHP Sessions

### Frontend
- **HTML5** with semantic markup
- **CSS3** with Flexbox and Grid layouts
- **JavaScript** (ES6+) for interactivity
- **Icons:** Lucide Icons (https://lucide.dev)

### Fonts
- **Headings:** Cormorant Garamond (Google Fonts)
- **Body:** Libre Franklin (Google Fonts)
- **Forms:** Roboto (Google Fonts)

### Libraries
- **PHPMailer** for email sending (SMTP via Gmail)
- **Lucide** for icon rendering

---

## 3. Project Structure

```
CM-2025/
├── index.php                    # Homepage with portfolio sections
├── test_connection.php          # Database connection tester
│
├── config/
│   ├── database.php            # MySQL PDO connection
│   ├── auth.php                # Authentication functions
│   └── email.php               # PHPMailer configuration
│
├── css/
│   ├── styles.css              # Main website styles (71KB)
│   └── admin-styles.css        # Admin panel styles (16KB)
│
├── database/
│   ├── schema.sql              # Users table
│   ├── measurements_schema.sql # Measurements table
│   ├── create_customers.sql    # Customers table (boutique)
│   ├── create_admin_table.sql  # Admin users table
│   ├── create_public_measurements.sql  # Public measurements
│   ├── setup_boutique_tables.sql       # Complete boutique setup
│   ├── update_customer_names_titlecase.php  # Data migration script
│   └── migrations/
│       ├── add_women_measurements_columns.sql
│       ├── fix_not_null_columns.sql
│       ├── rename_columns_to_match_form_fields.sql
│       ├── fix_public_measurements_nullable.sql
│       ├── add_women_fields_to_public_measurements.sql
│       └── add_customer_id_to_measurements.sql
│
├── images/
│   ├── logo.png                # Main logo
│   ├── logo2.png               # Alternative logo
│   ├── main-image2.png         # Hero section image
│   ├── blouse_diagram.PNG      # Measurement diagram
│   ├── women_diagram.PNG       # Women measurement guide
│   └── premium-silk-collection*.png  # Product images
│
├── includes/
│   ├── header.php              # Main page header/navigation
│   ├── footer.php              # Page footer with scripts
│   ├── admin-header.php        # Admin panel navigation
│   ├── mimic-banner.php        # Admin impersonation banner
│   └── README.md               # Component documentation
│
├── pages/
│   ├── login.php               # User authentication
│   ├── register.php            # User registration
│   ├── logout.php              # Session termination
│   ├── forgot-password.php     # Password reset
│   │
│   ├── dashboard-individual.php    # Individual user dashboard
│   ├── dashboard-boutique.php      # Boutique owner dashboard
│   ├── dashboard-wholesaler.php    # Wholesaler dashboard
│   ├── dashboard-pattern-provider.php  # Pattern provider dashboard
│   ├── dashboard-admin.php         # Admin control panel
│   │
│   ├── pattern-studio.php      # Measurement capture & pattern display
│   ├── tailoring.php           # Tailoring services showcase
│   ├── wholesale-catalog.php   # Product catalog listing
│   ├── wholesale-product.php   # Single product details
│   ├── contact-us.php          # Contact form
│   ├── about.php               # About page
│   │
│   ├── user-details.php        # Admin user editing
│   ├── saved-measurements.php  # Admin measurements view
│   ├── public-measurements.php # Admin public data view
│   ├── admin-enquiries.php     # Contact form management
│   ├── admin-pattern-portfolio.php     # Pattern portfolio management
│   ├── admin-tailoring-portfolio.php   # Tailoring portfolio management
│   ├── admin-wholesale-portfolio.php   # Wholesale product management
│   ├── admin-wholesale-variants.php    # Product variants management
│   │
│   ├── mimic-user.php          # Admin user impersonation
│   ├── exit-mimic.php          # Return from impersonation
│   │
│   └── pattern-studio/
│       ├── css/
│       │   ├── style.css
│       │   └── menustyle.css
│       ├── img/
│       │   └── blouse_diagram.PNG
│       ├── inc/
│       │   ├── header2.php
│       │   └── deepNeckCV.php      # Chest vertical calculations
│       └── savi/
│           ├── savi.php            # Saree blouse entry point
│           ├── saviMeasure.php     # Measurement input form
│           ├── saviComplete.php    # All patterns assembled
│           ├── saviFront.php       # Front pattern generation
│           ├── saviBack.php        # Back pattern generation
│           ├── saviSleeve.php      # Sleeve pattern generation
│           ├── saviPatti.php       # Waist band pattern
│           └── *Download.php       # Pattern download handlers
│
├── uploads/
│   ├── patterns/               # Pattern portfolio images
│   ├── tailoring/              # Tailoring portfolio images
│   ├── wholesale/              # Wholesale product images
│   └── variants/               # Product variant images
│
└── vendor/
    └── phpmailer/phpmailer/    # PHPMailer library
```

---

## 4. Database Schema

### Database Configuration
- **Database Name:** `cm`
- **Character Set:** utf8mb4
- **Collation:** utf8mb4_unicode_ci
- **Engine:** InnoDB (with foreign key support)

### 4.1 Users Table

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('individual', 'boutique', 'pattern_provider', 'wholesaler') NOT NULL,
    business_name VARCHAR(255) DEFAULT NULL,
    business_location VARCHAR(255) DEFAULT NULL,
    mobile_number VARCHAR(20) DEFAULT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,

    INDEX idx_user_type (user_type),
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.2 Measurements Table

```sql
CREATE TABLE measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_id INT DEFAULT NULL,
    measurement_of ENUM('self', 'customer') NOT NULL,
    category ENUM('women', 'men', 'boy', 'girl') NOT NULL,
    customer_name VARCHAR(255) DEFAULT NULL,

    -- Standard Body Measurements (in inches)
    bust DECIMAL(5,2) DEFAULT NULL,
    waist DECIMAL(5,2) DEFAULT NULL,
    hips DECIMAL(5,2) DEFAULT NULL,
    shoulder_width DECIMAL(5,2) DEFAULT NULL,
    sleeve_length DECIMAL(5,2) DEFAULT NULL,
    arm_circumference DECIMAL(5,2) DEFAULT NULL,
    inseam DECIMAL(5,2) DEFAULT NULL,
    thigh_circumference DECIMAL(5,2) DEFAULT NULL,
    neck_circumference DECIMAL(5,2) DEFAULT NULL,
    height DECIMAL(5,2) DEFAULT NULL,

    -- Women-Specific (Saree Blouse) Measurements
    blength DECIMAL(5,2) DEFAULT NULL,      -- Field 1: Blouse Back Length
    fshoulder DECIMAL(5,2) DEFAULT NULL,    -- Field 2: Full Shoulder
    shoulder DECIMAL(5,2) DEFAULT NULL,     -- Field 3: Shoulder Strap
    bnDepth DECIMAL(5,2) DEFAULT NULL,      -- Field 4: Back Neck Depth
    fndepth DECIMAL(5,2) DEFAULT NULL,      -- Field 5: Front Neck Depth
    apex DECIMAL(5,2) DEFAULT NULL,         -- Field 6: Shoulder to Apex
    flength DECIMAL(5,2) DEFAULT NULL,      -- Field 7: Front Length
    chest DECIMAL(5,2) DEFAULT NULL,        -- Field 8: Upper Chest
    slength DECIMAL(5,2) DEFAULT NULL,      -- Field 11: Sleeve Length
    saround DECIMAL(5,2) DEFAULT NULL,      -- Field 12: Arm Round
    sopen DECIMAL(5,2) DEFAULT NULL,        -- Field 13: Sleeve End Round
    armhole DECIMAL(5,2) DEFAULT NULL,      -- Field 14: Armhole

    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,

    INDEX idx_user_id (user_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_measurement_of (measurement_of),
    INDEX idx_category (category),
    INDEX idx_created_at (created_at),
    INDEX idx_user_customer (user_id, customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.3 Customers Table (Boutique)

```sql
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boutique_user_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_reference VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (boutique_user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_boutique_user (boutique_user_id),
    INDEX idx_customer_name (customer_name),
    INDEX idx_customer_reference (customer_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.4 Public Measurements Table

```sql
CREATE TABLE public_measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('women', 'men', 'boy', 'girl') NOT NULL,

    -- Required Base Measurements
    bust DECIMAL(5,2) NOT NULL,
    waist DECIMAL(5,2) NOT NULL,
    hips DECIMAL(5,2) DEFAULT NULL,
    height DECIMAL(5,2) DEFAULT NULL,

    -- Optional Measurements
    shoulder_width DECIMAL(5,2) DEFAULT NULL,
    sleeve_length DECIMAL(5,2) DEFAULT NULL,
    arm_circumference DECIMAL(5,2) DEFAULT NULL,
    inseam DECIMAL(5,2) DEFAULT NULL,
    thigh_circumference DECIMAL(5,2) DEFAULT NULL,
    neck_circumference DECIMAL(5,2) DEFAULT NULL,

    -- Women-Specific Fields
    blength DECIMAL(5,2) DEFAULT NULL,
    fshoulder DECIMAL(5,2) DEFAULT NULL,
    shoulder DECIMAL(5,2) DEFAULT NULL,
    bnDepth DECIMAL(5,2) DEFAULT NULL,
    fndepth DECIMAL(5,2) DEFAULT NULL,
    apex DECIMAL(5,2) DEFAULT NULL,
    flength DECIMAL(5,2) DEFAULT NULL,
    chest DECIMAL(5,2) DEFAULT NULL,
    slength DECIMAL(5,2) DEFAULT NULL,
    saround DECIMAL(5,2) DEFAULT NULL,
    sopen DECIMAL(5,2) DEFAULT NULL,
    armhole DECIMAL(5,2) DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_category (category),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.5 Admin Users Table

```sql
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.6 Portfolio Tables

```sql
-- Pattern Making Portfolio
CREATE TABLE pattern_making_portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(500),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tailoring Portfolio
CREATE TABLE tailoring_portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    image VARCHAR(500),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Wholesale Portfolio
CREATE TABLE wholesale_portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2),
    image VARCHAR(500),
    vendor_name VARCHAR(255),
    vendor_mobile VARCHAR(20),
    vendor_company VARCHAR(255),
    vendor_location VARCHAR(255),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Wholesale Variants
CREATE TABLE wholesale_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    color_name VARCHAR(100),
    color_image VARCHAR(500),
    size VARCHAR(50),
    price DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES wholesale_portfolio(id) ON DELETE CASCADE
);
```

### 4.7 Enquiries Table

```sql
CREATE TABLE enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mobile VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Database Relationships Diagram

```
users (1) ──────< (N) measurements
   │                    │
   │                    │ (customer_id)
   │                    ▼
   └──────< (N) customers (1) ───< (N) measurements

users ──< pattern_making_portfolio (via provider_id)
users ──< wholesale_portfolio (via vendor_id)

wholesale_portfolio (1) ──< (N) wholesale_variants
```

---

## 5. Configuration

### 5.1 Database Configuration (`config/database.php`)

```php
<?php
$host = 'localhost';
$dbname = 'cm';
$username = 'root';
$password = 'YOUR_PASSWORD';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
```

### 5.2 Authentication Configuration (`config/auth.php`)

Key functions:
- `registerUser($data)` - Creates new user with password hashing
- `loginUser($email, $password)` - Authenticates and creates session
- `isLoggedIn()` - Checks session for user_id
- `requireLogin()` - Redirects if not authenticated
- `getCurrentUser()` - Fetches full user data from database
- `logout()` - Destroys session and redirects

### 5.3 Email Configuration (`config/email.php`)

```php
<?php
// Gmail SMTP Settings
$email_config = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'security' => 'tls',
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',  // Gmail App Password
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'CuttingMaster'
];
```

---

## 6. User Types & Authentication

### 6.1 User Types

| Type | Purpose | Features |
|------|---------|----------|
| `individual` | Personal use | Self-measurements, pattern generation |
| `boutique` | Tailor/boutique owner | Customer management, multiple measurements |
| `pattern_provider` | Pattern designer | Upload/sell patterns (future) |
| `wholesaler` | Bulk seller | Product catalog, variants management |

### 6.2 Account Status

| Status | Description |
|--------|-------------|
| `active` | Can login and use all features |
| `inactive` | Account disabled, cannot login |
| `suspended` | Temporarily blocked |

### 6.3 Admin System

- Separate `admin_users` table
- Admin session uses `$_SESSION['is_admin']`
- Can impersonate regular users via mimic feature
- Full CRUD on all user data

### 6.4 Session Variables

```php
// Regular User Session
$_SESSION['user_id']      // User ID from users table
$_SESSION['user_type']    // individual/boutique/etc.
$_SESSION['username']     // Display name
$_SESSION['email']        // Email address

// Admin Session
$_SESSION['is_admin']     // Boolean: true for admin
$_SESSION['admin_id']     // Admin user ID

// Mimic (Impersonation) Session
$_SESSION['is_mimicking']      // Boolean: currently impersonating
$_SESSION['mimicked_user_id']  // ID of impersonated user
$_SESSION['mimicked_username'] // Username of impersonated user
$_SESSION['original_admin']    // Saved admin session data
```

---

## 7. Pages & Functionality

### 7.1 Public Pages

| Page | URL | Purpose |
|------|-----|---------|
| Homepage | `/index.php` | Landing page with portfolio |
| Login | `/pages/login.php` | User authentication |
| Register | `/pages/register.php` | Account creation |
| Forgot Password | `/pages/forgot-password.php` | Password reset |
| Contact Us | `/pages/contact-us.php` | Enquiry form |
| About | `/pages/about.php` | Information page |
| Tailoring | `/pages/tailoring.php` | Services showcase |
| Wholesale Catalog | `/pages/wholesale-catalog.php` | Product listing |
| Product Details | `/pages/wholesale-product.php?id=X` | Single product |

### 7.2 User Dashboards

| Dashboard | URL | User Type |
|-----------|-----|-----------|
| Individual | `/pages/dashboard-individual.php` | individual |
| Boutique | `/pages/dashboard-boutique.php` | boutique |
| Wholesaler | `/pages/dashboard-wholesaler.php` | wholesaler |
| Pattern Provider | `/pages/dashboard-pattern-provider.php` | pattern_provider |

### 7.3 Pattern Studio

| Page | URL | Purpose |
|------|-----|---------|
| Main Studio | `/pages/pattern-studio.php` | Measurement capture |
| Savi Blouse | `/pages/pattern-studio/savi/savi.php` | Blouse pattern system |

### 7.4 Admin Pages

| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/pages/dashboard-admin.php` | User management, stats |
| User Details | `/pages/user-details.php?id=X` | Edit user |
| Saved Measurements | `/pages/saved-measurements.php` | View all measurements |
| Public Measurements | `/pages/public-measurements.php` | Anonymous data |
| Enquiries | `/pages/admin-enquiries.php` | Contact submissions |
| Pattern Portfolio | `/pages/admin-pattern-portfolio.php` | Pattern images |
| Tailoring Portfolio | `/pages/admin-tailoring-portfolio.php` | Tailoring images |
| Wholesale Portfolio | `/pages/admin-wholesale-portfolio.php` | Products |
| Wholesale Variants | `/pages/admin-wholesale-variants.php?product_id=X` | Variants |
| Mimic User | `/pages/mimic-user.php?id=X` | Impersonate user |
| Exit Mimic | `/pages/exit-mimic.php` | Return to admin |

---

## 8. Pattern Generation System

### 8.1 System Overview

The Savi Blouse Pattern Designer generates SVG patterns from 14 body measurements. Located in `/pages/pattern-studio/savi/`.

### 8.2 Measurement Fields

| # | Field | Description | Range (inches) |
|---|-------|-------------|----------------|
| 1 | `blength` | Blouse Back Length | 10-18 |
| 2 | `fshoulder` | Full Shoulder | 10-17 |
| 3 | `shoulder` | Shoulder Strap | 1-5 |
| 4 | `bnDepth` | Back Neck Depth | 1+ |
| 5 | `fndepth` | Front Neck Depth | - |
| 6 | `apex` | Shoulder to Apex | 6-13 |
| 7 | `flength` | Front Length | < blength - 1.5 |
| 8 | `chest` | Upper Chest | 26-44 |
| 9 | `bust` | Bust Round | - |
| 10 | `waist` | Waist Round | 26-42 |
| 11 | `slength` | Sleeve Length | - |
| 12 | `saround` | Arm Round | - |
| 13 | `sopen` | Sleeve End Round | - |
| 14 | `armhole` | Armhole | - |

### 8.3 Conversion Constant

```php
$cIn = 25.4;  // 1 inch = 25.4 millimeters
```

All measurements are converted to millimeters for SVG coordinate calculations.

### 8.4 Core Formulas

```php
// Bottom Dart (waist shaping)
$bDart = ($chest - $waist) / 2;

// Bust Variance
$bustVar = ($bust - ($chest / 2)) / 2;

// Leg Width (center tuck)
$legWidth = ($bustVar - ($waist / 4)) / 2;

// Vertical Apex Position
$vApex = ($apex + 0.5) * $cIn;

// Chest Vertical (for sleeve)
$chestVertical = ((($armhole / 2) - 1.5) * $cIn) + (0.04 * $cIn);

// Horizontal Apex (bust point distance from center)
// Based on bust size lookup table:
if ($bust >= 30 && $bust <= 32) $hApex = 3.25 * $cIn;
elseif ($bust > 32 && $bust <= 35) $hApex = 3.5 * $cIn;
elseif ($bust > 35 && $bust <= 38) $hApex = 3.75 * $cIn;
elseif ($bust >= 39 && $bust <= 41) $hApex = 4 * $cIn;
elseif ($bust >= 41 && $bust <= 44) $hApex = 4.25 * $cIn;
```

### 8.5 Pattern Files

| File | Purpose | Output |
|------|---------|--------|
| `saviMeasure.php` | Input form | Session variables |
| `saviFront.php` | Front pattern | 4 SVG paths + 4 tucks |
| `saviBack.php` | Back pattern | 4 SVG paths + tuck |
| `saviSleeve.php` | Sleeve pattern | 3 SVG paths + center line |
| `saviPatti.php` | Waist band | 2 SVG paths + hook patti |
| `saviComplete.php` | Assembly | 2x2 grid display |

### 8.6 SVG Path Colors

| Color | Purpose |
|-------|---------|
| Gray (dotted) | Reference outline |
| Black (solid) | Main stitch line |
| Brown (dotted) | Transition details |
| Red (dotted) | Seam allowance |

### 8.7 Session Variables for Patterns

```php
// Measurements (stored in inches and millimeters)
$_SESSION["shoulder"]   // inches
$_SESSION["shoulder1"]  // millimeters (value * 25.4)

// Calculated Positions
$_SESSION["vApex"]      // Vertical apex (mm)
$_SESSION["hApex"]      // Horizontal apex (mm)
$_SESSION["fbDart"]     // Front bottom dart

// SVG Path Data
$_SESSION["saviBlouseFront"]      // Front gray outline
$_SESSION["saviFrontBlouseGreen"] // Front black stitch
$_SESSION["saviBlouseFrontRed"]   // Front seam allowance
$_SESSION["saviFlTucks"]          // Front left tuck
$_SESSION["saviFbTucks"]          // Front bottom tuck
$_SESSION["saviFrTucks"]          // Front right tuck
$_SESSION["saviBackBlack"]        // Back main pattern
$_SESSION["saviBackTucks"]        // Back tuck design
$_SESSION["sleeveBlack"]          // Sleeve main pattern
$_SESSION["saviPattiBlack"]       // Waist band pattern
```

---

## 9. CSS Design System

### 9.1 Color Palette

```css
/* Primary Colors */
--color-primary: #8B7BA8;        /* Muted Purple - brand */
--color-primary-dark: #6B5B88;   /* Darker Purple - hover */
--color-primary-darker: #4B3B68; /* Darkest - active */

/* Accent Colors */
--color-accent-teal: #4FD1C5;    /* Teal - secondary */
--color-accent-teal-dark: #38B2A8;

/* Neutrals */
--color-dark: #2D3748;           /* Text color */
--color-gray: #718096;           /* Secondary text */
--color-light-gray: #F7FAFC;     /* Backgrounds */
--color-border: #E2E8F0;         /* Borders */

/* Status Colors */
--color-success: #48BB78;        /* Green */
--color-error: #E53E3E;          /* Red */
--color-warning: #ED8936;        /* Orange */

/* Decorative */
--color-light-purple: #B19CD9;   /* Highlights */
--color-pink: #FFB6D9;           /* Gradients */
```

### 9.2 Typography

```css
/* Headings */
font-family: 'Cormorant Garamond', serif;
font-size: 2.5rem - 4.5rem;

/* Body Text */
font-family: 'Libre Franklin', sans-serif;
font-size: 0.875rem - 1rem;

/* Forms */
font-family: 'Roboto', sans-serif;
```

### 9.3 Spacing Scale

```css
0.5rem  (8px)
0.75rem (12px)
1rem    (16px)
1.5rem  (24px)
2rem    (32px)
3rem    (48px)
4rem    (64px)
8rem    (128px)
```

### 9.4 Responsive Breakpoints

```css
/* Desktop (1025px+) */
/* Full layouts, 3-4 column grids */

/* Tablet (769px - 1024px) */
@media (max-width: 1024px) {
    /* 2 column grids, collapsed navigation */
}

/* Mobile (768px and below) */
@media (max-width: 768px) {
    /* Single column, stacked layouts */
}

/* Extra Small (576px and below) */
@media (max-width: 576px) {
    /* Reduced padding, smaller fonts */
}
```

### 9.5 Key CSS Classes

```css
/* Layout */
.hero-container     /* Grid layout for hero */
.portfolio-grid     /* 4-column portfolio */
.services-grid      /* Service cards grid */
.admin-container    /* Admin page wrapper */

/* Components */
.btn-primary        /* Primary button */
.btn-secondary      /* Secondary button */
.form-input         /* Input fields */
.dashboard-card     /* Dashboard sections */
.stat-card          /* Statistics display */

/* Status Badges */
.status-badge.active     /* Green */
.status-badge.inactive   /* Gray */
.status-badge.suspended  /* Red */

/* User Type Badges */
.user-type-badge.individual
.user-type-badge.boutique
.user-type-badge.wholesaler
.user-type-badge.pattern_provider
```

---

## 10. Shared Components

### 10.1 Header Component (`includes/header.php`)

Configuration variables:
```php
$pageTitle          // Browser tab title
$cssPath            // Stylesheet path
$logoPath           // Logo image path
$logoLink           // Logo click destination
$navBase            // Base path for nav links
$activePage         // Current page highlight
$additionalStyles   // Page-specific CSS
$additionalScripts  // Page-specific JavaScript
```

### 10.2 Footer Component (`includes/footer.php`)

- Closes HTML document
- Initializes Lucide icons
- Injects `$additionalScripts`
- Navbar scroll detection

### 10.3 Admin Header (`includes/admin-header.php`)

- Fixed white navigation
- Links: Dashboard, Portfolio sections, Enquiries
- User dropdown with logout

### 10.4 Mimic Banner (`includes/mimic-banner.php`)

- Orange warning banner
- Shows when `$_SESSION['is_mimicking']` is true
- "Exit & Return to Admin" button

---

## 11. Business Logic

### 11.1 Customer Name Normalization

Customer names are converted to Title Case on save:
```php
$customerName = ucwords(strtolower(trim($_POST['customer_name'])));
```

### 11.2 Measurement Edit Flow

1. Dashboard displays edit icon when measurements exist
2. Link: `pattern-studio.php?edit={measurement_id}`
3. Pattern studio loads measurement by ID
4. Form pre-filled with existing values
5. Submit performs UPDATE instead of INSERT

### 11.3 Admin Mimic Flow

1. Admin clicks username in user table
2. `mimic-user.php` saves original admin session
3. Creates new session as target user
4. Banner appears on all pages
5. "Exit" restores original admin session

### 11.4 Flash Messages

Using session variables for one-time messages:
```php
// Set message
$_SESSION['mimic_ended_message'] = true;

// Display and clear
if (isset($_SESSION['mimic_ended_message'])) {
    echo "Message here";
    unset($_SESSION['mimic_ended_message']);
}
```

### 11.5 Wholesale Catalog Filters

- Category filter (dropdown)
- Color filter (color swatches)
- Size filter (logical order: XS, S, M, L, XL, XXL, 3XL, 4XL)
- Combined filtering via JavaScript

---

## 12. Session Management

### 12.1 Session Start

All pages begin with:
```php
session_start();
```

### 12.2 Pattern Measurement Sessions

Measurements stored in format:
```php
$_SESSION["fieldname"]   // Value in inches
$_SESSION["fieldname1"]  // Value in millimeters
```

### 12.3 Session Security

- Session ID regeneration on login
- Session destruction on logout
- Admin session preserved during mimic

---

## 13. Security Considerations

### 13.1 Password Security

- Hashing: `password_hash($password, PASSWORD_DEFAULT)`
- Verification: `password_verify($password, $hash)`

### 13.2 SQL Injection Prevention

- All queries use PDO prepared statements
- Parameter binding with `execute([$params])`

### 13.3 XSS Prevention

- Output escaping: `htmlspecialchars($data)`
- Input sanitization in `test_input()` function

### 13.4 CSRF Protection

Contact form includes:
```php
$_SESSION['form_token'] = bin2hex(random_bytes(32));
```

### 13.5 File Upload Security

- Allowed extensions: jpg, jpeg, png, gif, webp
- File size limits
- Unique filename generation

---

## 14. Deployment

### 14.1 Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx with mod_rewrite
- SSL certificate (recommended)

### 14.2 Installation Steps

1. Clone/upload files to web root
2. Create MySQL database named `cm`
3. Run SQL files in order:
   ```bash
   mysql -u root -p cm < database/schema.sql
   mysql -u root -p cm < database/measurements_schema.sql
   mysql -u root -p cm < database/create_customers.sql
   mysql -u root -p cm < database/create_public_measurements.sql
   mysql -u root -p cm < database/create_admin_table.sql
   ```
4. Run migrations from `database/migrations/`
5. Update `config/database.php` with credentials
6. Update `config/email.php` with SMTP settings
7. Create upload directories with write permissions:
   ```bash
   chmod 755 uploads/patterns uploads/tailoring uploads/wholesale uploads/variants
   ```
8. Create admin user:
   ```sql
   INSERT INTO admin_users (username, password)
   VALUES ('admin', '$2y$10$...');  -- Use password_hash()
   ```

### 14.3 File Permissions

```bash
# Directories
chmod 755 config/
chmod 755 uploads/
chmod 755 uploads/*/

# Config files
chmod 644 config/*.php

# PHP files
chmod 644 *.php
chmod 644 pages/*.php
```

---

## Appendix A: Measurement Field Mappings

| Form Field | Database Column | Description |
|------------|-----------------|-------------|
| cust | customer_name | Customer/file name |
| shoulder | shoulder | Shoulder strap width |
| fshoulder | fshoulder | Full shoulder width |
| bnDepth | bnDepth | Back neck depth |
| blength | blength | Blouse back length |
| waist | waist | Waist circumference |
| chest | chest | Upper chest |
| bust | bust | Bust round |
| flength | flength | Front length |
| fndepth | fndepth | Front neck depth |
| apex | apex | Shoulder to apex |
| slength | slength | Sleeve length |
| saround | saround | Arm round |
| sopen | sopen | Sleeve end round |
| armhole | armhole | Armhole circumference |

---

## Appendix B: Admin Dashboard Statistics

```php
// Total Users
SELECT COUNT(*) FROM users;

// Active Users
SELECT COUNT(*) FROM users WHERE status = 'active';

// Saved Measurements
SELECT COUNT(*) FROM measurements;

// Public Measurements
SELECT COUNT(*) FROM public_measurements;

// Posting Counts (boutique customers)
SELECT boutique_user_id, COUNT(*) FROM customers GROUP BY boutique_user_id;

// Pattern Provider uploads
SELECT provider_id, COUNT(*) FROM pattern_making_portfolio GROUP BY provider_id;

// Wholesaler products
SELECT vendor_id, COUNT(*) FROM wholesale_portfolio GROUP BY vendor_id;
```

---

## Appendix C: SVG ViewBox Settings

| Pattern | ViewBox | Height |
|---------|---------|--------|
| Front | `-10, -5, 230, 380` | 350px |
| Back | `-5, -5, 230, 400` | 350px |
| Sleeve | `-15, -15, 330, 320` | 300px |
| Waist Band | `-5, 0, 230, 230` | 280px |

---

**End of Documentation**

*Generated: January 1, 2026*
*CuttingMaster v2025*
