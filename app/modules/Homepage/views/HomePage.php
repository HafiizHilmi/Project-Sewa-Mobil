<?php
require_once __DIR__ . '/../../../../include/site.php';
require_once __DIR__ . '/../../../../include/car_card.php';

$site = site_settings();
$cars = $cars ?? [];
$categories = $categories ?? [];
$search = trim($_GET['search'] ?? '');
$maxPrice = (int) ($maxPrice ?? 2000000);
$minPrice = (int) ($minPrice ?? 100000);
$totalTypes = (int) ($totalTypes ?? count($cars));
$verification_status = $verification_status ?? null;
$isSearching = $search !== '';

// Slider harga dibulatkan ke kelipatan 50rb agar nilai maksimum tetap bisa dipilih.
$rangeMin = (int) (floor($minPrice / 50000) * 50000);
$rangeMax = (int) (ceil($maxPrice / 50000) * 50000);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog mobil — <?= htmlspecialchars($site['name']) ?></title>
    <script>
        (function () {
            var saved = localStorage.getItem('theme');
            var dark = saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <?php $navActive = 'katalog'; include __DIR__ . '/../../../../include/nav.php'; ?>

    <main>
        <section class="container" style="padding-top:46px;padding-bottom:26px">
            <?php if (isset($_SESSION['user_id']) && in_array($verification_status, ['unverified', 'rejected'], true)): ?>
                <div class="notice notice--warn u-mt-8" style="margin-bottom:22px" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>
                        Identitasmu belum lengkap. <a href="index.php?module=Profile&action=index" style="text-decoration:underline;font-weight:700">Lengkapi KTP &amp; SIM</a>
                        dulu supaya bisa menyelesaikan pemesanan.
                    </span>
                </div>
            <?php endif; ?>

            <div class="u-between u-wrap" style="align-items:flex-end;gap:24px">
                <div>
                    <span class="eyebrow">Katalog &middot; <?= $totalTypes ?> tipe</span>
                    <h1 style="font-size:clamp(1.9rem,4vw,2.9rem);margin-top:10px;letter-spacing:-.035em">Pilih mobilmu</h1>
                    <p class="muted u-mt-8" style="font-size:.95rem">Harga per hari, transparan. Filter sesuai kebutuhanmu.</p>
                </div>

                <form id="searchForm" action="index.php" method="GET" class="search" style="width:min(380px,100%)">
                    <input type="hidden" name="module" value="Homepage">
                    <input type="hidden" name="action" value="index">
                    <i class="bi bi-search"></i>
                    <input class="input" type="text" id="searchInput" name="search"
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Cari merek, model, kategori…" autocomplete="off">
                    <button type="button" id="clearSearchBtn" class="search-clear <?= $isSearching ? '' : 'u-hide' ?>" aria-label="Hapus pencarian">
                        <i class="bi bi-x-lg" style="font-size:.8rem"></i>
                    </button>
                </form>
            </div>
        </section>

        <div class="filter-bar">
            <div class="container">
                <div class="filter-row">
                    <div class="chips" id="categoryChips" role="tablist" aria-label="Kategori mobil">
                        <button type="button" class="chip is-active" data-cat="*" role="tab" aria-selected="true">Semua</button>
                        <?php foreach ($categories as $cat): ?>
                            <?php $label = trim((string) $cat['category']) ?: 'Lainnya'; ?>
                            <button type="button" class="chip" data-cat="<?= htmlspecialchars(strtoupper($label)) ?>" role="tab" aria-selected="false">
                                <?= htmlspecialchars($label) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="toolbar">
                        <select id="filterTrans" class="select" aria-label="Transmisi">
                            <option value="ALL">Semua transmisi</option>
                            <option value="MANUAL">Manual</option>
                            <option value="MATIC">Matic</option>
                        </select>
                        <select id="sortPrice" class="select" aria-label="Urutkan harga">
                            <option value="asc">Termurah</option>
                            <option value="desc">Termahal</option>
                        </select>
                        <button type="button" id="toggleFilterBtn" class="btn btn--outline btn--sm" aria-expanded="false" aria-controls="filterPanel">
                            <i class="bi bi-sliders"></i> Harga
                        </button>
                    </div>
                </div>

                <div class="adv-panel" id="filterPanel" hidden>
                    <div class="field">
                        <label for="priceRange">Harga maksimal</label>
                        <span id="priceLabel" class="badge badge--accent" style="align-self:flex-start"><?= htmlspecialchars(rupiah($rangeMax)) ?></span>
                    </div>
                    <div class="field">
                        <div class="u-between" style="font-size:.75rem;color:var(--ink-3)">
                            <span><?= htmlspecialchars(rupiah($rangeMin)) ?></span>
                            <span><?= htmlspecialchars(rupiah($rangeMax)) ?></span>
                        </div>
                        <input type="range" id="priceRange" class="range" min="<?= $rangeMin ?>" max="<?= $rangeMax ?>" step="50000" value="<?= $rangeMax ?>">
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end">
                        <button type="button" id="resetFilter" class="btn btn--ghost btn--sm">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <section class="container" style="padding-bottom:40px">
            <div class="u-between u-wrap" style="margin-top:22px">
                <p class="result-count">
                    Menampilkan <b id="visibleCount"><?= count($cars) ?></b> dari <?= $totalTypes ?> tipe mobil
                </p>
                <?php if ($isSearching): ?>
                    <a class="tiny" href="index.php?module=Homepage&action=index" style="color:var(--accent);font-weight:650">
                        Hapus filter “<?= htmlspecialchars($search) ?>”
                    </a>
                <?php endif; ?>
            </div>

            <div class="car-grid" id="carGrid">
                <?php if (!empty($cars)): ?>
                    <?php foreach ($cars as $car): ?>
                        <?php
                        car_card($car, 'Sewa', [
                            'data-category' => strtoupper(trim((string) ($car['category'] ?? 'LAINNYA'))),
                            'data-trans'    => strtoupper((string) ($car['transmission'] ?? 'MANUAL')),
                            'data-price'    => (int) $car['price_per_day'],
                        ]);
                        ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div id="emptyState" class="empty" <?= empty($cars) ? '' : 'hidden' ?>>
                    <i class="bi bi-search"></i>
                    <h3>Tidak ada mobil yang cocok</h3>
                    <p>Coba longgarkan filter harga, ganti kategori, atau hapus kata kunci pencarian.</p>
                    <button type="button" class="btn btn--outline btn--sm u-mt-16" onclick="resetAllFilters()">Reset filter</button>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../../../../include/footer.php'; ?>

    <script>
    (function () {
        const grid = document.getElementById('carGrid');
        const cards = Array.from(grid.querySelectorAll('.car-card'));
        const chips = Array.from(document.querySelectorAll('#categoryChips .chip'));
        const transSel = document.getElementById('filterTrans');
        const sortSel = document.getElementById('sortPrice');
        const priceRange = document.getElementById('priceRange');
        const priceLabel = document.getElementById('priceLabel');
        const panel = document.getElementById('filterPanel');
        const panelBtn = document.getElementById('toggleFilterBtn');
        const resetBtn = document.getElementById('resetFilter');
        const countEl = document.getElementById('visibleCount');
        const emptyState = document.getElementById('emptyState');
        let activeCategory = '*';

        const fmt = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

        function apply() {
            const trans = (transSel.value || 'ALL').toUpperCase();
            const maxPrice = parseInt(priceRange.value, 10);
            const order = sortSel.value;
            let visible = 0;

            cards.forEach(card => {
                const cat = (card.dataset.category || '').toUpperCase();
                const cardTrans = (card.dataset.trans || '').toUpperCase();
                const price = parseInt(card.dataset.price, 10);
                const okCat = activeCategory === '*' || cat === activeCategory;
                const okTrans = trans === 'ALL' || cardTrans.includes(trans);
                const okPrice = price <= maxPrice;
                const show = okCat && okTrans && okPrice;
                card.hidden = !show;
                if (show) visible++;
            });

            const shown = cards.filter(c => !c.hidden);
            shown.sort((a, b) => order === 'asc'
                ? parseInt(a.dataset.price, 10) - parseInt(b.dataset.price, 10)
                : parseInt(b.dataset.price, 10) - parseInt(a.dataset.price, 10));
            shown.forEach(c => grid.appendChild(c));
            grid.appendChild(emptyState);

            emptyState.hidden = visible !== 0;
            countEl.textContent = visible;
        }

        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                chips.forEach(c => { c.classList.remove('is-active'); c.setAttribute('aria-selected', 'false'); });
                chip.classList.add('is-active');
                chip.setAttribute('aria-selected', 'true');
                activeCategory = chip.dataset.cat;
                apply();
            });
        });

        transSel.addEventListener('change', apply);
        sortSel.addEventListener('change', apply);
        priceRange.addEventListener('input', () => {
            priceLabel.textContent = fmt(priceRange.value);
            apply();
        });

        panelBtn.addEventListener('click', () => {
            const open = panel.hasAttribute('hidden');
            if (open) { panel.removeAttribute('hidden'); } else { panel.setAttribute('hidden', ''); }
            panelBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            panelBtn.classList.toggle('btn--outline', !open);
            panelBtn.classList.toggle('btn--primary', open);
        });

        function resetAllFilters() {
            activeCategory = '*';
            chips.forEach((c, i) => { c.classList.toggle('is-active', i === 0); c.setAttribute('aria-selected', i === 0 ? 'true' : 'false'); });
            transSel.value = 'ALL';
            sortSel.value = 'asc';
            priceRange.value = priceRange.max;
            priceLabel.textContent = fmt(priceRange.value);
            apply();
        }
        resetBtn.addEventListener('click', resetAllFilters);
        window.resetAllFilters = resetAllFilters;

        // --- Pencarian ---
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearchBtn');
        const searchForm = document.getElementById('searchForm');
        const toggleClear = () => clearBtn.classList.toggle('u-hide', searchInput.value.trim() === '');
        searchInput.addEventListener('input', toggleClear);
        clearBtn.addEventListener('click', () => { window.location.href = 'index.php?module=Homepage&action=index'; });
        searchForm.addEventListener('submit', (e) => {
            if (searchInput.value.trim() === '') {
                e.preventDefault();
                window.location.href = 'index.php?module=Homepage&action=index';
            }
        });
        toggleClear();

        apply();
    })();
    </script>
</body>
</html>