<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#7C3AED">
    <meta name="description" content="Tur visual 11 halaman {{ config('app.name') }} — dari halaman depan sampai mode anak, gambaran lengkap sebelum daftar.">
    <title>Tur Halaman — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="tour-body">

<nav class="tour-nav">
    <a href="{{ route('landing') }}" class="tour-logo">⭐ {{ config('app.name') }}</a>
    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Coba GRATIS</a>
</nav>

<header class="tour-masthead">
    <p class="tour-eyebrow">Tur Produk · 11 Halaman</p>
    <h1>{{ config('app.name') }}, dari Halaman Depan Sampai Mode Anak</h1>
    <p class="tour-lede">Urutan yang benar-benar dilalui pengguna: pengunjung baru → orang tua yang masuk → admin
        platform → anak yang buka link checklist-nya sendiri.</p>
    <div class="tour-meta">
        <span class="tour-pill">👨‍👩‍👧 Orang Tua</span>
        <span class="tour-pill">🛡️ Admin</span>
        <span class="tour-pill">🧒 Anak</span>
    </div>
</header>

<nav class="tour-toc">
    <a href="#tur-tamu">Sebelum Daftar</a>
    <a href="#tur-ortu">Dashboard Orang Tua</a>
    <a href="#tur-admin">Mode Admin</a>
    <a href="#tur-anak">Yang Dilihat Anak</a>
</nav>

<main class="tour-main">

    <section class="tour-chapter" id="tur-tamu">
        <div class="tour-chapter-num">01</div>
        <div class="tour-chapter-head">
            <p class="tour-kicker">Sebelum Punya Akun</p>
            <h2>Yang dilihat pengunjung baru</h2>
            <p>Tiga halaman publik — tak perlu masuk untuk melihatnya. Di sinilah orang tua memutuskan apakah mau mencoba.</p>
        </div>
        <div class="tour-screens">
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">01</span><h3>Halaman Depan</h3></div>
                <p class="tour-cap">Media iklan: penjelasan fitur, cara kerja 3 langkah, dan ajakan "Coba GRATIS".</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/01-landing.png') }}" alt="Halaman depan"></div>
            </div>
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">02</span><h3>Daftar Akun</h3></div>
                <p class="tour-cap">Nama, email, kata sandi — keluarga baru langsung dapat ruang sendiri, terisolasi dari keluarga lain.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/02-daftar.png') }}" alt="Halaman daftar akun"></div>
            </div>
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">03</span><h3>Masuk</h3></div>
                <p class="tour-cap">Untuk orang tua yang sudah pernah mendaftar sebelumnya.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/03-login.png') }}" alt="Halaman masuk"></div>
            </div>
        </div>
    </section>

    <section class="tour-chapter" id="tur-ortu">
        <div class="tour-chapter-num">02</div>
        <div class="tour-chapter-head">
            <p class="tour-kicker">👨‍👩‍👧 Setelah Masuk</p>
            <h2>Dashboard orang tua</h2>
            <p>Lima halaman inti tempat orang tua memantau, mengatur, dan mengapresiasi — semua bisa diakses dari bottom nav yang sama.</p>
        </div>
        <div class="tour-screens">
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">04</span><h3>Beranda</h3></div>
                <p class="tour-cap">Kartu tiap anak: level peliharaan, streak, poin, dan berapa tugas yang masih tertunda hari ini.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/04-beranda.png') }}" alt="Beranda orang tua"></div>
            </div>
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">05</span><h3>Checklist Anak</h3></div>
                <p class="tour-cap">Rincian tugas per waktu (pagi/siang/sore/malam), mood hari itu, dan progres KPI — bisa dikoreksi kapan saja.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/05-checklist.png') }}" alt="Halaman checklist anak"></div>
            </div>
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">06</span><h3>Laporan &amp; Performa</h3></div>
                <p class="tour-cap">KPI 7 hari terakhir, riwayat poin, dan histori penukaran hadiah dalam satu halaman.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/06-laporan.png') }}" alt="Halaman laporan performa"></div>
            </div>
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">07</span><h3>Kelola Anak</h3></div>
                <p class="tour-cap">Atur tujuan keluarga, tantangan mingguan, dan tantangan kerja sama tim dari satu tempat.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/07-kelola.png') }}" alt="Halaman kelola anak"></div>
            </div>
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">08</span><h3>Pengaturan</h3></div>
                <p class="tour-cap">Profil, kata sandi, orang tua kedua, dan koneksi Telegram — semua kontrol akun.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/08-pengaturan.png') }}" alt="Halaman pengaturan"></div>
            </div>
        </div>
    </section>

    <section class="tour-chapter" id="tur-admin">
        <div class="tour-chapter-num">03</div>
        <div class="tour-chapter-head">
            <p class="tour-kicker">🛡️ Khusus Pemilik Platform</p>
            <h2>Mode admin</h2>
            <p>Satu halaman lintas-keluarga — hanya terlihat oleh akun yang ditandai admin, tak muncul di navigasi orang tua biasa.</p>
        </div>
        <div class="tour-screens">
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">09</span><h3>Dashboard Admin</h3></div>
                <p class="tour-cap">Ringkasan seluruh keluarga terdaftar, aktivitas lintas-tenant, dan kesehatan sistem — plus tombol nonaktifkan keluarga.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/09-admin.png') }}" alt="Dashboard admin"></div>
            </div>
        </div>
    </section>

    <section class="tour-chapter" id="tur-anak">
        <div class="tour-chapter-num">04</div>
        <div class="tour-chapter-head">
            <p class="tour-kicker">🧒 Tanpa Login</p>
            <h2>Yang dilihat anak</h2>
            <p>Anak buka link rahasianya sendiri di HP — tak perlu akun, tak perlu ingat kata sandi apa pun.</p>
        </div>
        <div class="tour-screens">
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">10</span><h3>Tugasku</h3></div>
                <p class="tour-cap">Peliharaan virtual, tujuan keluarga, mood harian, dan tantangan pekan ini — checklist jadi terasa seperti main.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/10-kid-tugasku.png') }}" alt="Halaman tugasku mode anak"></div>
            </div>
            <div class="tour-screen">
                <div class="tour-screen-label"><span class="tour-step">11</span><h3>Performaku</h3></div>
                <p class="tour-cap">Progres peliharaan, tantangan tim, dan katalog hadiah yang bisa ditukar dengan poin yang terkumpul.</p>
                <div class="tour-phone"><img src="{{ asset('img/tur/11-kid-performa.png') }}" alt="Halaman performaku mode anak"></div>
            </div>
        </div>
    </section>

    <section class="tour-final-cta">
        <h2>Sudah kebayang tampilannya?</h2>
        <p class="muted">Coba langsung, gratis — tidak perlu kartu kredit.</p>
        <a href="{{ route('register') }}" class="btn btn-primary btn-block tour-cta-btn">🚀 Coba GRATIS Sekarang</a>
    </section>

</main>

<footer class="tour-footer">
    <p class="muted">Tampilan contoh dengan data demo — nama &amp; angka di layar bukan data pengguna asli.</p>
</footer>

</body>
</html>
