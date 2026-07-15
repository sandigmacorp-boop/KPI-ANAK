(() => {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let pendingItem = null; // tugas yang menunggu foto dari pemilih file

    // Service worker — hanya jalan di konteks aman (localhost / https)
    if ('serviceWorker' in navigator && window.isSecureContext) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    // ---- Klik pada papan checklist ----
    document.addEventListener('click', (e) => {
        // Lihat foto bukti (jangan sampai ikut men-toggle tugas)
        const thumb = e.target.closest('.proof-thumb');
        if (thumb && thumb.getAttribute('src')) {
            openLightbox(thumb.getAttribute('src'));
            return;
        }

        const item = e.target.closest('.task-item[data-toggle-url]');
        if (!item || item.dataset.pending) return;

        const checking = !item.classList.contains('done');

        // Tugas wajib foto → buka kamera/galeri dulu
        if (checking && item.dataset.photo === '1') {
            const input = document.getElementById('proof-input');
            if (input) {
                pendingItem = item;
                input.value = '';
                input.click();
                return;
            }
        }

        // Batal centang yang ada fotonya → konfirmasi (fotonya ikut terhapus)
        if (!checking && item.dataset.hasPhoto === '1'
            && !confirm('Batalkan centang? Foto bukti akan ikut terhapus.')) {
            return;
        }

        sendToggle(item, null);
    });

    // Foto dipilih → kirim
    document.addEventListener('change', (e) => {
        if (e.target.id === 'proof-input' && e.target.files[0] && pendingItem) {
            sendToggle(pendingItem, e.target.files[0]);
            pendingItem = null;
        }
    });

    // Pemilih foto dibatalkan → orang tua boleh lanjut tanpa foto, anak tidak
    document.addEventListener('cancel', (e) => {
        if (e.target.id === 'proof-input' && pendingItem) {
            const board = pendingItem.closest('.board');
            if (board?.dataset.mode === 'admin'
                && confirm('Centang tanpa foto bukti? (khusus orang tua)')) {
                sendToggle(pendingItem, null);
            }
            pendingItem = null;
        }
    }, true);

    async function sendToggle(item, photoFile) {
        const board = item.closest('.board');
        item.dataset.pending = '1';
        item.classList.add('pending');

        try {
            const body = new FormData();
            if (board?.dataset.date) body.append('date', board.dataset.date);
            if (photoFile) body.append('photo', photoFile);

            const res = await fetch(item.dataset.toggleUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body,
            });

            if (res.status === 419) { location.reload(); return; }

            if (res.status === 422) {
                const err = await res.json().catch(() => null);
                alert(err?.message || 'Data tidak valid.');
                return;
            }

            if (!res.ok) throw new Error(String(res.status));

            const data = await res.json();
            item.classList.toggle('done', data.done);

            // Perbarui thumbnail bukti
            const thumb = item.querySelector('.proof-thumb');
            if (thumb) {
                if (data.done && data.photo_url) {
                    thumb.src = data.photo_url;
                    thumb.hidden = false;
                    item.dataset.hasPhoto = '1';
                } else if (!data.done) {
                    thumb.hidden = true;
                    thumb.removeAttribute('src');
                    item.dataset.hasPhoto = '0';
                }
            }

            updateBoard(data);
        } catch {
            alert('Gagal menyimpan. Periksa koneksi lalu coba lagi.');
        } finally {
            delete item.dataset.pending;
            item.classList.remove('pending');
        }
    }

    function updateBoard(d) {
        setText('pct', d.percent === null ? '—' : d.percent + '%');
        setText('done-count', d.done_tasks);
        setText('earned', d.earned_points);

        const fill = document.getElementById('bar-fill');
        if (fill) fill.style.width = (d.percent ?? 0) + '%';

        const stars = document.getElementById('stars');
        if (stars) {
            [...stars.querySelectorAll('.star')].forEach((s, i) => s.classList.toggle('on', (d.stars ?? 0) > i));
        }

        const all = document.getElementById('alldone');
        if (all) {
            if (d.all_done && all.hidden) {
                all.hidden = false;
                all.classList.remove('pop');
                void all.offsetWidth; // restart animasi
                all.classList.add('pop');
            } else if (!d.all_done) {
                all.hidden = true;
            }
        }
    }

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    };

    // ---- Lightbox foto bukti ----
    function openLightbox(src) {
        let lb = document.getElementById('lightbox');
        if (!lb) {
            lb = document.createElement('dialog');
            lb.id = 'lightbox';
            lb.innerHTML = '<img alt="Bukti foto"><button type="button" class="iconbtn" data-close aria-label="Tutup">✕</button>';
            document.body.appendChild(lb);
        }
        lb.querySelector('img').src = src;
        lb.showModal();
    }

    // ---- Toast (hadiah terbuka, dsb.) ----
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast pop';
        toast.setAttribute('role', 'status');
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    // ---- Salin link (dengan fallback untuk http LAN) ----
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-copy]');
        if (!btn) return;

        let ok = false;
        try {
            await navigator.clipboard.writeText(btn.dataset.copy);
            ok = true;
        } catch {
            ok = fallbackCopy(btn.dataset.copy);
        }

        const prev = btn.textContent;
        btn.textContent = ok ? 'Tersalin ✓' : 'Gagal — salin manual';
        setTimeout(() => (btn.textContent = prev), 1600);
    });

    function fallbackCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        let ok = false;
        try { ok = document.execCommand('copy'); } catch {}
        ta.remove();
        return ok;
    }

    // ---- Dialog / bottom sheet ----
    document.addEventListener('click', (e) => {
        const opener = e.target.closest('[data-dialog]');
        if (opener) {
            document.getElementById(opener.dataset.dialog)?.showModal();
            return;
        }

        if (e.target.closest('[data-close]')) {
            e.target.closest('dialog')?.close();
            return;
        }

        // Klik backdrop menutup sheet
        if (e.target instanceof HTMLDialogElement) e.target.close();
    });

    // Buka lagi dialog yang gagal validasi server
    const reopen = document.body.dataset.reopen;
    if (reopen) document.getElementById(reopen)?.showModal();

    // ---- Konfirmasi aksi berbahaya ----
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('form[data-confirm]');
        if (form && !confirm(form.dataset.confirm)) e.preventDefault();
    });

    // ---- "Setiap hari" vs pilihan hari ----
    const syncDays = (box) => {
        const picker = box.closest('form')?.querySelector('.days-picker');
        if (picker) picker.hidden = box.checked;
    };

    document.querySelectorAll('input[name="everyday"]').forEach((box) => {
        syncDays(box);
        box.addEventListener('change', () => syncDays(box));
    });

    // ---- Auto-refresh halaman anak (pengingat dalam aplikasi) ----
    if (document.body.classList.contains('kid-mode')) {
        const loadedAt = Date.now();

        // Saat tablet dibuka lagi dan halaman sudah lama, muat ulang agar segar.
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && Date.now() - loadedAt > 5 * 60 * 1000) location.reload();
        });

        // Saat slot waktu berganti (pagi→siang dst.), muat ulang agar pengingat muncul.
        const board = document.querySelector('.board[data-mode="kid"]');
        if (board?.dataset.slotUntil) {
            setInterval(() => {
                const now = new Date();
                const hhmm = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                if (hhmm > board.dataset.slotUntil) location.reload();
            }, 60 * 1000);
        }
    }

    // ---- Notifikasi perangkat (hanya konteks aman: localhost / HTTPS) ----
    const notifBtn = document.getElementById('enable-notif');
    if (notifBtn && 'Notification' in window && window.isSecureContext) {
        const reminderUrl = notifBtn.dataset.reminderUrl;

        const checkReminder = async () => {
            try {
                const res = await fetch(reminderUrl, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const d = await res.json();
                if (!d.slot || d.pending < 1) return;

                // Sekali saja per slot per hari.
                const key = 'sansfamily-notif-' + new Date().toDateString() + '-' + d.slot;
                if (localStorage.getItem(key)) return;
                localStorage.setItem(key, '1');

                new Notification('SANS FAMILY', {
                    body: '⏰ Waktunya tugas ' + d.slot_label + '! ' + d.pending + ' tugas menunggu:\n' + d.titles.join('\n'),
                    icon: '/icons/icon-192.png',
                    badge: '/icons/icon-192.png',
                });
            } catch {}
        };

        const startPolling = () => {
            checkReminder();
            setInterval(checkReminder, 60 * 1000);
        };

        if (Notification.permission === 'granted') {
            startPolling();
        } else if (Notification.permission !== 'denied') {
            notifBtn.hidden = false;
            notifBtn.addEventListener('click', async () => {
                const perm = await Notification.requestPermission();
                if (perm === 'granted') {
                    notifBtn.hidden = true;
                    showToast('🔔 Pengingat aktif selama aplikasi terbuka.');
                    startPolling();
                }
            });
        }
    }
})();
