<?php

$cachedBS = null;
function getBusinessSettings() {
    global $cachedBS;
    if ($cachedBS !== null) return $cachedBS;

    try {
        $pdo = getPDO();
        if (!$pdo) {
            $cachedBS = ['company_name' => 'Sewa Mobil SBY', 'phone' => '', 'social_media' => '', 'address' => ''];
            return $cachedBS;
        }

        // Ensure table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS business_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        // Seed defaults
        $checkSeed = $pdo->query("SELECT COUNT(*) FROM business_settings WHERE setting_key = 'company_name'");
        if ($checkSeed->fetchColumn() == 0) {
            $pdo->exec("INSERT IGNORE INTO business_settings (setting_key, setting_value) VALUES 
                ('company_name', 'Sewa Mobil SBY'),
                ('phone', '08123456789'),
                ('social_media', '@sewamobilsby'),
                ('address', 'Jl. Ketintang Baru, Surabaya, Jawa Timur')");
        }

        $stmt = $pdo->query("SELECT setting_key, setting_value FROM business_settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $cachedBS = $settings;
        return $settings;
    } catch (Exception $e) {
        $cachedBS = ['company_name' => 'Sewa Mobil SBY', 'phone' => '', 'social_media' => '', 'address' => ''];
        return $cachedBS;
    }
}