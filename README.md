# CuttingMaster - Pattern & Measurement Management System

A comprehensive PHP web application for managing custom tailoring measurements with support for multiple categories (Women, Men, Boy, Girl) and detailed body measurements for pattern making.

## Features

- **User Authentication**
  - User registration with validation
  - Secure login system with role-based access (Admin/User)
  - Password hashing using PHP's `password_hash()`
  - Session-based authentication
  - Admin dashboard for user management

- **Measurement Management**
  - **Pattern Studio** - Interactive form for submitting measurements
  - Support for 4 categories: Women, Men, Boy, Girl
  - Tab-based interface (Upper Body / Lower Body)
  - Women-specific measurements (14 upper body fields)
  - Generic measurements for Men/Boy/Girl
  - Save measurements for "Self" or "Customer"
  - **Anonymous submission** - Users can submit measurements BEFORE logging in
  - Edit existing measurements
  - Field-level validation with real-time feedback

- **Dual-Save System**
  - **public_measurements** table - Anonymous data collection for analytics
  - **measurements** table - User-specific saved measurements (requires login)
  - Automatic transfer from public to saved measurements after login

- **Admin Features**
  - Dashboard with statistics
  - View all saved measurements (filterable by category)
  - View public measurements (anonymous analytics data)
  - User management
  - Delete measurements
  - Pagination support

- **Individual User Dashboard**
  - View personal measurements
  - Edit measurements
  - Category-specific field display
  - Measurement history

- **Boutique / Tailor Use Case** ⭐ NEW
  - **Customer Management** - Dedicated customer database for boutique owners
  - **Multi-Customer Support** - Manage measurements for multiple customers
  - **Customer Dropdown** - Searchable customer selector in Pattern Studio
  - **Boutique Dashboard** - Customer list with measurement display
  - **Business Fields** - Store boutique name, location, and contact info
  - **Customer Linking** - Measurements automatically linked to customers
  - **Quick Customer Add** - Add new customers while entering measurements
  - **Customer Search** - Find customers by name or reference number

- **Modern UI**
  - Clean, responsive design with gradient effects
  - Tab-based forms
  - Real-time validation
  - Professional styling with custom CSS
  - Lucide icons integration

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- PDO PHP extension

## Installation

1. Clone or download this repository
2. Navigate to the project directory
3. Set up the database:
   ```bash
   # Create the database
   mysql -u root -p < database/create_database.sql

   # Create users table
   mysql -u root -p cuttingmaster < database/create_users.sql

   # Create measurements table
   mysql -u root -p cuttingmaster < database/create_measurements.sql

   # Create public_measurements table
   mysql -u root -p cuttingmaster < database/create_public_measurements.sql

   # Run migrations
   mysql -u root -p cuttingmaster < database/migrations/add_women_fields_to_public_measurements.sql
   mysql -u root -p cuttingmaster < database/migrations/fix_public_measurements_nullable.sql

   # For Boutique/Tailor Use Case (Optional - only if you need multi-customer management)
   mysql -u root -p cuttingmaster < database/create_customers.sql
   mysql -u root -p cuttingmaster < database/migrations/add_customer_id_to_measurements.sql
   ```

   **Note**: See [BOUTIQUE_INSTALLATION.md](database/BOUTIQUE_INSTALLATION.md) for detailed boutique setup guide.

4. Configure database connection in `config/database.php`:
   ```php
   $host = 'localhost';
   $dbname = 'cuttingmaster';
   $username = 'your_db_username';
   $password = 'your_db_password';
   ```

5. Start PHP's built-in development server:
   ```bash
   php -S localhost:8000
   ```

6. Open your browser and visit: `http://localhost:8000`

## Project Structure

```
CM-2025/
├── index.php                      # Landing page
├── config/
│   └── database.php              # Database connection (PDO)
├── database/
│   ├── create_database.sql       # Database creation
│   ├── create_users.sql          # Users table
│   ├── create_measurements.sql   # Measurements table
│   ├── create_public_measurements.sql  # Public measurements table
│   └── migrations/
│       ├── add_women_fields_to_public_measurements.sql
│       └── fix_public_measurements_nullable.sql
├── pages/
│   ├── login.php                 # User login
│   ├── register.php              # User registration
│   ├── pattern-studio.php        # Main measurement form
│   ├── dashboard-individual.php  # User dashboard
│   ├── dashboard-admin.php       # Admin dashboard
│   ├── saved-measurements.php    # Admin: View all measurements
│   ├── public-measurements.php   # Admin: View anonymous data
│   ├── about-us.php             # About page
│   ├── contact-us.php           # Contact page
│   └── logout.php               # Logout handler
├── css/
│   └── styles.css               # Main stylesheet
├── .gitignore                   # Git ignore file
└── README.md                    # This file
```

## Usage

### Registration
1. Navigate to the Register page
2. Fill in username, email, and password
3. Confirm password
4. Submit the form
5. You'll be redirected to login after successful registration

### Pattern Studio (Measurement Submission)

Pattern Studio supports two main user flows with built-in duplicate prevention and data integrity safeguards.

#### Flow 1: Non-Logged In Users (Anonymous Submission)
1. Navigate to [pattern-studio.php](pages/pattern-studio.php)
2. Select category: **Women**, **Men**, **Boy**, or **Girl**
3. Choose tab: **Upper Body** or **Lower Body**
4. Fill in the measurement fields:
   - **Women Upper Body**: 14 fields (Blouse Back Length, Full Shoulder, Shoulder Strap, Back Neck Depth, Front Neck Depth, Shoulder to Apex, Front Length, Upper Chest, Bust Round, Waist Round, Sleeve Length, Arm Round, Sleeve End Round, Armhole)
5. Click **"Save Measurements"**
6. **Server Processing:**
   - Validates the form
   - Inserts data into **public_measurements** table (anonymous analytics)
   - Stores complete form data in `$_SESSION['pending_measurements']`
   - Redirects to `login.php?return=pattern-studio&action=save_measurements`
7. **After Login/Registration:**
   - Session data is retrieved from `$_SESSION['pending_measurements']`
   - Data is saved to **measurements** table with user_id
   - User is redirected to pattern-studio with success message
   - User can view measurements in their dashboard


#### Flow 2: Logged In Users (Authenticated Submission with Duplicate Prevention)
1. Navigate to [pattern-studio.php](pages/pattern-studio.php)
2. Select measurement type: **Self** or **Customer**
   - If **Customer** selected: Enter customer name and reference
3. Select category: **Women**, **Men**, **Boy**, or **Girl**
4. Choose tab: **Upper Body** or **Lower Body**
5. Fill in measurements (only active tab fields)
6. Click **"Save Measurements"**
7. **Client-Side Processing (JavaScript):**
   - Validates only fields in active tab
   - Disables all fields in inactive tab
   - Disables hidden category-specific fields (prevents duplicate field names)
   - Disables submit button and shows "Saving..." text
   - Submits form with unique submission token
8. **Server-Side Processing (PHP):**
   - **Step 1: Duplicate Check**
     - Compares `$_POST['submission_token']` with `$_SESSION['last_submission_token']`
     - If match: Browser refresh detected → Redirect to success page (no duplicate save)
     - If no match: Proceed to save
   - **Step 2: Database Transaction**
     - Begins transaction: `$pdo->beginTransaction()`
     - Inserts into **measurements** table (user-specific data)
     - IF bust AND waist provided: Inserts into **public_measurements** table (anonymous analytics)
     - Commits transaction: `$pdo->commit()`
     - If any error: Rolls back both INSERTs: `$pdo->rollBack()`
   - **Step 3: Token Storage**
     - Stores token in session: `$_SESSION['last_submission_token'] = $submissionToken`
   - **Step 4: POST-Redirect-GET**
     - Redirects to `pattern-studio.php?success=1`
     - Prevents duplicate submission on browser refresh
9. **Success Page:**
   - Displays "Measurements saved successfully!" message
   - User can submit new measurements or view saved ones

### Boutique UseCase

**For New Boutique Users (Without Account):**

1. **User selects**: Customer, Women and Upper Body
2. Enters customer name, reference, and measurement values
3. Clicks on "Save Measurements"
4. **Server Processing**:
   - Saves to `public_measurements` table (anonymous analytics)
   - Stores all data in `$_SESSION['pending_measurements']`
5. Page gets redirected to "Login" page
6. **User has TWO options:**

   **Option A: Direct Login (Existing User)**
   - Enters login credentials
   - Clicks "Login"
   - **System automatically**:
     - Creates customer record in `customers` table
     - Links measurement to customer via `customer_id`
     - Saves measurement to `measurements` table
   - Redirected to dashboard-boutique.php

   **Option B: Registration First (New User)**
   - Clicks on "Register" link
   - Fills registration form (selects "Boutique / Tailor" user type)
   - Completes registration
   - **Session data preserved during registration** ✅
   - Returns to login page
   - Enters login credentials
   - **System automatically**:
     - Creates customer record in `customers` table
     - Links measurement to customer via `customer_id`
     - Saves measurement to `measurements` table
   - Redirected to dashboard-boutique.php

7. On dashboard-boutique.php:
   - Left column: List of customers (Alphabetically sorted)
   - Clicks on customer name → measurements table shown below

**For Existing Boutique Users (Logged In):**

1. Navigate to Pattern Studio
2. Select "Customer" as measurement type
3. Enter customer name and reference (or select from dropdown if customer exists)
4. Fill in measurements
5. Click "Save Measurements"
6. **System automatically**:
   - Checks if customer exists
   - Creates new customer record OR uses existing customer ID
   - Saves measurement linked to customer
7. Success message displayed
8. Can view customer in dashboard-boutique.php

**Duplicate Prevention Mechanisms:**
1. **Submission Token**: Unique 32-character hex token generated per page load
2. **Session Validation**: Server checks if token was already processed
3. **Database Transaction**: Ensures atomic operation (both tables updated or neither)
4. **POST-Redirect-GET**: Redirects after POST to prevent refresh resubmission
5. **Button Disable**: Submit button disabled for 3 seconds after click
6. **Field Disabling**: Inactive/hidden fields disabled before submission

**Data Flow:**
```
Form Submission → Token Check → Transaction Start →
INSERT measurements → INSERT public_measurements →
Transaction Commit → Token Store → Redirect → Success
```

**Error Handling:**
- If transaction fails: Automatic rollback, error displayed to user
- If validation fails: Show field-specific error messages
- If duplicate submission: Silent redirect (no duplicate data created)

### Women's 14 Upper Body Fields

1. **blength** - Blouse Back Length (1)
2. **fshoulder** - Full Shoulder (2)
3. **shoulder** - Shoulder Strap (3)
4. **bnDepth** - Back Neck Depth (4)
5. **fndepth** - Front Neck Depth (5)
6. **apex** - Shoulder to Apex (6)
7. **flength** - Front Length (7)
8. **chest** - Upper Chest (8)
9. **bust** - Bust Round (9)
10. **waist** - Waist Round (10)
11. **slength** - Sleeve Length (11)
12. **saround** - Arm Round (12)
13. **sopen** - Sleeve End Round (13)
14. **armhole** - Armhole (14)

### Admin Features

#### Dashboard
- View total users, active users, saved measurements, and public measurements
- Quick links to view detailed data

#### Saved Measurements
- View all measurements from the **measurements** table
- Filter by category (Women/Men/Boy/Girl)
- Filter by body section (Upper Body/Lower Body/Both)
- Pagination support
- Delete measurements
- Search by user

#### Public Measurements
- View anonymous data from the **public_measurements** table
- Filter by category
- Filter by body section
- Analytics on measurement trends
- No user identification (completely anonymous)

### Boutique / Tailor Features

The boutique use case enables tailors and boutique owners to manage measurements for multiple customers efficiently.

#### Registration
1. Navigate to the Register page
2. Select **"Boutique / Tailor (for self and customers)"** as user type
3. Fill in business details:
   - Business Name (Boutique/Tailor Shop Name)
   - Business Location
   - Mobile Number
4. Complete registration with username, email, and password

#### Customer Management Workflow

**For Boutique Owners:**

1. **Adding First Customer Measurement**
   - Log in to your boutique account
   - Navigate to Pattern Studio
   - Select "Customer" as measurement type
   - Enter customer name and reference number
   - Fill in measurements (Women category - Upper/Lower Body)
   - Submit → Customer is automatically added to your customer database

2. **Adding Additional Measurements for Existing Customers**
   - Navigate to Pattern Studio
   - Customer dropdown appears (populated with your existing customers)
   - Select customer from dropdown OR enter new customer name
   - Fill in measurements
   - Submit → Measurement linked to selected customer

3. **Boutique Dashboard**
   - **Left Column**: Customer selector dropdown
     - Search customers by name or reference
     - Select customer to view their measurements
   - **Right Column**: (Future) Pattern management
   - View all measurements organized by customer
   - Filter by category (Women/Men/Boy/Girl)
   - Filter by body section (Upper/Lower)

#### Key Benefits

- **Centralized Customer Database**: All customer information in one place
- **Quick Customer Lookup**: Searchable dropdown for fast customer selection
- **Measurement History**: Track all measurements per customer over time
- **Business Organization**: Separate business name and location tracking
- **Customer Reference System**: Custom reference numbers for your filing system

#### Data Structure

```
Boutique User Account
    ├── Business Info (name, location, contact)
    ├── Customer 1
    │   ├── Measurement Set 1 (Upper Body, Women)
    │   ├── Measurement Set 2 (Lower Body, Women)
    │   └── Measurement Set 3 (Upper Body, Women - Updated)
    ├── Customer 2
    │   └── Measurement Set 1 (Upper Body, Women)
    └── Customer 3
        ├── Measurement Set 1 (Upper Body, Women)
        └── Measurement Set 2 (Lower Body, Women)
```

### Login
1. Navigate to the Login page
2. Enter your username (or email) and password
3. Submit the form
4. You'll be redirected based on your role:
   - **Admin**: Dashboard Admin
   - **User**: Dashboard Individual

### Logout
- Click the "Logout" button in the navigation
- You'll be redirected to the login page

## Database Schema

### users table
- id, username, email, password, user_type (individual/boutique/pattern_provider/wholesaler), status, created_at
- business_name, business_location, mobile_number (for boutique/wholesaler/pattern_provider)

### customers table (Boutique Use Case)
- id, boutique_user_id (FK to users.id), customer_name, customer_reference
- created_at, updated_at

### measurements table
- id, user_id, **customer_id** (FK to customers.id - for boutique users), measurement_of (self/customer), category (women/men/boy/girl)
- Women's 14 fields: blength, fshoulder, shoulder, bnDepth, fndepth, apex, flength, chest, bust, waist, slength, saround, sopen, armhole
- Generic fields: hips, height, inseam, thigh_circumference
- customer_name, customer_reference, notes, created_at, updated_at

### public_measurements table
- id, category
- Women's 14 fields: blength, fshoulder, shoulder, bnDepth, fndepth, apex, flength, chest, bust, waist, slength, saround, sopen, armhole
- Lower body fields: hips, height, inseam, thigh_circumference
- created_at
- **No user_id** - completely anonymous for analytics

## Key Features Explained

### Tab-Based Validation
- Form only validates fields in the active tab
- Disabled fields in inactive tabs are not submitted
- Hidden category-specific fields are disabled to prevent duplicate field names
- JavaScript prevents submission of incomplete data

### Duplicate Field Name Handling
- `bust` and `waist` fields exist in both women-specific and generic forms
- JavaScript disables hidden category fields before submission
- Ensures only the correct fields for the selected category are submitted

### Anonymous Data Collection
- Users can submit measurements WITHOUT creating an account
- Data is saved to public_measurements table
- After login, data is transferred to measurements table with user_id
- Admins can analyze trends from public_measurements without seeing user identities

### Duplicate Submission Prevention (Technical Implementation)

Pattern Studio implements a multi-layered approach to prevent duplicate records:

#### 1. Submission Token System
```php
// Generate token on page load (pattern-studio.php:295)
$submissionToken = bin2hex(random_bytes(16));

// Check for duplicate (pattern-studio.php:30-38)
if ($submissionToken === $lastToken && !empty($submissionToken)) {
    header('Location: pattern-studio.php?success=1');
    exit;
}

// Store after successful save (pattern-studio.php:172)
$_SESSION['last_submission_token'] = $submissionToken;
```

#### 2. Database Transaction Wrapper
```php
// Wrap both INSERTs in transaction (pattern-studio.php:67-182)
$pdo->beginTransaction();
try {
    // INSERT into measurements table
    $stmt->execute([...]);

    // INSERT into public_measurements table
    if (!empty($bust) && !empty($waist)) {
        $publicStmt->execute([...]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

#### 3. Client-Side Field Disabling
```javascript
// Disable inactive tab fields (pattern-studio.php:939-963)
// Prevents duplicate field names from being submitted
allTabContents.forEach(tabContent => {
    if (!tabContent.classList.contains('active')) {
        tabContent.querySelectorAll('input').forEach(input => {
            input.disabled = true;
        });
    }
});
```

#### 4. POST-Redirect-GET Pattern
```php
// Redirect after successful save (pattern-studio.php:175-176)
header('Location: pattern-studio.php?success=1');
exit;
```

### Security Features
- Passwords hashed using `password_hash()`
- Session-based authentication
- PDO prepared statements for SQL injection prevention
- Input validation and sanitization
- Role-based access control (Admin/User)
- Protected routes
- XSS prevention using `htmlspecialchars()`
- CSRF protection via submission tokens

## Development Notes

### Error Logging
- PHP errors are logged to error_log
- Debug logging enabled for public_measurements INSERT
- Check logs for "DEBUG:" messages to troubleshoot data flow

### Common Issues & Solutions

#### Issue 1: Data not showing in public_measurements table
**Symptoms:**
- Form submits successfully but no record in public_measurements
- Only measurements table has data

**Solutions:**
- ✅ **FIXED**: Ensure bust and waist fields have values (required for INSERT)
- ✅ **FIXED**: Hidden category-fields divs are now disabled before submission
- **Check**: PHP error logs for INSERT errors: `tail -f /var/log/php_errors.log`
- **Verify**: Transaction completed successfully (check for rollback in logs)

#### Issue 2: Duplicate records in public_measurements table
**Symptoms:**
- Two records created per submission
- One record with only bust/waist, another with all 14 fields

**Solutions:**
- ✅ **FIXED**: Submission token system prevents browser refresh duplicates
- ✅ **FIXED**: Database transaction ensures atomic INSERT (both tables or neither)
- ✅ **FIXED**: Field disabling prevents duplicate field names from being submitted
- ✅ **FIXED**: POST-Redirect-GET pattern prevents form resubmission
- **Verify**: Check browser console for form data before submission
- **Debug**: Look for "Form data being submitted:" in console (line 967)

#### Issue 3: Form validating fields from inactive tabs
**Symptoms:**
- Validation errors for fields in Lower Body tab when on Upper Body tab
- Cannot submit partial measurements

**Solutions:**
- ✅ **FIXED**: JavaScript only validates fields in active tab
- ✅ **FIXED**: Inactive tab fields are disabled before submission
- **Check**: Browser console for validation errors
- **Verify**: Only active tab fields should be enabled

#### Issue 4: Session data not persisting after login (non-logged-in users)
**Symptoms:**
- User submits measurements without login
- After login, measurements not saved to measurements table

**Solutions:**
- **Check**: `$_SESSION['pending_measurements']` exists after form submission
- **Verify**: login.php processes the session data correctly
- **Debug**: Add logging to track session data flow
- **Verify**: Database has record in public_measurements (anonymous copy)

#### Issue 5: Cannot submit measurements (button stays disabled)
**Symptoms:**
- Submit button shows "Saving..." and never re-enables
- Form appears frozen

**Solutions:**
- **Check**: Browser console for JavaScript errors
- **Verify**: Form is actually submitting (check Network tab)
- **Note**: Button re-enables after 3 seconds automatically (line 978)
- **Debug**: Check for validation errors preventing submission

## License

This is a demonstration project for educational purposes. Feel free to use and modify as needed.

## Additional Documentation

For detailed technical documentation on Pattern Studio implementation, see:
- **[PATTERN-STUDIO-DOCUMENTATION.md](PATTERN-STUDIO-DOCUMENTATION.md)** - Comprehensive technical guide including:
  - System architecture and file structure
  - Complete database schema with field definitions
  - Detailed user flows (logged-in and non-logged-in)
  - Technical implementation details (submission tokens, transactions, validation)
  - Security features and safeguards
  - API reference for form submissions
  - Troubleshooting guide with solutions
  - Women's 14 upper body measurements reference table

## Support

For issues or questions, please check:
- PHP error logs
- Browser console for JavaScript errors
- Database connection in config/database.php
- [PATTERN-STUDIO-DOCUMENTATION.md](PATTERN-STUDIO-DOCUMENTATION.md) for detailed troubleshooting
