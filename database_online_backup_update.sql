-- Update backups table to support online backup options
ALTER TABLE backups 
ADD COLUMN online_backup BOOLEAN DEFAULT FALSE,
ADD COLUMN cloud_provider VARCHAR(50) NULL,
ADD COLUMN cloud_backup_url VARCHAR(500) NULL,
ADD COLUMN cloud_backup_status ENUM('pending', 'uploading', 'completed', 'failed') NULL,
ADD COLUMN cloud_backup_error TEXT NULL,
ADD COLUMN cloud_backup_at TIMESTAMP NULL;

-- Create online backup configurations table
CREATE TABLE IF NOT EXISTS online_backup_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(50) NOT NULL,
    config_name VARCHAR(100) NOT NULL,
    api_key TEXT NULL,
    api_secret TEXT NULL,
    access_token TEXT NULL,
    refresh_token TEXT NULL,
    bucket_name VARCHAR(200) NULL,
    folder_path VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Insert default cloud backup configurations (placeholders)
INSERT INTO online_backup_configs (provider, config_name, is_active, created_by) VALUES
('google_drive', 'Google Drive Backup', FALSE, 1),
('dropbox', 'Dropbox Backup', FALSE, 1),
('onedrive', 'OneDrive Backup', FALSE, 1);
