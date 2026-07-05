<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: /index.php");
    exit;
}
require_once '../config/firebase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mulai' && isset($_POST['activity_id'])) {
    $activityId = $_POST['activity_id'];
    $act = firebase_get("/activities/$activityId");
    if ($act && $act['user_id'] === $_SESSION['user_id'] && $act['status'] === 'belum_mulai') {
        firebase_patch("/activities/$activityId", ['status' => 'berjalan']);
        $_SESSION['success_msg'] = "Tugas berhasil dimulai. Silakan kerjakan dan buat laporannya nanti.";
    }
    header("Location: tugas_admin.php");
    exit;
}

require_once '../components/header.php';
require_once '../components/sidebar.php';

$activities = firebase_get('/activities');
$myTasks = [];

if ($activities) {
    foreach ($activities as $key => $act) {
        if (isset($act['user_id']) && $act['user_id'] === $_SESSION['user_id'] && !empty($act['is_from_admin'])) {
            $act['id'] = $key;
            $myTasks[] = $act;
        }
    }
    usort($myTasks, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}
?>

<div class="mb-8 animate-fade-in">
    <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Tugas dari Admin</h2>
    <p class="text-slate-500 mt-1">Daftar kegiatan yang secara khusus ditugaskan oleh Admin kepada Anda.</p>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3 animate-fade-in" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="block sm:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['success_msg']); ?></span>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-3xl overflow-hidden border border-slate-100 animate-slide-up">
    <?php if (empty($myTasks)): ?>
        <div class="p-12 text-center text-slate-500 flex flex-col items-center">
            <svg class="w-16 h-16 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <p class="text-lg font-medium mb-4">Belum ada tugas baru dari Admin untuk saat ini.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-indigo-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Detail Tugas</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Waktu Pelaksanaan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-indigo-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    <?php foreach ($myTasks as $act): 
                        $badgeClass = 'bg-slate-100 text-slate-800';
                        $statusText = str_replace('_', ' ', $act['status']);
                        if ($act['status'] === 'selesai') $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                        elseif ($act['status'] === 'berjalan') $badgeClass = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                        elseif ($act['status'] === 'belum_mulai') $badgeClass = 'bg-blue-100 text-blue-800 border-blue-200';
                        elseif ($act['status'] === 'menunggu_persetujuan_laporan') $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
                        elseif ($act['status'] === 'perlu_revisi') $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
                    ?>
                        <tr class="hover:bg-slate-50 transition-colors duration-200 group">
                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-800 mb-1"><?php echo htmlspecialchars($act['nama_kegiatan']); ?></div>
                                <?php if (!empty($act['deskripsi'])): ?>
                                    <div class="text-sm text-slate-500 line-clamp-2 max-w-xs"><?php echo htmlspecialchars($act['deskripsi']); ?></div>
                                <?php endif; ?>
                                <div class="text-xs text-slate-400 mt-2 font-medium">📍 <?php echo htmlspecialchars($act['lokasi'] ?? '-'); ?></div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <?php echo date('d M Y, H:i', strtotime($act['waktu_mulai'])); ?>
                                </div>
                                <div class="text-xs text-slate-500 mt-1 pl-6">s/d <?php echo date('d M Y, H:i', strtotime($act['waktu_selesai'])); ?></div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full border <?php echo $badgeClass; ?>">
                                    <?php echo ucwords($statusText); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                <?php if ($act['status'] === 'belum_mulai'): ?>
                                    <form method="POST" action="tugas_admin.php" class="inline">
                                        <input type="hidden" name="action" value="mulai">
                                        <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($act['id']); ?>">
                                        <button type="submit" class="inline-flex items-center text-white bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 px-4 py-2 rounded-xl transition-all shadow-md hover:-translate-y-0.5 text-xs font-bold">
                                            Mulai Kerjakan
                                        </button>
                                    </form>
                                <?php elseif (in_array($act['status'], ['berjalan', 'selesai', 'perlu_revisi'])): ?>
                                    <a href="buat_laporan.php?id=<?php echo htmlspecialchars($act['id']); ?>" class="inline-flex items-center text-primary bg-primary/10 hover:bg-primary hover:text-white px-4 py-2 rounded-xl transition-colors border border-primary/20 text-xs font-bold">
                                        <?php echo ($act['status'] === 'selesai' || isset($act['laporan'])) ? 'Lihat Laporan' : 'Buat Laporan'; ?>
                                    </a>
                                <?php elseif ($act['status'] === 'menunggu_persetujuan_laporan'): ?>
                                    <span class="text-amber-500 font-bold text-xs bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200">Menunggu Admin</span>
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