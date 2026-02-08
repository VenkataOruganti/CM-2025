# Database Setup Instructions

## Step 1: Create the Database

Run this command in your MySQL terminal or phpMyAdmin:

```sql
CREATE DATABASE IF NOT EXISTS cm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Step 2: Create the Users Table

You can run the SQL schema in one of these ways:

### Option A: Using MySQL Command Line

```bash
mysql -u root -p cm < /Users/venkataoruganti/Desktop/CM-2025/database/schema.sql
```

### Option B: Using phpMyAdmin

1. Open phpMyAdmin
2. Select the `cm` database
3. Click on the "SQL" tab
4. Copy and paste the contents of `schema.sql`
5. Click "Go"

### Option C: Using MySQL Terminal

```bash
mysql -u root -p
```

Then run:

```sql
USE cm;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('individual', 'business', 'wholesaler', 'pattern_designer') NOT NULL,
    business_name VARCHAR(255) DEFAULT NULL,
    business_location VARCHAR(255) DEFAULT NULL,
    mobile_number VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Step 3: Verify the Setup

Run this command to verify the table was created:

```sql
DESCRIBE users;
```

You should see the following columns:
- id
- username
- email
- password
- user_type
- business_name
- business_location
- mobile_number
- created_at
- updated_at

## Database Structure

### Users Table Fields:

- **id**: Auto-incrementing primary key
- **username**: User's display name (required)
- **email**: User's email address (required, unique)
- **password**: Hashed password (required)
- **user_type**: Type of user account (required):
  - `individual`: Individual users downloading for self
  - `business`: Tailors/Boutiques serving customers
  - `wholesaler`: Garment wholesalers offering catalogs
  - `pattern_designer`: Pattern designers
- **business_name**: Name of the business (optional, required for business/wholesaler/pattern_designer)
- **business_location**: Business location (optional, required for business/wholesaler/pattern_designer)
- **mobile_number**: Contact phone number (optional, required for business/wholesaler/pattern_designer)
- **created_at**: Account creation timestamp
- **updated_at**: Last update timestamp

## Testing the Connection

You can test the database connection by running:

```bash
php /Users/venkataoruganti/Desktop/CM-2025/test_connection.php
```

You should see: `✅ Connected successfully!`

## Next Steps

After setting up the database:

1. Navigate to the registration page: `http://localhost/CM-2025/pages/register.php`
2. Fill in the registration form
3. Select your user type
4. Submit the form
5. The data will be saved to the `users` table in MySQL
6. You can then login with your credentials at `http://localhost/CM-2025/pages/login.php`

## Troubleshooting

If you encounter any errors:

1. **Connection Error**: Check that MySQL is running and credentials are correct in `config/database.php`
2. **Table Creation Error**: Ensure you have proper permissions to create tables
3. **Duplicate Email Error**: Check if a user with that email already exists

## Security Notes

- Passwords are automatically hashed using PHP's `password_hash()` function (bcrypt)
- All database queries use prepared statements to prevent SQL injection
- Email addresses must be unique
- Business fields are validated based on user type
