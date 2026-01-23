-- Asset Item History Table
-- This table tracks the history/audit trail of individual asset items

CREATE TABLE IF NOT EXISTS asset_item_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    old_value TEXT,
    new_value TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES asset_items(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Index for better performance
CREATE INDEX idx_asset_item_history_item_id ON asset_item_history(item_id);
CREATE INDEX idx_asset_item_history_created_at ON asset_item_history(created_at);
