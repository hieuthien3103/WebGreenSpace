-- Add pending_review status for simulated transfer admin approval flow.
-- Run this on existing databases before using admin approval feature.

ALTER TABLE orders
MODIFY COLUMN payment_status ENUM('unpaid', 'pending_review', 'paid', 'failed') NOT NULL DEFAULT 'unpaid';

ALTER TABLE payments
MODIFY COLUMN status ENUM('unpaid', 'pending_review', 'paid', 'failed') NOT NULL DEFAULT 'unpaid';
