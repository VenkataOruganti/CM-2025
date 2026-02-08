# Pattern Studio Documentation

## Overview
Pattern Studio is a web-based measurement collection system for CuttingMaster that allows users to submit body measurements for custom pattern generation. The system supports multiple categories (Women, Men, Boy, Girl) with category-specific measurement fields.

---

## Table of Contents
1. [System Architecture](#system-architecture)
2. [Database Schema](#database-schema)
3. [User Flow](#user-flow)
4. [Technical Implementation](#technical-implementation)
5. [Security Features](#security-features)
6. [API Reference](#api-reference)

---

## System Architecture

### File Structure
```
pages/
├── pattern-studio.php          # Main measurement form
├── public-measurements.php     # Admin view of anonymous data
├── saved-measurements.php      # Admin view of user measurements
├── dashboard-individual.php    # User dashboard with measurements
└── login.php                   # Authentication handling

config/
├── database.php               # PDO database connection
└── auth.php                   # Authentication functions
```

### Database Tables
1. **measurements** - User-specific measurements (with user_id)
2. **public_measurements** - Anonymous analytics data (no user_id)

---

## Database Schema

### measurements Table
Stores user-specific measurement data with authentication.

```sql
CREATE TABLE measurements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    measurement_of ENUM('self', 'customer'),
    category ENUM('women', 'men', 'boy', 'girl'),
    customer_name VARCHAR(255),
    customer_reference VARCHAR(255),

    -- Women's 14 Upper Body Fields --
    blength DECIMAL(5,2),      -- Blouse Back Length (1)
    fshoulder DECIMAL(5,2),    -- Full Shoulder (2)
    shoulder DECIMAL(5,2),     -- Shoulder Strap (3)
    bnDepth DECIMAL(5,2),      -- Back Neck Depth (4)
    fndepth DECIMAL(5,2),      -- Front Neck Depth (5)
    apex DECIMAL(5,2),         -- Shoulder to Apex (6)
    flength DECIMAL(5,2),      -- Front Length (7)
    chest DECIMAL(5,2),        -- Upper Chest (8)
    bust DECIMAL(5,2),         -- Bust Round (9)
    waist DECIMAL(5,2),        -- Waist Round (10)
    slength DECIMAL(5,2),      -- Sleeve Length (11)
    saround DECIMAL(5,2),      -- Arm Round (12)
    sopen DECIMAL(5,2),        -- Sleeve End Round (13)
    armhole DECIMAL(5,2),      -- Armhole (14)

    -- Generic Upper Body Fields (Men/Boy/Girl) --
    shoulder_width DECIMAL(5,2),
    arm_circumference DECIMAL(5,2),
    neck_circumference DECIMAL(5,2),

    -- Common Lower Body Fields --
    hips DECIMAL(5,2),
    height DECIMAL(5,2),
    inseam DECIMAL(5,2),
    thigh_circumference DECIMAL(5,2),

    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### public_measurements Table
Stores anonymous measurement data for analytics (no user_id).

```sql
CREATE TABLE public_measurements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category ENUM('women', 'men', 'boy', 'girl'),

    -- Same measurement fields as measurements table --
    -- (excluding user_id, measurement_of, customer fields)

    blength DECIMAL(5,2),
    fshoulder DECIMAL(5,2),
    shoulder DECIMAL(5,2),
    bnDepth DECIMAL(5,2),
    fndepth DECIMAL(5,2),
    apex DECIMAL(5,2),
    flength DECIMAL(5,2),
    chest DECIMAL(5,2),
    bust DECIMAL(5,2),
    waist DECIMAL(5,2),
    slength DECIMAL(5,2),
    saround DECIMAL(5,2),
    sopen DECIMAL(5,2),
    armhole DECIMAL(5,2),
    hips DECIMAL(5,2),
    height DECIMAL(5,2),
    inseam DECIMAL(5,2),
    thigh_circumference DECIMAL(5,2),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## User Flow

### Flow 1: Logged-In User
1. User navigates to `pattern-studio.php`
2. Selects measurement type: Self or Customer
3. Selects category: Women/Men/Boy/Girl
4. Fills out Upper Body OR Lower Body measurements
5. Clicks "Save Measurements"
6. **Server Actions:**
   - Validates submission token (prevents duplicates)
   - Starts database transaction
   - Inserts into `measurements` table
   - IF Women + Upper Body: Also inserts into `public_measurements`
   - Commits transaction
   - Stores token in session
   - Redirects with success message
7. User sees success message

### Flow 2: Non-Logged-In User
1. User navigates to `pattern-studio.php`
2. Fills out measurements (no Self/Customer selection shown)
3. Clicks "Save Measurements"
4. **Server Actions:**
   - Saves to `public_measurements` table
   - Stores form data in session
   - Redirects to `login.php?return=pattern-studio&action=save_measurements`
5. User logs in or registers
6. After authentication, `login.php` redirects back to pattern-studio
7. Session data is used to save to `measurements` table
8. User sees success message

### Flow 3: Admin Viewing Data
1. Admin logs into `dashboard-admin.php`
2. Navigates to "Saved Measurements" or "Public Measurements"
3. **Saved Measurements Page:**
   - Shows all user measurements with user_id
   - Filterable by category
   - Can delete individual records
4. **Public Measurements Page:**
   - Shows anonymous analytics data
   - No user identification
   - Filterable by category and body section

---

## Technical Implementation

### 1. Duplicate Submission Prevention

#### Submission Token System
**Location:** `pattern-studio.php` lines 30-38, 172, 295, 390

```php
// Generate unique token on page load
$submissionToken = bin2hex(random_bytes(16));

// Check for duplicate submission
$submissionToken = $_POST['submission_token'] ?? '';
$lastToken = $_SESSION['last_submission_token'] ?? '';

if ($submissionToken === $lastToken && !empty($submissionToken)) {
    header('Location: pattern-studio.php?success=1');
    exit;
}

// After successful save
$_SESSION['last_submission_token'] = $submissionToken;
```

**How It Works:**
- Each page load generates a unique 32-character hex token
- Token is embedded as hidden field in form
- Server checks if token matches last processed token
- If match: Duplicate submission (browser refresh) → redirect immediately
- If no match: New submission → process normally

### 2. Database Transaction Wrapper

#### Atomic Operations
**Location:** `pattern-studio.php` lines 67-182

```php
// Start transaction
$pdo->beginTransaction();

try {
    // Insert into measurements table
    $stmt = $pdo->prepare("INSERT INTO measurements ...");
    $stmt->execute([...]);

    // Insert into public_measurements table (if applicable)
    if (!empty($bust) && !empty($waist)) {
        $publicStmt = $pdo->prepare("INSERT INTO public_measurements ...");
        $publicStmt->execute([...]);
    }

    // Commit both INSERTs together
    $pdo->commit();

} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    throw $e;
}
```

**Benefits:**
- Both tables updated atomically (all-or-nothing)
- Prevents partial data insertion
- Ensures data consistency
- Eliminates duplicate records

### 3. Category-Based Field Management

#### Women's Fields vs Generic Fields
**Location:** `pattern-studio.php` lines 537-681

**Women's Upper Body (14 fields):**
```html
<div id="women-upper-fields" class="category-fields">
    <input name="blength">   <!-- Blouse Back Length -->
    <input name="fshoulder"> <!-- Full Shoulder -->
    <input name="shoulder">  <!-- Shoulder Strap -->
    <input name="bnDepth">   <!-- Back Neck Depth -->
    <input name="fndepth">   <!-- Front Neck Depth -->
    <input name="apex">      <!-- Shoulder to Apex -->
    <input name="flength">   <!-- Front Length -->
    <input name="chest">     <!-- Upper Chest -->
    <input name="bust">      <!-- Bust Round -->
    <input name="waist">     <!-- Waist Round -->
    <input name="slength">   <!-- Sleeve Length -->
    <input name="saround">   <!-- Arm Round -->
    <input name="sopen">     <!-- Sleeve End Round -->
    <input name="armhole">   <!-- Armhole -->
</div>
```

**Generic Upper Body (Men/Boy/Girl):**
```html
<div id="generic-upper-fields" class="category-fields" style="display: none;">
    <input name="bust">                <!-- Chest (reuses bust field) -->
    <input name="waist">               <!-- Waist -->
    <input name="shoulder_width">      <!-- Shoulder Width -->
    <input name="neck_circumference">  <!-- Neck Circumference -->
    <input name="arm_circumference">   <!-- Arm Circumference -->
</div>
```

#### JavaScript Field Toggle
**Location:** `pattern-studio.php` lines 798-838

```javascript
function updateFieldsVisibility() {
    const selectedCategory = document.querySelector('input[name="category"]:checked').value;

    if (selectedCategory === 'women') {
        womenUpperFields.style.display = 'block';
        genericUpperFields.style.display = 'none';

        // Enable women fields, disable generic
        womenUpperFields.querySelectorAll('input').forEach(input => input.disabled = false);
        genericUpperFields.querySelectorAll('input').forEach(input => input.disabled = true);
    } else {
        womenUpperFields.style.display = 'none';
        genericUpperFields.style.display = 'block';

        // Disable women fields, enable generic
        womenUpperFields.querySelectorAll('input').forEach(input => input.disabled = true);
        genericUpperFields.querySelectorAll('input').forEach(input => input.disabled = false);
    }
}
```

**Key Points:**
- Disabled fields are NOT submitted with form
- Prevents duplicate field names (bust/waist appear in both sections)
- Only active category's fields are sent to server

### 4. Tab-Based Validation

#### Upper Body vs Lower Body
**Location:** `pattern-studio.php` lines 891-937

```javascript
// Validate fields ONLY in active tab
const activeTab = document.querySelector('.tab-content.active');

if (activeTab) {
    const visibleCategoryFields = activeTab.querySelectorAll('.category-fields');

    visibleCategoryFields.forEach(categoryDiv => {
        if (categoryDiv.style.display === 'none') return; // Skip hidden

        const inputs = categoryDiv.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            if (!input.disabled && !validateField(input)) {
                hasErrors = true;
            }
        });
    });
}
```

**Features:**
- Users can submit Upper Body only or Lower Body only
- Validation only applies to visible, enabled fields
- Allows partial measurement saves

### 5. Form Submission Safeguards

#### Disable Inactive Fields Before Submit
**Location:** `pattern-studio.php` lines 939-963

```javascript
// Disable all fields NOT in active tab
const allTabContents = document.querySelectorAll('.tab-content');
allTabContents.forEach(tabContent => {
    if (!tabContent.classList.contains('active')) {
        const inactiveInputs = tabContent.querySelectorAll('input, textarea');
        inactiveInputs.forEach(input => input.disabled = true);
    }
});

// Disable all inputs in hidden category-fields divs
const allCategoryFields = document.querySelectorAll('.category-fields');
allCategoryFields.forEach(categoryDiv => {
    if (categoryDiv.style.display === 'none') {
        const hiddenInputs = categoryDiv.querySelectorAll('input, textarea');
        hiddenInputs.forEach(input => input.disabled = true);
    }
});
```

**Purpose:**
- Ensures only active tab's data is sent
- Prevents duplicate field names from being submitted
- Critical for avoiding double INSERT issues

---

## Security Features

### 1. Submission Token Protection
**Prevents:** Browser refresh duplicates, double-click submissions
**Implementation:** Cryptographically secure random token (16 bytes)
**Storage:** Session-based, server-side validation

### 2. Database Transactions
**Prevents:** Partial data insertion, data inconsistency
**Implementation:** PDO `beginTransaction()` and `commit()`
**Rollback:** Automatic on any exception

### 3. POST-Redirect-GET Pattern
**Prevents:** Form resubmission on page refresh
**Implementation:** Redirect to `?success=1` after POST
**Location:** Lines 175-176, 285-286

```php
header('Location: pattern-studio.php?success=1');
exit;
```

### 4. Input Validation
**Client-Side:** JavaScript validation (lines 895-927)
- Required field checks
- Min/max value validation
- Type checking (number inputs)

**Server-Side:** PHP validation (lines 50-53)
```php
if (empty($measurementOf) || empty($category)) {
    throw new Exception('Please select measurement type and category.');
}
```

### 5. SQL Injection Prevention
**Implementation:** PDO prepared statements
```php
$stmt = $pdo->prepare("INSERT INTO measurements (...) VALUES (?, ?, ...)");
$stmt->execute([$value1, $value2, ...]);
```

### 6. XSS Prevention
**Implementation:** `htmlspecialchars()` on all output
```php
<?php echo htmlspecialchars($message); ?>
```

---

## API Reference

### Form Submission Endpoints

#### POST /pages/pattern-studio.php
**Purpose:** Submit measurement data

**Parameters:**
```
measurement_of: 'self' | 'customer'
category: 'women' | 'men' | 'boy' | 'girl'
customer_name: string (optional, required if measurement_of='customer')
customer_reference: string (optional)
submission_token: string (required, generated by page)

-- Women's Upper Body Fields --
blength: decimal(5,2)
fshoulder: decimal(5,2)
shoulder: decimal(5,2)
bnDepth: decimal(5,2)
fndepth: decimal(5,2)
apex: decimal(5,2)
flength: decimal(5,2)
chest: decimal(5,2)
bust: decimal(5,2)
waist: decimal(5,2)
slength: decimal(5,2)
saround: decimal(5,2)
sopen: decimal(5,2)
armhole: decimal(5,2)

-- Generic Upper Body Fields --
bust: decimal(5,2)         (reused for chest)
waist: decimal(5,2)
shoulder_width: decimal(5,2)
arm_circumference: decimal(5,2)
neck_circumference: decimal(5,2)

-- Lower Body Fields --
hips: decimal(5,2)
height: decimal(5,2)
inseam: decimal(5,2)
thigh_circumference: decimal(5,2)

notes: text
```

**Response (Logged In):**
- **Success:** Redirect to `pattern-studio.php?success=1`
- **Duplicate:** Redirect to `pattern-studio.php?success=1` (token match)
- **Error:** Display error message on same page

**Response (Not Logged In):**
- **Success:** Redirect to `login.php?return=pattern-studio&action=save_measurements`
- **Error:** Display error message on same page

### Database Operations

#### Insert to measurements Table
**Trigger:** Logged-in user submits form
**Location:** Lines 100-131

```php
INSERT INTO measurements (
    user_id, measurement_of, category, customer_name, customer_reference,
    blength, fshoulder, shoulder, bnDepth, fndepth, apex, flength, chest,
    bust, waist, hips, height, shoulder_width, slength, arm_circumference,
    saround, sopen, armhole, inseam, thigh_circumference, neck_circumference,
    notes
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
```

#### Insert to public_measurements Table
**Trigger 1:** Logged-in user submits (if bust AND waist provided)
**Trigger 2:** Non-logged-in user submits (if bust AND waist provided)
**Location:** Lines 136-164, 221-249

```php
INSERT INTO public_measurements (
    category, blength, fshoulder, shoulder, bnDepth, fndepth, apex,
    flength, chest, bust, waist, slength, saround, sopen, armhole,
    hips, height, inseam, thigh_circumference
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
```

**Condition:** `!empty($bust) && !empty($waist)`

---

## Troubleshooting

### Common Issues

#### Issue 1: Duplicate Records in public_measurements
**Symptom:** Two records created per submission (one with just bust/waist, one with all fields)

**Root Cause:** Form submitting twice or multiple field sets being sent

**Solution Applied:**
1. Submission token system (prevents browser refresh duplicates)
2. Database transaction (ensures atomic INSERT)
3. Field disabling before submit (prevents duplicate field names)

**Verification:**
```javascript
// Check console for form data before submission
const formData = new FormData(form);
console.log('Form data being submitted:');
for (let [key, value] of formData.entries()) {
    console.log(key + ': ' + value);
}
```

#### Issue 2: Fields from Wrong Category Being Submitted
**Symptom:** Women-specific fields showing up in Men category data

**Root Cause:** Hidden category fields not disabled before submission

**Solution Applied:**
- JavaScript disables hidden category-fields divs (lines 955-963)
- Disabled inputs are excluded from form submission

**Verification:**
```javascript
// Check disabled state before submit
document.querySelectorAll('.category-fields').forEach(div => {
    console.log('Display:', div.style.display);
    div.querySelectorAll('input').forEach(input => {
        console.log('Disabled:', input.disabled);
    });
});
```

#### Issue 3: Session Token Not Persisting
**Symptom:** Every submission treated as new (no duplicate detection)

**Root Cause:** Session not started or token not being stored

**Solution:**
```php
// Ensure session_start() at top of file
session_start();

// Store token AFTER successful commit
$_SESSION['last_submission_token'] = $submissionToken;
```

---

## Future Enhancements

### Recommended Improvements

1. **AJAX Form Submission**
   - Replace POST-Redirect-GET with AJAX
   - Show success message without page reload
   - Better user experience

2. **Measurement History**
   - Track measurement changes over time
   - Show growth/change trends
   - Version control for edits

3. **Bulk Import**
   - CSV upload for multiple customers
   - Template download
   - Data validation

4. **Measurement Presets**
   - Save frequently used measurements as templates
   - Quick-fill for repeat customers
   - Size chart integration

5. **Mobile Optimization**
   - Better responsive design
   - Touch-friendly inputs
   - Mobile camera for measurement photos

6. **Email Notifications**
   - Send measurement summary to user
   - Confirmation emails
   - Reminder for updates

---

## File Change Log

### pattern-studio.php
**Date:** 2024-12-28
**Changes:**
- Added submission token system (lines 30-38, 172, 295, 390)
- Implemented database transaction wrapper (lines 67-182)
- Enhanced field disabling logic (lines 939-963)
- Added POST-Redirect-GET pattern (lines 175-176)

**Previous Issues Fixed:**
- Duplicate record insertion in public_measurements table
- Multiple field names causing conflicts
- Browser refresh creating duplicate submissions

### public-measurements.php
**Date:** 2024-12-27
**Changes:**
- Removed non-existent columns from SELECT query (lines 91-95)
- Fixed field display mapping

### saved-measurements.php
**Date:** 2024-12-27
**Changes:**
- Added delete functionality (lines 12-24)
- Fixed column display for Women's 14 fields

---

## Support & Maintenance

### Contact
For issues or questions:
- Check error logs: `error_log("message")`
- Review browser console for JavaScript errors
- Verify database schema matches documentation

### Error Logging
All errors are logged using PHP's `error_log()`:
```php
error_log("Measurement save error: " . $e->getMessage());
```

Check server error logs for troubleshooting.

---

## Appendix

### Women's 14 Upper Body Measurements Reference

| # | Field Name | Variable Name | Description |
|---|------------|---------------|-------------|
| 1 | Blouse Back Length | `blength` | Length from nape to waist |
| 2 | Full Shoulder | `fshoulder` | Shoulder to shoulder width |
| 3 | Shoulder Strap | `shoulder` | Shoulder strap width |
| 4 | Back Neck Depth | `bnDepth` | Depth of back neckline |
| 5 | Front Neck Depth | `fndepth` | Depth of front neckline |
| 6 | Shoulder to Apex | `apex` | Shoulder to bust point |
| 7 | Front Length | `flength` | Front waist length |
| 8 | Upper Chest | `chest` | Chest circumference at armhole |
| 9 | Bust Round | `bust` | Full bust circumference |
| 10 | Waist Round | `waist` | Natural waist circumference |
| 11 | Sleeve Length | `slength` | Shoulder to wrist |
| 12 | Arm Round | `saround` | Upper arm circumference |
| 13 | Sleeve End Round | `sopen` | Wrist circumference |
| 14 | Armhole | `armhole` | Armhole circumference |

### Generic Upper Body Measurements (Men/Boy/Girl)

| Field Name | Variable Name | Description |
|------------|---------------|-------------|
| Chest | `bust` | Chest circumference (reuses bust field) |
| Waist | `waist` | Natural waist circumference |
| Shoulder Width | `shoulder_width` | Shoulder to shoulder |
| Neck Circumference | `neck_circumference` | Neck circumference |
| Arm Circumference | `arm_circumference` | Upper arm circumference |

### Common Lower Body Measurements

| Field Name | Variable Name | Description |
|------------|---------------|-------------|
| Hips | `hips` | Hip circumference |
| Height | `height` | Full height |
| Inseam | `inseam` | Inner leg length |
| Thigh Circumference | `thigh_circumference` | Upper thigh circumference |

---

**End of Documentation**
