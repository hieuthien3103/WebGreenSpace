-- Migration: Create inventory_batch_allocations table for FIFO tracking
-- Tracks exactly which batch quantities were consumed for each order+product
-- Date: 2026-04-15

CREATE TABLE IF NOT EXISTS inventory_batch_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    batch_id INT NOT NULL,
    quantity INT NOT NULL COMMENT 'Units consumed from this batch for this order+product',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_batch_alloc_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_batch_alloc_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_batch_alloc_batch
        FOREIGN KEY (batch_id) REFERENCES inventory_batches(id) ON DELETE CASCADE,
    INDEX idx_batch_alloc_order_id (order_id),
    INDEX idx_batch_alloc_order_product (order_id, product_id),
    INDEX idx_batch_alloc_batch_id (batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
