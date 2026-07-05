<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit;
}
require_once '../config/firebase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activity_id = $_POST['activity_id'] ?? '';
    $action = $_POST['action'] ?? '';
    $catatan_revisi = trim($_POST['catatan_revisi'] ?? '');

    if ($activity_id && $action) {
        if ($action === 'approve') {
            $updateData = ['status' => 'selesai'];
            $res = firebase_patch("/activities/$activity_id", $updateData);
            if ($res) {
                $_SESSION['success_msg'] = "Laporan disetujui, status kegiatan menjadi selesai.";
            } else {
                $_SESSION['error_msg'] = "Gagal menyetujui laporan.";
            }
        } elseif ($action === 'reject') {
            if (empty($catatan_revisi)) {
                $_SESSION['error_msg'] = "Catatan revisi wajib diisi jika menolak laporan.";
            } else {
                $updateData = [
                    'status' => 'perlu_revisi',
                    'laporan/catatan_revisi' => $catatan_revisi
                ];
                $res = firebase_patch("/activities/$activity_id", $updateData);
                if ($res) {
                    $_SESSION['success_msg'] = "Laporan dikembalikan untuk revisi.";
                } else {
                    $_SESSION['error_msg'] = "Gagal memproses penolakan.";
                }
            }
        }
    }
    header("Location: approval_laporan.php");
    exit;
}

require_once '../components/header.php';
require_once '../components/sidebar.php';

$activities = firebase_get('/activities');
$users = firebase_get('/users');

// Filter dari query string (untuk deep-link dari KPI card)
$filter = $_GET['filter'] ?? 'menunggu_persetujuan_laporan';
$allowedFilters = ['menunggu_persetujuan_laporan', 'perlu_revisi', 'selesai', 'berjalan', 'semua'];
if (!in_array($filter, $allowedFilters)) {
    $filter = 'menunggu_persetujuan_laporan';
}

$pendingReports = [];
$filterLabels = [
    'menunggu_persetujuan_laporan' => 'Menunggu Persetujuan Laporan',
    'perlu_revisi' => 'Perlu Revisi',
    'selesai' => 'Selesai',
    'berjalan' => 'Sedang Berjalan',
    'semua' => 'Semua Kegiatan',
];

if ($activities) {
    foreach ($activities as $key => $act) {
        $status = $act['status'] ?? '';
        $match = ($filter === 'semua') || ($status === $filter);
        if ($match) {
            $act['id'] = $key;
            $act['user_name'] = isset($users[$act['user_id']]) ? $users[$act['user_id']]['nama_lengkap'] : 'Unknown';
            $pendingReports[] = $act;
        }
    }
}
?>

<div class="mb-6 animate-fade-in">
    <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Manajemen Laporan</h2>
    <p class="text-slate-500 mt-1">Filter: <span class="font-semibold text-primary"><?php echo $filterLabels[$filter]; ?></span> &mdash; <?php echo count($pendingReports); ?> kegiatan ditemukan.</p>
</div>

<!-- Filter Pills -->
<div class="flex flex-wrap gap-2 mb-8 animate-fade-in">
    <?php
    $pills = [
        'menunggu_persetujuan_laporan' => ['label' => '⏳ Menunggu Laporan', 'color' => 'amber'],
        'perlu_revisi'                 => ['label' => '🔄 Perlu Revisi',     'color' => 'rose'],
        'selesai'                      => ['label' => '✅ Selesai',           'color' => 'emerald'],
        'berjalan'                     => ['label' => '▶️ Berjalan',         'color' => 'indigo'],
        'semua'                        => ['label' => '📋 Semua',             'color' => 'slate'],
    ];
    foreach ($pills as $key => $pill):
        $isActive = ($filter === $key);
        $base = $isActive
            ? 'bg-primary text-white shadow-lg shadow-indigo-300/50'
            : 'bg-white text-slate-600 border border-slate-200 hover:border-primary hover:text-primary';
    ?>
        <a href="?filter=<?php echo $key; ?>" class="px-4 py-2 rounded-xl text-sm font-bold transition-all duration-200 <?php echo $base; ?>">
            <?php echo $pill['label']; ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3 animate-fade-in" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="block sm:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['success_msg']); ?></span>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_msg'])): ?>
    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3 animate-fade-in" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="block sm:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['error_msg']); ?></span>
    </div>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<div class="animate-slide-up">
    <?php if (empty($pendingReports)): ?>
        <div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-100 p-12 text-center text-slate-500 flex flex-col items-center">
            <svg class="w-16 h-16 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <p class="text-lg font-medium">Tidak ada kegiatan dengan filter <strong><?php echo $filterLabels[$filter]; ?></strong>.</p>
            <a href="?filter=semua" class="mt-4 text-primary underline text-sm">Lihat semua kegiatan</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php foreach ($pendingReports as $rep): ?>
                <div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-100 overflow-hidden group hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300">
                    <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-slate-100">
                        <h3 class="font-extrabold text-xl text-slate-800 tracking-tight group-hover:text-primary transition-colors"><?php echo htmlspecialchars($rep['nama_kegiatan']); ?></h3>
                        <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Oleh: <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($rep['user_name']); ?></span>
                        </p>
                    </div>
                    
                    <div class="p-6 space-y-5">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Waktu Kegiatan</p>
                            <p class="text-sm text-slate-700 bg-slate-50 inline-block px-3 py-1.5 rounded-lg border border-slate-100 font-medium">
                                <?php echo date('d M Y, H:i', strtotime($rep['waktu_mulai'])); ?> - <?php echo date('d M Y, H:i', strtotime($rep['waktu_selesai'])); ?>
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Hasil Kegiatan</p>
                            <div class="text-sm text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100 leading-relaxed shadow-inner">
                                <?php echo nl2br(htmlspecialchars($rep['laporan']['hasil_kegiatan'] ?? '-')); ?>
                            </div>
                        </div>

                        <?php if (!empty($rep['laporan']['kendala'])): ?>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Kendala</p>
                                <div class="text-sm text-rose-700 bg-rose-50 p-4 rounded-xl border border-rose-100 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($rep['laporan']['kendala'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($rep['laporan']['solusi'])): ?>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Solusi</p>
                                <div class="text-sm text-emerald-700 bg-emerald-50 p-4 rounded-xl border border-emerald-100 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($rep['laporan']['solusi'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Bukti Berkas</p>
                            <?php
                            $url = $rep['laporan']['url_bukti'] ?? '';
                            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                echo '<img src="' . htmlspecialchars($url) . '" alt="Lampiran Bukti" class="w-full max-h-64 object-cover rounded-xl border border-slate-200 shadow-sm">';
                            } else {
                                echo '<a href="' . htmlspecialchars($url) . '" target="_blank" class="inline-flex items-center text-primary hover:text-secondary text-sm font-bold bg-primary/5 hover:bg-primary/10 px-4 py-2 rounded-lg transition-colors border border-primary/20"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>Lihat Bukti Lampiran</a>';
                            }
                            ?>
                        </div>
                    </div>

                    <?php if (($rep['status'] ?? '') === 'menunggu_persetujuan_laporan'): ?>
                    <div class="p-6 bg-slate-50 border-t border-slate-100">
                        <form action="approval_laporan.php" method="POST">
                            <input type="hidden" name="activity_id" value="<?php echo htmlspecialchars($rep['id']); ?>">
                            
                            <div class="mb-5">
                                <label class="block text-slate-700 text-xs font-bold mb-2">Catatan Revisi <span class="font-normal text-slate-400">(Wajib jika ditolak)</span></label>
                                <textarea name="catatan_revisi" rows="2" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 placeholder-slate-400 text-sm shadow-inner" placeholder="Tulis catatan jika ada revisi..."></textarea>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button type="submit" name="action" value="approve" class="flex-1 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-emerald-500/30 transform transition-all duration-300 hover:-translate-y-1 active:scale-95 text-sm flex justify-center items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Setujui Laporan
                                </button>
                                <button type="submit" name="action" value="reject" class="flex-1 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-rose-500/30 transform transition-all duration-300 hover:-translate-y-1 active:scale-95 text-sm flex justify-center items-center" onclick="return confirm('Yakin ingin menolak dan meminta revisi laporan ini?');">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Tolak & Revisi
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../components/footer.php'; ?>
