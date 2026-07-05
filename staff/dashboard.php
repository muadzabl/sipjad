<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: /index.php");
    exit;
}
require_once '../config/firebase.php';
require_once '../components/header.php';
require_once '../components/sidebar.php';

// Ambil data kegiatan user ini
$activities = firebase_get('/activities');
$myActivities = [];

if ($activities) {
    foreach ($activities as $key => $act) {
        if (isset($act['user_id']) && $act['user_id'] === $_SESSION['user_id']) {
            $act['id'] = $key;
            $myActivities[] = $act;
        }
    }
    // Urutkan berdasarkan waktu mulai (terbaru ke terlama)
    usort($myActivities, function($a, $b) {
        return strtotime($b['waktu_mulai']) - strtotime($a['waktu_mulai']);
    });
}
?>

<div class="mb-8 animate-fade-in">
    <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Jadwal Saya</h2>
    <p class="text-slate-500 mt-1">Daftar kegiatan yang telah Anda jadwalkan beserta statusnya.</p>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3 animate-fade-in" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="block sm:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['success_msg']); ?></span>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-3xl overflow-hidden border border-slate-100 animate-slide-up">
    <?php if (empty($myActivities)): ?>
        <div class="p-12 text-center text-slate-500 flex flex-col items-center">
            <svg class="w-16 h-16 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <p class="text-lg font-medium mb-4">Anda belum memiliki jadwal kegiatan.</p>
            <a href="buat_kegiatan.php" class="bg-primary hover:bg-secondary text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-indigo-500/30 hover:-translate-y-1 inline-block">Buat Jadwal Baru</a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Kegiatan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Waktu Mulai</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    <?php foreach ($myActivities as $act): 
                        // Badge logic
                        $badgeClass = 'bg-slate-100 text-slate-800';
                        $statusText = str_replace('_', ' ', $act['status']);
                        if ($act['status'] === 'selesai') $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                        elseif ($act['status'] === 'berjalan') $badgeClass = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                        elseif ($act['status'] === 'menunggu_persetujuan_laporan') $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
                        elseif ($act['status'] === 'perlu_revisi') $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
                    ?>
                        <tr class="hover:bg-slate-50 transition-colors duration-200 group">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="font-bold text-slate-800"><?php echo htmlspecialchars($act['nama_kegiatan']); ?></div>
                                <div class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($act['lokasi'] ?? '-'); ?></div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-700"><?php echo date('d M Y', strtotime($act['waktu_mulai'])); ?></div>
                                <div class="text-xs text-slate-500"><?php echo date('H:i', strtotime($act['waktu_mulai'])); ?></div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full border <?php echo $badgeClass; ?>">
                                    <?php echo ucwords($statusText); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                <?php if (in_array($act['status'], ['berjalan', 'selesai', 'perlu_revisi'])): ?>
                                    <a href="buat_laporan.php?id=<?php echo htmlspecialchars($act['id']); ?>" class="inline-flex items-center text-primary bg-primary/10 hover:bg-primary hover:text-white px-3 py-1.5 rounded-lg transition-colors border border-primary/20 text-xs font-bold">
                                        <?php echo ($act['status'] === 'selesai' || isset($act['laporan'])) ? 'Lihat/Edit Laporan' : 'Buat Laporan'; ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($act['status'] === 'selesai'): ?>
                                    <a href="/cetak_laporan.php?id=<?php echo htmlspecialchars($act['id']); ?>" target="_blank" class="inline-flex items-center text-teal-600 bg-teal-50 hover:bg-teal-500 hover:text-white px-3 py-1.5 rounded-lg transition-colors border border-teal-200 text-xs font-bold">
                                        Cetak PDF
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../components/footer.php'; ?>
