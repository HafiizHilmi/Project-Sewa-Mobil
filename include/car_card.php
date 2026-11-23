<?php
/** Partial kartu mobil (dipakai landing + katalog). Butuh include/site.php. */

if (!function_exists('car_card')) {
    function car_card(array $car, string $cta = 'Sewa', array $data = []): void
    {
        $attrs = '';
        foreach ($data as $key => $value) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars((string) $value) . '"';
        }
        $name     = trim(($car['make'] ?? '') . ' ' . ($car['model'] ?? ''));
        $year     = $car['year'] ?? '';
        $category = trim((string) ($car['category'] ?? '')) ?: 'Lainnya';
        $fuel     = trim((string) ($car['fuel_type'] ?? '')) ?: '-';
        $seats    = $car['seats'] ?? null;
        $trans    = trim((string) ($car['transmission'] ?? '')) ?: 'Manual';
        $price    = rupiah($car['price_per_day'] ?? 0);
        $image    = car_image_url($car['image'] ?? '');
        $id       = (int) ($car['id'] ?? 0);
        $stock    = (int) ($car['stock'] ?? 1);
        ?>
        <article class="car-card"<?= $attrs ?>>
            <div class="car-media">
                <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($name . ' ' . $year) ?>" loading="lazy">
                <span class="badge badge--accent"><?= htmlspecialchars($category) ?></span>
            </div>
            <div class="car-body">
                <div class="car-eyebrow">
                    <span><?= htmlspecialchars($year) ?> &middot; <?= htmlspecialchars($fuel) ?></span>
                    <span><?= $stock > 1 ? $stock . ' unit' : 'Tersedia' ?></span>
                </div>
                <h3 class="car-name"><?= htmlspecialchars($name) ?></h3>
                <div class="car-specs">
                    <span class="spec"><i class="bi bi-people"></i><?= $seats !== null ? (int) $seats . ' kursi' : '-' ?></span>
                    <span class="spec"><i class="bi bi-gear"></i><?= htmlspecialchars($trans) ?></span>
                    <span class="spec"><i class="bi bi-fuel-pump"></i><?= htmlspecialchars($fuel) ?></span>
                </div>
                <div class="car-footer">
                    <div class="price num"><?= htmlspecialchars($price) ?><small>per hari</small></div>
                    <a class="car-cta" href="index.php?module=Booking&action=checkout&car_id=<?= $id ?>">
                        <?= htmlspecialchars($cta) ?> <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </article>
        <?php
    }
}