# Boutique Use Case - Database Installation Guide

This guide explains how to set up the database for the Boutique/Tailor use case.

## Prerequisites

- MySQL 5.7 or higher
- Existing CuttingMaster database with users and measurements tables

## Installation Steps

### Step 1: Create the Customers Table

The customers table stores customer information for boutique owners.

```bash
mysql -u root -p cuttingmaster < database/create_customers.sql
```

**Table Structure:**
- `id` - Primary key
- `boutique_user_id` - Foreign key linking to users table
- `customer_name` - Customer's name (required)
- `customer_reference` - Custom reference number/code (optional)
- `created_at` - Timestamp when customer was added
- `updated_at` - Timestamp when customer was last modified

### Step 2: Add Customer Linking to Measurements Table

This migration adds a `customer_id` column to the measurements table to link measurements to specific customers.

```bash
mysql -u root -p cuttingmaster < database/migrations/add_customer_id_to_measurements.sql
```

**Changes Made:**
- Adds `customer_id` column to measurements table
- Creates foreign key constraint to customers table
- Adds indexes for faster queries
- Nullable for backward compatibility with existing data

## Verification

After installation, verify the tables exist:

```sql
USE cuttingmaster;

-- Check customers table
DESC customers;

-- Check measurements table has customer_id
DESC measurements;

-- Verify foreign key constraints
SHOW CREATE TABLE customers;
SHOW CREATE TABLE measurements;
```

## How It Works

### For Regular Users
- Measurements are saved with `customer_name` and `customer_reference` fields (as before)
- `customer_id` remains NULL

### For Boutique Users
1. First customer measurement → Creates entry in `customers` table
2. Measurement is saved to `measurements` table with `customer_id` link
3. Future measurements for same customer → Select from dropdown, links to same `customer_id`

### Data Relationships

```
users (boutique)
    ↓ (boutique_user_id)
customers
    ↓ (customer_id)
measurements
```

## Rollback (If Needed)

If you need to rollback these changes:

```sql
-- Remove customer_id from measurements
ALTER TABLE measurements DROP FOREIGN KEY fk_customer_id;
ALTER TABLE measurements DROP INDEX idx_customer_id;
ALTER TABLE measurements DROP INDEX idx_user_customer;
ALTER TABLE measurements DROP COLUMN customer_id;

-- Drop customers table
DROP TABLE IF EXISTS customers;
```

## Next Steps

After database setup:
1. Register as a boutique user (select "Boutique / Tailor" during registration)
2. Log in and navigate to Pattern Studio
3. Enter customer measurements - customer dropdown will appear
4. View all customers and their measurements in boutique dashboard

## Support

For issues, check:
- MySQL error logs
- Verify foreign key constraints are supported (InnoDB engine)
- Ensure user has proper permissions
