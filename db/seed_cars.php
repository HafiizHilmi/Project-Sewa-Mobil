<?php
/**
 * Seeder armada SewaMobil.
 * Sumber data tunggal: db/cars.json (dipakai juga oleh db/fetch_car_images.py).
 *
 * CLI  : php db/seed_cars.php
 * Web  : dipanggil otomatis dari include/db_config.php saat tabel cars masih kosong.
 */

function sewamobil_type_key(array $c): string
{
    return implode('|', [
        $c['make'] ?? '',
        $c['model'] ?? '',
        $c['year'] ?? '',
        $c['fuel_type'] ?? '',
        $c['engine_capacity'] ?? '',
        $c['seats'] ?? '',
        $c['transmission'] ?? 'Manual',
        $c['category'] ?? '',
        number_format((float) ($c['price_per_day'] ?? 0), 2, '.', ''),
    ]);
}

/**
 * Upsert seluruh armada dari db/cars.json ke tabel cars.
 * Baris dicocokkan lewat (make, model, year) supaya id lama tidak berubah
 * dan tidak ada duplikasi.
 */
function seed_cars(PDO $pdo): array
{
    $file = __DIR__ . '/cars.json';
    if (!is_file($file)) {
        return ['inserted' => 0, 'updated' => 0, 'error' => 'db/cars.json tidak ditemukan'];
    }
    $doc = json_decode(file_get_contents($file), true);
    if (!is_array($doc) || empty($doc['cars'])) {
        return ['inserted' => 0, 'updated' => 0, 'error' => 'db/cars.json tidak valid'];
    }

    $find = $pdo->prepare("SELECT id FROM cars WHERE make = ? AND model = ? AND year = ? LIMIT 1");
    $update = $pdo->prepare("
        UPDATE cars SET category = ?, fuel_type = ?, seats = ?, stock = ?, price_per_day = ?,
            image = ?, available = 1, is_type = 0, engine_capacity = ?, transmission = ?, type_key = ?
        WHERE id = ?
    ");
    $insert = $pdo->prepare("
        INSERT INTO cars (make, model, year, category, fuel_type, seats, stock, price_per_day,
            image, available, is_type, type_key, engine_capacity, transmission)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, ?, ?, ?)
    ");

    $inserted = 0;
    $updated = 0;
    foreach ($doc['cars'] as $c) {
        $typeKey = sewamobil_type_key($c);
        $price = (float) $c['price_per_day'];
        $image = $c['image'] ?? '';
        $find->execute([$c['make'], $c['model'], $c['year']]);
        $id = $find->fetchColumn();

        if ($id) {
            $update->execute([
                $c['category'], $c['fuel_type'], $c['seats'], (int) $c['stock'], $price,
                $image, $c['engine_capacity'] ?? '', $c['transmission'] ?? 'Manual', $typeKey, $id,
            ]);
            $updated++;
        } else {
            $insert->execute([
                $c['make'], $c['model'], $c['year'], $c['category'], $c['fuel_type'],
                $c['seats'], (int) $c['stock'], $price, $image, $typeKey,
                $c['engine_capacity'] ?? '', $c['transmission'] ?? 'Manual',
            ]);
            $inserted++;
        }
    }
    return ['inserted' => $inserted, 'updated' => $updated, 'error' => null];
}

// Mode CLI: siapkan koneksi sendiri lalu jalankan seeder.
if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === realpath(__FILE__)) {
    require_once __DIR__ . '/../include/db_config.php';
    $pdo = getPDO();
    if (!$pdo) {
        fwrite(STDERR, "Koneksi database gagal.\n");
        exit(1);
    }
    $result = seed_cars($pdo);
    $total = (int) $pdo->query("SELECT COUNT(*) FROM cars WHERE is_type = 0")->fetchColumn();
    echo "Seeder selesai: {$result['inserted']} baru, {$result['updated']} diperbarui. Total armada: {$total}\n";
    if ($result['error']) {
        fwrite(STDERR, $result['error'] . "\n");
        exit(1);
    }
}