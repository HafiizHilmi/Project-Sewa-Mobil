<?php
/** Footer pelanggan. Butuh include/site.php. */
require_once __DIR__ . '/site.php';
$site = site_settings();
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <a class="brand" href="index.php?module=Homepage&action=index"><?= site_brand($site['name']) ?></a>
                <p style="margin-top:14px;max-width:34ch">
                    Sewa mobil harian, mingguan, dan bulanan di Surabaya. Armada terawat,
                    harga transparan, verifikasi cepat.
                </p>
            </div>

            <div class="footer-col">
                <h4>Layanan</h4>
                <div class="footer-links">
                    <a href="index.php?module=Homepage&action=index">Sewa lepas kunci</a>
                    <a href="index.php?module=Homepage&action=index">Sewa + driver</a>
                    <a href="index.php?module=Homepage&action=index">Antar-jemput bandara</a>
                    <a href="index.php?module=Homepage&action=index">Mobil pernikahan</a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Informasi</h4>
                <div class="footer-links">
                    <a href="index.php?module=Landing&action=index#cara-sewa">Cara booking</a>
                    <a href="index.php?module=Homepage&action=index">Daftar harga</a>
                    <a href="index.php?module=Auth&action=register">Verifikasi identitas</a>
                    <a href="index.php?module=Profile&action=index">Pesanan saya</a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Kontak</h4>
                <div class="footer-links">
                    <span class="muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($site['address']) ?></span>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $site['phone'])) ?>"><i class="bi bi-telephone"></i> <?= htmlspecialchars($site['phone']) ?></a>
                    <a href="mailto:<?= htmlspecialchars($site['email']) ?>"><i class="bi bi-envelope"></i> <?= htmlspecialchars($site['email']) ?></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($site['name']) ?>. Seluruh hak cipta dilindungi.</span>
            <div class="footer-social">
                <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            </div>
        </div>
    </div>
</footer>