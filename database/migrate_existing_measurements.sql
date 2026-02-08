-- Migration script to link existing measurements to customers table
-- This creates customer records for measurements that don't have customer_id set

USE cm;

-- Create customers from existing measurements where customer_id is NULL
-- and measurement_of is 'customer' (boutique customers)
INSERT INTO customers (boutique_user_id, customer_name, customer_reference)
SELECT DISTINCT
    m.user_id,
    m.customer_name,
    m.customer_reference
FROM measurements m
LEFT JOIN customers c ON c.boutique_user_id = m.user_id AND c.customer_name = m.customer_name
WHERE m.customer_id IS NULL
    AND m.measurement_of = 'customer'
    AND m.customer_name IS NOT NULL
    AND c.id IS NULL;  -- Only insert if customer doesn't already exist

-- Now link measurements to their customer records
UPDATE measurements m
INNER JOIN customers c ON c.boutique_user_id = m.user_id AND c.customer_name = m.customer_name
SET m.customer_id = c.id
WHERE m.customer_id IS NULL
    AND m.measurement_of = 'customer'
    AND m.customer_name IS NOT NULL;

SELECT 'Migration completed! Existing measurements have been linked to customer records.' AS status;
