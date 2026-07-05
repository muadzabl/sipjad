<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit;
}
require_once '../config/firebase.php';
require_once '../components/header.php';
require_once '../components/sidebar.php';

$activities = firebase_get('/activities');
$users = firebase_get('/users');

$kpi = [
    'total' => 0,
    'selesai' => 0,
    'berjalan' => 0,
    'menunggu_persetujuan_laporan' => 0,
    'perlu_revisi' => 0
];

$monthlyData = array_fill(1, 12, 0); // Jan-Dec
$recent_reports = [];

if ($activities) {
    foreach ($activities as $key => $act) {
        $kpi['total']++;
        $status = $act['status'] ?? '';
        if (isset($kpi[$status])) {
            $kpi[$status]++;
        }
        
        if (isset($act['waktu_mulai'])) {
            $month = (int)date('n', strtotime($act['waktu_mulai']));
            $monthlyData[$month]++;
        }

        // Collect reports waiting for approval to show recent staff submissions
        if ($status === 'menunggu_persetujuan_laporan' && !empty($act['laporan']['submitted_at'])) {
            $submittedAt = strtotime($act['laporan']['submitted_at']);
            $userName = isset($users[$act['user_id']]) ? $users[$act['user_id']]['nama_lengkap'] : 'Staff / Unknown';
            $recent_reports[] = [
                'id' => $key,
                'nama_kegiatan' => $act['nama_kegiatan'] ?? '-',
                'user_name' => $userName,
                'submitted_at' => $act['laporan']['submitted_at'],
                'timestamp' => $submittedAt
            ];
        }
    }
}

// Sort descending by submission time
usort($recent_reports, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});
?>

<div class="mb-8 animate-fade-in">
    <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Dashboard Admin</h2>
    <p class="text-slate-500 mt-1">Ringkasan aktivitas dan pengguna sistem SIPJAD.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate-slide-up" style="animation-delay: 0.1s;">
    <!-- Stat Card 1: Total Kegiatan -->
    <a href="/admin/approval_laporan.php?filter=semua" class="bg-gradient-to-br from-indigo-500 to-primary p-6 rounded-2xl shadow-xl shadow-indigo-200 text-white relative overflow-hidden group cursor-pointer transform hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-300/50 transition-all duration-300 block">
        <div class="absolute right-[-20%] top-[-20%] w-32 h-32 bg-white/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative z-10">
            <h3 class="text-indigo-100 font-medium tracking-wide text-sm mb-1 uppercase">Total Kegiatan</h3>
            <p class="text-4xl font-extrabold tracking-tight"><?php echo $kpi['total']; ?></p>
            <p class="text-indigo-200 text-xs mt-2 flex items-center gap-1">Lihat semua <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg></p>
        </div>
    </a>
    
    <!-- Stat Card 2: Selesai -->
    <a href="/admin/approval_laporan.php?filter=selesai" class="bg-gradient-to-br from-emerald-500 to-teal-600 p-6 rounded-2xl shadow-xl shadow-emerald-200 text-white relative overflow-hidden group cursor-pointer transform hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-300/50 transition-all duration-300 block">
        <div class="absolute right-[-20%] top-[-20%] w-32 h-32 bg-white/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative z-10">
            <h3 class="text-emerald-100 font-medium tracking-wide text-sm mb-1 uppercase">Selesai</h3>
            <p class="text-4xl font-extrabold tracking-tight"><?php echo $kpi['selesai']; ?></p>
            <p class="text-emerald-200 text-xs mt-2 flex items-center gap-1">Lihat laporan selesai <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg></p>
        </div>
    </a>

    <!-- Stat Card 3: Menunggu Laporan -->
    <a href="/admin/approval_laporan.php?filter=menunggu_persetujuan_laporan" class="bg-gradient-to-br from-amber-400 to-orange-500 p-6 rounded-2xl shadow-xl shadow-orange-200 text-white relative overflow-hidden group cursor-pointer transform hover:-translate-y-1 hover:shadow-2xl hover:shadow-orange-300/50 transition-all duration-300 block">
        <div class="absolute right-[-20%] top-[-20%] w-32 h-32 bg-white/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative z-10">
            <h3 class="text-orange-100 font-medium tracking-wide text-sm mb-1 uppercase">Menunggu Laporan</h3>
            <p class="text-4xl font-extrabold tracking-tight"><?php echo $kpi['menunggu_persetujuan_laporan']; ?></p>
            <p class="text-orange-200 text-xs mt-2 flex items-center gap-1">Proses sekarang <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg></p>
        </div>
    </a>

    <!-- Stat Card 4: Perlu Revisi -->
    <a href="/admin/approval_laporan.php?filter=perlu_revisi" class="bg-gradient-to-br from-rose-400 to-red-500 p-6 rounded-2xl shadow-xl shadow-red-200 text-white relative overflow-hidden group cursor-pointer transform hover:-translate-y-1 hover:shadow-2xl hover:shadow-red-300/50 transition-all duration-300 block">
        <div class="absolute right-[-20%] top-[-20%] w-32 h-32 bg-white/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative z-10">
            <h3 class="text-red-100 font-medium tracking-wide text-sm mb-1 uppercase">Perlu Revisi</h3>
            <p class="text-4xl font-extrabold tracking-tight"><?php echo $kpi['perlu_revisi']; ?></p>
            <p class="text-red-200 text-xs mt-2 flex items-center gap-1">Tinjau revisi <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg></p>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-slide-up" style="animation-delay: 0.2s;">
    <!-- Chart 1 -->
    <div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-2xl p-6 border border-slate-100">
        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
            <span class="w-2 h-6 bg-primary rounded-full mr-3"></span>
            Status Kegiatan
        </h3>
        <div class="relative h-64 w-full">
            <canvas id="pieChart"></canvas>
        </div>
    </div>
    <div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-2xl p-6 border border-slate-100">
        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
            <span class="w-2 h-6 bg-emerald-500 rounded-full mr-3"></span>
            Tren Kegiatan per Bulan
        </h3>
        <div class="relative h-64 w-full">
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>

<!-- Laporan Terbaru -->
<div class="mt-8 animate-slide-up" style="animation-delay: 0.3s;">
    <div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-2xl p-6 border border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-slate-800 flex items-center">
                <span class="w-2 h-6 bg-amber-400 rounded-full mr-3"></span>
                Aktivitas Staf (Laporan Masuk)
            </h3>
            <a href="/admin/approval_laporan.php?filter=menunggu_persetujuan_laporan" class="text-sm font-bold text-primary hover:underline">Lihat Semua Laporan →</a>
        </div>
        
        <?php if (empty($recent_reports)): ?>
            <div class="text-center py-8 text-slate-500 bg-slate-50 rounded-xl border border-slate-100">
                <p>Belum ada laporan baru dari staf saat ini.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 text-sm uppercase tracking-wider">
                            <th class="pb-3 font-semibold w-1/4">Nama Staf</th>
                            <th class="pb-3 font-semibold">Kegiatan</th>
                            <th class="pb-3 font-semibold w-1/4">Waktu Pengiriman</th>
                            <th class="pb-3 font-semibold w-1/6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach (array_slice($recent_reports, 0, 5) as $report): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-primary to-accent flex items-center justify-center text-white font-bold text-xs">
                                        <?php echo substr(htmlspecialchars($report['user_name']), 0, 1); ?>
                                    </div>
                                    <span class="font-bold text-slate-800 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($report['user_name']); ?></span>
                                </div>
                            </td>
                            <td class="py-4 text-slate-600 text-sm"><?php echo htmlspecialchars($report['nama_kegiatan']); ?></td>
                            <td class="py-4 text-slate-500 text-sm">
                                <?php 
                                $timeDiff = time() - $report['timestamp'];
                                if ($timeDiff < 3600) {
                                    echo floor($timeDiff / 60) . " menit yang lalu";
                                } elseif ($timeDiff < 86400) {
                                    echo floor($timeDiff / 3600) . " jam yang lalu";
                                } else {
                                    echo date('d M Y, H:i', $report['timestamp']);
                                }
                                ?>
                            </td>
                            <td class="py-4 text-center">
                                <a href="/admin/approval_laporan.php?filter=menunggu_persetujuan_laporan" class="inline-flex items-center justify-center bg-primary/10 text-primary hover:bg-primary hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                                    Tinjau
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (count($recent_reports) > 5): ?>
                <div class="mt-4 text-center">
                    <p class="text-xs text-slate-400">Menampilkan 5 laporan terbaru dari total <?php echo count($recent_reports); ?> laporan.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Pie Chart
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Berjalan', 'Selesai', 'Menunggu Persetujuan', 'Perlu Revisi'],
            datasets: [{
                data: [
                    <?php echo $kpi['berjalan']; ?>, 
                    <?php echo $kpi['selesai']; ?>, 
                    <?php echo $kpi['menunggu_persetujuan_laporan']; ?>, 
                    <?php echo $kpi['perlu_revisi']; ?>
                ],
                backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e'],
                borderColor: ['#fff', '#fff', '#fff', '#fff'],
                borderWidth: 3,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: 'Outfit', size: 12 },
                        padding: 16,
                        usePointStyle: true
                    }
                }
            },
            cutout: '65%'
        }
    });

    // Bar Chart
    const barCtx = document.getElementById('barChart').getContext('2d');
    const gradient = barCtx.createLinearGradient(0, 0, 0, 256);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.8)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.1)');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Jumlah Kegiatan',
                data: [<?php echo implode(',', $monthlyData); ?>],
                backgroundColor: gradient,
                borderColor: '#4f46e5',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { family: 'Outfit' }
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.2)'
                    }
                },
                x: {
                    ticks: { font: { family: 'Outfit' } },
                    grid: { display: false }
                }
            }
        }
    });
</script>

<?php require_once '../components/footer.php'; ?>
