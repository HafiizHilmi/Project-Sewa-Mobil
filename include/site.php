<?php
/**
 * Konteks bersama untuk halaman pelanggan (nav, footer, kartu mobil).
 * Semua angka & nama bisnis diambil dari tabel business_settings.
 */

if (!function_exists('site_settings')) {
    function site_settings(): array
    {
        if (!function_exists('getBusinessSettings')) {
            require_once __DIR__ . '/business_settings.php';
        }
        if (!function_exists('getPDO')) {
            require_once __DIR__ . '/db_config.php';
        }
        $s = getBusinessSettings();

        return [
            'name'    => trim((string) ($s['company_name'] ?? '')) ?: 'SewaMobil',
            'address' => trim((string) ($s['address'] ?? '')) ?: 'Surabaya, Jawa Timur',
            'phone'   => trim((string) ($s['phone'] ?? '')) ?: '08123456789',
            'email'   => trim((string) ($s['email'] ?? '')) ?: 'admin@sewamobilsby.id',
            'social'  => trim((string) ($s['social_media'] ?? '')) ?: '@sewamobilsby',
        ];
    }
}

if (!function_exists('site_brand')) {
    /** "Sewa M@obil" -> Sewa + Mobil (kata kedua diwarnai aksen). */
    function site_brand(string $name): string
    {
        $name = trim($name);
        if (stripos($name, 'sewa') === 0 && strlen($name) > 4) {
            return 'Sewa<span>' . htmlspecialchars(substr($name, 4)) . '</span>';
        }
        return htmlspecialchars($name);
    }
}

if (!function_exists('rupiah')) {
    function rupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('car_image_url')) {
    /** Kolom cars.image bisa berisi URL penuh atau path relatif terhadap public/assets/images. */
    function car_image_url(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return 'assets/images/cars/toyota-avanza-2024.jpg';
        }
        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            return $raw;
        }
        return 'assets/images/' . ltrim($raw, '/');
    }
}

if (!function_exists('site_initials')) {
    function site_initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $ini = strtoupper(substr($parts[0] ?? 'S', 0, 1));
        if (count($parts) > 1) {
            $ini .= strtoupper(substr(end($parts), 0, 1));
        }
        return $ini;
    }
}