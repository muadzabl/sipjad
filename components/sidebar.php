<?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$nama = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : $username;
?>
<!-- Sidebar Backdrop Overlay -->
<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-25 hidden transition-opacity duration-300 md:hidden"></div>

<!-- Sidebar -->
<aside id="main-sidebar" class="w-64 bg-white/80 backdrop-blur-xl border-r border-slate-200 flex flex-col transition-all duration-300 shadow-xl shadow-slate-200/50 z-30 fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 md:static">
    <div class="h-20 flex items-center justify-center border-b border-slate-100 px-4 bg-gradient-to-r from-primary to-accent text-white shadow-md relative overflow-hidden">
        <div class="absolute inset-0 bg-black/5"></div>
        <h1 class="text-2xl font-extrabold tracking-widest drop-shadow-md relative z-10">SIPJAD</h1>
        <!-- Close button for mobile -->
        <button onclick="toggleSidebar()" class="absolute right-4 top-1/2 -translate-y-1/2 md:hidden p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-colors z-20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    
    <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
        <p class="text-[10px] text-slate-400 font-bold mb-1 uppercase tracking-widest">Selamat Datang</p>
        <p class="font-bold text-slate-800 text-sm truncate">
            <?php echo htmlspecialchars($nama); ?> 
        </p>
        <span class="text-[10px] bg-primary/10 text-primary font-bold px-2 py-0.5 rounded-full uppercase border border-primary/20 mt-1 inline-block"><?php echo htmlspecialchars($role); ?></span>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 px-3">
        <p class="text-[10px] text-slate-400 font-bold px-3 mb-3 uppercase tracking-widest">Menu Utama</p>
        <ul class="space-y-1">
            <?php if ($role === 'admin'): ?>
                <li>
                    <a href="/admin/dashboard.php" class="nav-link group flex items-center px-4 py-3 text-slate-600 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-200 hover:translate-x-1 font-medium">
                        <svg class="w-5 h-5 mr-3 opacity-60 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span>Dashboard Admin</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/buat_kegiatan.php" class="nav-link group flex items-center px-4 py-3 text-slate-600 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-200 hover:translate-x-1 font-medium">
                        <svg class="w-5 h-5 mr-3 opacity-60 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Buat Kegiatan</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/approval_admin.php" class="nav-link group flex items-center px-4 py-3 text-slate-600 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-200 hover:translate-x-1 font-medium">
                        <svg class="w-5 h-5 mr-3 opacity-60 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span>Persetujuan Admin</span>
                        <span id="badge-admin" class="ml-auto text-[10px] bg-rose-500 text-white font-bold px-2 py-0.5 rounded-full hidden">0</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/approval_laporan.php" class="nav-link group flex items-center px-4 py-3 text-slate-600 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-200 hover:translate-x-1 font-medium">
                        <svg class="w-5 h-5 mr-3 opacity-60 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Manajemen Laporan</span>
                        <span id="badge-laporan" class="ml-auto text-[10px] bg-amber-500 text-white font-bold px-2 py-0.5 rounded-full hidden">0</span>
                    </a>
                </li>
            <?php elseif ($role === 'staff'): ?>
                <li>
                    <a href="/staff/dashboard.php" class="nav-link group flex items-center px-4 py-3 text-slate-600 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-200 hover:translate-x-1 font-medium">
                        <svg class="w-5 h-5 mr-3 opacity-60 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Jadwal Saya</span>
                    </a>
                </li>
                <li>
                    <a href="/staff/buat_kegiatan.php" class="nav-link group flex items-center px-4 py-3 text-slate-600 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-200 hover:translate-x-1 font-medium">
                        <svg class="w-5 h-5 mr-3 opacity-60 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Buat Kegiatan</span>
                    </a>
                </li>
                <li>
                    <a href="/staff/dashboard.php" class="nav-link group flex items-center px-4 py-3 text-slate-600 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-200 hover:translate-x-1 font-medium">
                        <svg class="w-5 h-5 mr-3 opacity-60 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span>Laporan Kegiatan</span>
                    </a>
                </li>
                <li>
                    <a href="/staff/tugas_admin.php" class="nav-link group flex items-center px-4 py-3 text-slate-600 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-200 hover:translate-x-1 font-medium">
                        <svg class="w-5 h-5 mr-3 opacity-60 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <span>Tugas dari Admin</span>
                        <span id="badge-tugas" class="ml-auto text-[10px] bg-indigo-500 text-white font-bold px-2 py-0.5 rounded-full hidden">0</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <!-- Notification Bell Area -->
    <div class="px-4 pb-2 border-t border-slate-100 pt-3">
        <button id="notif-bell-btn" onclick="toggleNotifPanel()" class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors group relative">
            <span class="flex items-center gap-3 font-semibold text-sm">
                <svg class="w-5 h-5 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                Notifikasi
            </span>
            <span id="notif-total-badge" class="text-[10px] bg-primary text-white font-bold px-2 py-0.5 rounded-full hidden transition-all">0</span>
        </button>

        <!-- Notif Dropdown Panel -->
        <div id="notif-panel" class="hidden mx-1 mb-2 bg-white border border-slate-200 rounded-2xl shadow-2xl shadow-slate-300/50 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-primary to-secondary text-white">
                <p class="text-sm font-bold">🔔 Notifikasi Masuk</p>
            </div>
            <div id="notif-list" class="max-h-56 overflow-y-auto divide-y divide-slate-100">
                <div class="px-4 py-8 text-center text-slate-400 text-sm">Memuat...</div>
            </div>
            <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100">
                <?php if ($role === 'admin'): ?>
                    <a href="/admin/approval_laporan.php" class="text-primary text-xs font-bold hover:underline">Lihat semua laporan →</a>
                <?php else: ?>
                    <a href="/staff/dashboard.php" class="text-primary text-xs font-bold hover:underline">Lihat jadwal saya →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="p-4 border-t border-slate-100">
        <div class="text-[10px] text-slate-400 font-bold px-1 mb-2 uppercase tracking-widest">Akun</div>
        <a href="/logout.php" class="flex items-center justify-center w-full px-4 py-2.5 text-sm font-bold text-rose-600 bg-rose-50 border border-rose-100 rounded-xl hover:bg-rose-500 hover:text-white transition-all duration-300 group">
            <svg class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Keluar
        </a>
    </div>
</aside>

<!-- Main Content Area -->
<main class="flex-1 flex flex-col bg-slate-50/50 h-screen overflow-y-auto relative">
    <!-- Sticky Mobile Header -->
    <header class="h-16 flex items-center justify-between px-6 bg-white/80 backdrop-blur-md border-b border-slate-200 md:hidden sticky top-0 z-20 flex-shrink-0 shadow-sm">
        <button onclick="toggleSidebar()" class="p-2 -ml-2 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <span class="text-xl font-extrabold tracking-wider bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">SIPJAD</span>
        <div class="w-10"></div> <!-- spacer -->
    </header>

    <!-- Decorative background blobs -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-primary/5 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob pointer-events-none"></div>
    <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-accent/5 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000 pointer-events-none"></div>
    
    <div class="p-4 sm:p-6 md:p-8 w-full max-w-7xl mx-auto relative z-10">

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none" style="max-width: 340px;"></div>

<script>
    const userRole = '<?php echo htmlspecialchars($role); ?>';
    let lastNotifCount = parseInt(localStorage.getItem('sipjad_last_notif_count') || '0');
    let lastSelesaiCount = parseInt(localStorage.getItem('sipjad_last_selesai_count') || '0');
    let lastRevisiCount = parseInt(localStorage.getItem('sipjad_last_revisi_count') || '0');
    let lastTugasBaruCount = parseInt(localStorage.getItem('sipjad_last_tugas_baru_count') || '0');

    
    let notifPanelOpen = false;
    
    function toggleNotifPanel() {
        notifPanelOpen = !notifPanelOpen;
        const panel = document.getElementById('notif-panel');
        if (notifPanelOpen) {
            panel.classList.remove('hidden');
            loadNotifList();
        } else {
            panel.classList.add('hidden');
        }
    }

    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        const colors = {
            info:    'bg-white border-primary/30 text-slate-800',
            success: 'bg-emerald-50 border-emerald-300 text-emerald-800',
            warning: 'bg-amber-50 border-amber-300 text-amber-800',
            error:   'bg-rose-50 border-rose-300 text-rose-800',
        };
        const icons = {
            info:    '🔔',
            success: '✅',
            warning: '⚠️',
            error:   '❌',
        };
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-2xl border shadow-xl shadow-slate-300/40 backdrop-blur-sm text-sm font-medium ${colors[type]} animate-slide-up`;
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.innerHTML = `
            <span class="text-xl flex-shrink-0 mt-0.5">${icons[type]}</span>
            <div class="flex-1">
                <p class="font-bold mb-0.5">Notifikasi SIPJAD</p>
                <p class="opacity-80 text-xs">${message}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 ml-2 flex-shrink-0 text-lg leading-none">&times;</button>
        `;
        container.appendChild(toast);
        // Animate in
        requestAnimationFrame(() => {
            toast.style.transition = 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });
        // Auto dismiss after 6 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 400);
        }, 6000);
    }

    function loadNotifList() {
        const list = document.getElementById('notif-list');
        fetch('/api/notifications.php')
            .then(r => r.json())
            .then(data => {
                const counts = data.counts;
                const total = data.total_notifications;

                const totalBadge = document.getElementById('notif-total-badge');
                if (total > 0) {
                    totalBadge.textContent = total;
                    totalBadge.classList.remove('hidden');
                } else {
                    totalBadge.classList.add('hidden');
                }

                let html = '';
                
                if (userRole === 'admin') {
                    const badgeLaporan = document.getElementById('badge-laporan');
                    const badgeAdmin   = document.getElementById('badge-admin');

                    if (badgeLaporan) {
                        if (counts.menunggu_persetujuan_laporan > 0) {
                            badgeLaporan.textContent = counts.menunggu_persetujuan_laporan;
                            badgeLaporan.classList.remove('hidden');
                        } else {
                            badgeLaporan.classList.add('hidden');
                        }
                    }

                    if (badgeAdmin) {
                        if (counts.pending_admin > 0) {
                            badgeAdmin.textContent = counts.pending_admin;
                            badgeAdmin.classList.remove('hidden');
                        } else {
                            badgeAdmin.classList.add('hidden');
                        }
                    }

                    if (counts.menunggu_persetujuan_laporan > 0) {
                        html += `
                            <a href="/admin/approval_laporan.php?filter=menunggu_persetujuan_laporan" class="flex items-start gap-3 px-4 py-3 hover:bg-amber-50 transition-colors">
                                <span class="text-amber-500 text-xl mt-0.5 flex-shrink-0">⏳</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">${counts.menunggu_persetujuan_laporan} Laporan Menunggu</p>
                                    <p class="text-xs text-slate-500">Laporan masuk & perlu persetujuan</p>
                                </div>
                            </a>`;
                    }
                    if (counts.pending_admin > 0) {
                        html += `
                            <a href="/admin/approval_admin.php" class="flex items-start gap-3 px-4 py-3 hover:bg-rose-50 transition-colors">
                                <span class="text-rose-500 text-xl mt-0.5 flex-shrink-0">👤</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">${counts.pending_admin} Admin Menunggu</p>
                                    <p class="text-xs text-slate-500">Akun admin perlu disetujui</p>
                                </div>
                            </a>`;
                    }
                    if (counts.perlu_revisi > 0) {
                        html += `
                            <a href="/admin/approval_laporan.php?filter=perlu_revisi" class="flex items-start gap-3 px-4 py-3 hover:bg-orange-50 transition-colors">
                                <span class="text-orange-500 text-xl mt-0.5 flex-shrink-0">🔄</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">${counts.perlu_revisi} Laporan Direvisi</p>
                                    <p class="text-xs text-slate-500">Laporan dikembalikan ke staff</p>
                                </div>
                            </a>`;
                    }
                } else if (userRole === 'staff') {
                    const badgeTugas = document.getElementById('badge-tugas');
                    if (badgeTugas) {
                        if (counts.tugas_baru > 0) {
                            badgeTugas.textContent = counts.tugas_baru;
                            badgeTugas.classList.remove('hidden');
                        } else {
                            badgeTugas.classList.add('hidden');
                        }
                    }

                    if (counts.tugas_baru > 0) {
                        html += `
                            <a href="/staff/tugas_admin.php" class="flex items-start gap-3 px-4 py-3 hover:bg-indigo-50 transition-colors">
                                <span class="text-indigo-500 text-xl mt-0.5 flex-shrink-0">📋</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">${counts.tugas_baru} Tugas Baru</p>
                                    <p class="text-xs text-slate-500">Admin memberikan tugas baru untuk Anda</p>
                                </div>
                            </a>`;
                    }
                    if (counts.perlu_revisi > 0) {
                        html += `
                            <a href="/staff/dashboard.php" class="flex items-start gap-3 px-4 py-3 hover:bg-rose-50 transition-colors">
                                <span class="text-rose-500 text-xl mt-0.5 flex-shrink-0">🔄</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">${counts.perlu_revisi} Laporan Perlu Revisi</p>
                                    <p class="text-xs text-slate-500">Admin meminta revisi pada laporan Anda</p>
                                </div>
                            </a>`;
                    }
                    if (counts.selesai > 0) {
                        html += `
                            <div class="flex items-start gap-3 px-4 py-3 hover:bg-emerald-50 transition-colors cursor-default">
                                <span class="text-emerald-500 text-xl mt-0.5 flex-shrink-0">✅</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">${counts.selesai} Laporan Disetujui</p>
                                    <p class="text-xs text-slate-500">Total laporan Anda yang telah selesai</p>
                                </div>
                            </div>`;
                    }
                }

                if (!html) {
                    html = '<div class="px-4 py-8 text-center text-slate-400 text-sm flex flex-col items-center gap-2"><span class="text-3xl">✅</span><p>Tidak ada notifikasi baru!</p></div>';
                }
                list.innerHTML = html;
            })
            .catch(() => {
                list.innerHTML = '<div class="px-4 py-4 text-center text-red-400 text-sm">Gagal memuat notifikasi.</div>';
            });
    }

    function pollNotifications() {
        fetch('/api/notifications.php')
            .then(r => r.json())
            .then(data => {
                const total = data.total_notifications;
                const counts = data.counts;
                const totalBadge = document.getElementById('notif-total-badge');

                if (total > 0) {
                    totalBadge.textContent = total;
                    totalBadge.classList.remove('hidden');
                } else {
                    totalBadge.classList.add('hidden');
                }

                if (userRole === 'admin') {
                    const badgeLaporan = document.getElementById('badge-laporan');
                    const badgeAdmin   = document.getElementById('badge-admin');
                    
                    if (badgeLaporan) {
                        if (counts.menunggu_persetujuan_laporan > 0) {
                            badgeLaporan.textContent = counts.menunggu_persetujuan_laporan;
                            badgeLaporan.classList.remove('hidden');
                        } else {
                            badgeLaporan.classList.add('hidden');
                        }
                    }

                    if (badgeAdmin) {
                        if (counts.pending_admin > 0) {
                            badgeAdmin.textContent = counts.pending_admin;
                            badgeAdmin.classList.remove('hidden');
                        } else {
                            badgeAdmin.classList.add('hidden');
                        }
                    }

                    if (total > lastNotifCount && lastNotifCount >= 0) {
                        const diff = total - lastNotifCount;
                        if (counts.menunggu_persetujuan_laporan > 0 && diff > 0) {
                            showToast(`${diff} laporan baru masuk dan menunggu persetujuan Anda!`, 'warning');
                        }
                    }
                    lastNotifCount = total;
                    localStorage.setItem('sipjad_last_notif_count', total);
                    
                } else if (userRole === 'staff') {
                    const badgeTugas = document.getElementById('badge-tugas');
                    if (badgeTugas) {
                        if (counts.tugas_baru > 0) {
                            badgeTugas.textContent = counts.tugas_baru;
                            badgeTugas.classList.remove('hidden');
                        } else {
                            badgeTugas.classList.add('hidden');
                        }
                    }

                    // Check for new tasks
                    if (counts.tugas_baru > lastTugasBaruCount && lastTugasBaruCount >= 0) {
                        const diff = counts.tugas_baru - lastTugasBaruCount;
                        showToast(`Ada ${diff} tugas baru dari Admin!`, 'info');
                    }
                    lastTugasBaruCount = counts.tugas_baru;
                    localStorage.setItem('sipjad_last_tugas_baru_count', counts.tugas_baru);

                    // Check for new revisions
                    if (counts.perlu_revisi > lastRevisiCount && lastRevisiCount >= 0) {
                        showToast(`Admin menolak laporan Anda dan meminta revisi!`, 'error');
                    }
                    lastRevisiCount = counts.perlu_revisi;
                    localStorage.setItem('sipjad_last_revisi_count', counts.perlu_revisi);

                    // Check for new approvals
                    if (counts.selesai > lastSelesaiCount && lastSelesaiCount >= 0) {
                        showToast(`Laporan Anda telah disetujui oleh admin!`, 'success');
                    }
                    lastSelesaiCount = counts.selesai;
                    localStorage.setItem('sipjad_last_selesai_count', counts.selesai);
                }
            })
            .catch(() => {});
    }

    pollNotifications();
    setInterval(pollNotifications, 30000);

    function toggleSidebar() {
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }
</script>
