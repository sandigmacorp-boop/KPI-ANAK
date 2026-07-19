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
            if (photoFile) body.append('photo', await shrinkImage(photoFile));

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
            updatePet(data.pet);
            if (data.achievements && data.achievements.length) celebrateAchievements(data.achievements);
            updateFamilyGoal(data.family_goal);
        } catch {
            alert('Gagal menyimpan. Periksa koneksi lalu coba lagi.');
        } finally {
            delete item.dataset.pending;
            item.classList.remove('pending');
        }
    }

    // Perkecil & kompres foto di perangkat sebelum dikirim: hemat kuota dan
    // lolos batas upload server (foto HP 3-8 MB jadi ~150 KB). Server tetap
    // menyimpannya sebagai WebP 400x400.
    async function shrinkImage(file, maxSide = 1000, quality = 0.8) {
        if (!file || !file.type || !file.type.startsWith('image/') || typeof createImageBitmap !== 'function') {
            return file;
        }
        try {
            const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
            const scale = Math.min(1, maxSide / Math.max(bitmap.width, bitmap.height));
            const w = Math.max(1, Math.round(bitmap.width * scale));
            const h = Math.max(1, Math.round(bitmap.height * scale));
            const canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            canvas.getContext('2d').drawImage(bitmap, 0, 0, w, h);
            bitmap.close && bitmap.close();
            const blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', quality));
            // Pakai hasil ciut hanya bila memang lebih kecil dari aslinya.
            return blob && blob.size < file.size
                ? new File([blob], 'foto.jpg', { type: 'image/jpeg' })
                : file;
        } catch {
            return file; // bila gagal, kirim foto asli (biar tetap bisa jalan)
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
                celebrateAllDone();
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

    // ---- Perayaan: confetti, bunyi, peliharaan naik level ----
    function confetti(count) {
        const colors = ['#7C3AED', '#F59E0B', '#22C55E', '#DB2777', '#0EA5E9', '#FBBF24'];
        const wrap = document.createElement('div');
        wrap.className = 'confetti-wrap';
        for (let i = 0; i < (count || 80); i++) {
            const p = document.createElement('div');
            p.className = 'confetti';
            p.style.left = Math.random() * 100 + 'vw';
            p.style.background = colors[i % colors.length];
            p.style.animationDuration = (2 + Math.random() * 1.6) + 's';
            p.style.animationDelay = (Math.random() * 0.4) + 's';
            if (Math.random() < 0.5) p.style.borderRadius = '50%';
            wrap.appendChild(p);
        }
        document.body.appendChild(wrap);
        setTimeout(() => wrap.remove(), 4200);
    }

    function playChime() {
        try {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            const ctx = new AC();
            [523.25, 659.25, 783.99, 1046.5].forEach((f, i) => { // C E G C
                const o = ctx.createOscillator();
                const g = ctx.createGain();
                o.type = 'triangle';
                o.frequency.value = f;
                const t = ctx.currentTime + i * 0.11;
                g.gain.setValueAtTime(0.0001, t);
                g.gain.exponentialRampToValueAtTime(0.22, t + 0.02);
                g.gain.exponentialRampToValueAtTime(0.0001, t + 0.32);
                o.connect(g);
                g.connect(ctx.destination);
                o.start(t);
                o.stop(t + 0.34);
            });
            setTimeout(() => ctx.close(), 900);
        } catch {}
    }

    function celebrateToast(html) {
        const el = document.createElement('div');
        el.className = 'celebrate-toast';
        el.setAttribute('role', 'status');
        el.innerHTML = html;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    function celebrateAllDone() {
        confetti(90);
        playChime();
    }

    function celebratePetLevelUp(pet) {
        confetti(120);
        playChime();
        celebrateToast('<span class="big">' + pet.emoji + '</span>Peliharaanmu naik ke <b>Level ' + pet.level + '</b>!<br>'
            + pet.species + ' ' + pet.stage_name + ' 🎉');
        const em = document.getElementById('pet-emoji');
        if (em) { em.classList.remove('bounce'); void em.offsetWidth; em.classList.add('bounce'); }
    }

    function celebrateAchievements(list) {
        confetti(100);
        playChime();
        const a = list[0];
        celebrateToast('<span class="big">' + a.emoji + '</span>Lencana baru!<br><b>' + a.title + '</b>'
            + (list.length > 1 ? ' <small>+' + (list.length - 1) + ' lagi</small>' : ''));
    }

    function updateFamilyGoal(fg) {
        if (!fg) return;
        const bar = document.getElementById('fg-bar');
        if (bar) bar.style.width = fg.percent + '%';
        const p = document.getElementById('fg-progress');
        if (p) p.textContent = (fg.progress || 0).toLocaleString('id-ID');
        if (fg.achieved_now) {
            confetti(150);
            playChime();
            celebrateToast('<span class="big">' + fg.emoji + '</span>Tujuan Keluarga tercapai!<br><b>' + fg.title + '</b> 🎉');
        }
    }

    function updatePet(pet) {
        if (!pet) return;
        const card = document.getElementById('pet-card');
        if (!card) return;
        const prev = parseInt(card.dataset.petStage || '0', 10);
        const em = document.getElementById('pet-emoji');
        if (em) em.textContent = pet.emoji;
        const bar = document.getElementById('pet-bar');
        if (bar) bar.style.width = pet.percent + '%';
        const tn = document.getElementById('pet-tonext');
        if (tn) tn.textContent = (pet.to_next || 0).toLocaleString('id-ID');
        card.dataset.petStage = pet.stage;
        if (pet.stage > prev) celebratePetLevelUp(pet);
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

    // ---- Mood harian ----
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.mood-btn');
        if (!btn) return;
        const card = btn.closest('.mood-card');
        const url = card && card.dataset.moodUrl;
        if (!url || btn.classList.contains('selected')) return;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ mood: btn.dataset.mood }),
            });
            if (res.status === 419) { location.reload(); return; }
            if (!res.ok) throw new Error(String(res.status));
            card.querySelectorAll('.mood-btn').forEach((b) => b.classList.remove('selected'));
            btn.classList.add('selected');
        } catch {
            alert('Gagal menyimpan perasaan. Coba lagi ya.');
        }
    });

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
