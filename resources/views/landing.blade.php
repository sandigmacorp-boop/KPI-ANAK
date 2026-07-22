<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#7C3AED">
    <meta name="description" content="SANS FAMILY — aplikasi gratis untuk memantau tugas rutin harian anak di rumah: checklist, bukti foto, poin, hadiah, dan gamifikasi biar anak semangat tanpa perlu diingatkan berkali-kali.">
    <title>{{ config('app.name') }} — Bikin Anak Semangat Kerjakan Tugas Rumah</title>
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="landing-body">

<nav class="landing-nav">
    <span class="landing-logo">⭐ {{ config('app.name') }}</span>
    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Masuk</a>
</nav>

<header class="landing-hero">
    <div class="landing-hero-inner">
        <span class="landing-badge">100% GRATIS</span>
        <h1>Bikin Anak Semangat Kerjakan Tugas Rumah — Tanpa Drama, Tanpa Teriak-teriak 😌</h1>
        <p class="landing-sub">
            {{ config('app.name') }} bantu Anda memantau tugas harian anak (rapikan tempat tidur, gosok gigi, PR, dll.)
            lewat checklist yang menyenangkan — lengkap dengan bukti foto, poin, hadiah, dan peliharaan virtual yang
            tumbuh sesuai kerajinan anak.
        </p>
        <a href="{{ route('register') }}" class="btn btn-primary btn-block landing-cta">🚀 Coba GRATIS Sekarang</a>
        <p class="muted landing-reassure">
            Gratis selamanya untuk fitur inti · Tanpa kartu kredit · Siap pakai dalam 2 menit ·
            <a href="{{ route('tur') }}">Lihat preview tampilan →</a>
        </p>
    </div>
</header>

<main class="landing-main">

    <section class="landing-section">
        <h2>Capek Mengingatkan Anak Berkali-kali Setiap Hari? 😮‍💨</h2>
        <p class="muted">
            "Ayo mandi!", "Rapikan mainanmu!", "Sudah gosok gigi belum?" — diucapkan berulang setiap hari sampai
            Anda sendiri lelah. Anak pun sering lupa atau menunda, dan sulit bagi orang tua untuk benar-benar tahu
            apakah tugasnya sudah selesai atau belum.
        </p>
        <p class="muted">
            {{ config('app.name') }} mengubah tugas rumah jadi checklist digital yang seru buat anak, dan mudah
            dipantau oleh Anda — kapan saja, dari HP Anda sendiri.
        </p>
    </section>

    <section class="landing-section">
        <h2>Cara Kerjanya — 3 Langkah Mudah</h2>
        <div class="landing-steps">
            <div class="landing-step">
                <span class="landing-step-num">1</span>
                <div>
                    <b>Daftar & Tambahkan Anak</b>
                    <p class="muted">Buat akun gratis, lalu tambahkan anak dan susun daftar tugas hariannya
                        (pagi/siang/sore/malam) sesuai kebutuhan keluarga.</p>
                </div>
            </div>
            <div class="landing-step">
                <span class="landing-step-num">2</span>
                <div>
                    <b>Anak Buka Link Checklist Sendiri</b>
                    <p class="muted">Setiap anak dapat link rahasia sendiri — bisa dibuka langsung di HP-nya,
                        tanpa perlu bikin akun atau login sendiri.</p>
                </div>
            </div>
            <div class="landing-step">
                <span class="landing-step-num">3</span>
                <div>
                    <b>Centang, Kumpulkan Poin, Tukar Hadiah</b>
                    <p class="muted">Anak mencentang tugas (dengan bukti foto bila perlu), poin terkumpul otomatis,
                        dan bisa ditukar dengan hadiah yang Anda tentukan sendiri.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section">
        <h2>Semua yang Anda Butuhkan, Sudah Ada</h2>
        <div class="landing-features">
            <div class="landing-feature">
                <span class="landing-feature-ico">⏰</span>
                <b>Checklist per Waktu</b>
                <p class="muted">Tugas dikelompokkan pagi/siang/sore/malam, otomatis terkunci begitu waktunya lewat
                    — melatih anak disiplin dengan waktu.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">📸</span>
                <b>Bukti Foto</b>
                <p class="muted">Tugas tertentu bisa diwajibkan pakai foto bukti, jadi Anda yakin tugasnya
                    benar-benar dikerjakan.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">⭐</span>
                <b>Skor KPI, Bintang & Streak</b>
                <p class="muted">Persentase penyelesaian harian, bintang prestasi, dan rentetan hari konsisten
                    yang memotivasi anak terus rajin.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">🎁</span>
                <b>Tukar Poin Jadi Hadiah</b>
                <p class="muted">Anda yang menentukan katalog hadiah & harga poinnya sendiri — es krim, main game
                    ekstra, jalan-jalan, apa saja.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">🐉</span>
                <b>Peliharaan Virtual</b>
                <p class="muted">Peliharaan digital anak tumbuh besar seiring kerajinannya — cara main yang bikin
                    anak makin semangat.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">🏅</span>
                <b>Lencana Pencapaian</b>
                <p class="muted">Belasan lencana yang terbuka otomatis di momen-momen spesial pencapaian anak.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">😊</span>
                <b>Catatan Mood Harian</b>
                <p class="muted">Anak bisa mencatat perasaannya setiap hari — Anda jadi lebih dekat dengan
                    keseharian mereka.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">🤝</span>
                <b>Tantangan Kerja Sama Tim</b>
                <p class="muted">Misi yang harus diselesaikan bersama kakak-adik, melatih kekompakan keluarga.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">🏆</span>
                <b>Tujuan Keluarga Bersama</b>
                <p class="muted">Kumpulkan poin bersama untuk tujuan besar keluarga, seperti liburan ke pantai.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">👨‍👩‍👧</span>
                <b>Multi-Anak & Multi-Orang Tua</b>
                <p class="muted">Tambahkan semua anak dan pasangan Anda ke keluarga yang sama — semua bisa
                    memantau bersama.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">📱</span>
                <b>Bisa Diinstall di HP</b>
                <p class="muted">Berjalan sebagai aplikasi (PWA) langsung dari browser — tanpa perlu download dari
                    app store.</p>
            </div>
            <div class="landing-feature">
                <span class="landing-feature-ico">🔔</span>
                <b>Pengingat Otomatis</b>
                <p class="muted">Notifikasi pengingat tugas per slot waktu, plus rekap mingguan lewat Telegram.</p>
            </div>
        </div>
    </section>

    <section class="landing-section landing-final-cta">
        <h2>Yuk, Coba Sekarang — Gratis!</h2>
        <p class="muted">Tidak perlu kartu kredit. Buat akun keluarga Anda sendiri dan mulai pantau tugas anak
            hari ini juga.</p>
        <a href="{{ route('register') }}" class="btn btn-primary btn-block landing-cta">🚀 Coba GRATIS Sekarang</a>
        <p class="muted landing-reassure">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
    </section>

</main>

<footer class="landing-footer">
    <p class="muted">⭐ {{ config('app.name') }} · Bantu anak lebih disiplin, sehari-hari.</p>
</footer>

</body>
</html>
