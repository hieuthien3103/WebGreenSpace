-- Migration: Create inventory_batches table for FIFO batch inventory management
-- Date: 2026-04-15

CREATE TABLE IF NOT EXISTS inventory_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    batch_code VARCHAR(50) NOT NULL,
    quantity_received INT NOT NULL,
    quantity_remaining INT NOT NULL,
    cost_price DECIMAL(10, 2) DEFAULT NULL COMMENT 'Unit cost price for this batch',
    supplier VARCHAR(200) DEFAULT NULL,
    note VARCHAR(255) DEFAULT NULL,
    received_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventory_batches_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_inventory_batches_product_id (product_id),
    INDEX idx_inventory_batches_product_remaining (product_id, quantity_remaining),
    INDEX idx_inventory_batches_received_at (received_at),
    UNIQUE KEY unique_batch_code (batch_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
