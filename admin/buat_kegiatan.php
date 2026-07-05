<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit;
}
require_once '../config/firebase.php';

$users = firebase_get('/users') ?? [];
$staffs = [];
foreach ($users as $id => $u) {
    if (($u['role'] ?? '') === 'staff') {
        $u['id'] = $id;
        $staffs[] = $u;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kegiatan = trim($_POST['nama_kegiatan'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $waktu_mulai = $_POST['waktu_mulai'] ?? '';
    $waktu_selesai = $_POST['waktu_selesai'] ?? '';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $assign_to = $_POST['assign_to'] ?? '';

    if (empty($nama_kegiatan) || empty($waktu_mulai) || empty($waktu_selesai) || empty($assign_to)) {
        $_SESSION['error_msg'] = "Formulir tidak lengkap!";
    } elseif (strtotime($waktu_selesai) <= strtotime($waktu_mulai)) {
        $_SESSION['error_msg'] = "Waktu selesai tidak boleh lebih awal atau sama dengan waktu mulai!";
    } else {
        $baseKegiatanData = [
            'nama_kegiatan' => $nama_kegiatan,
            'lokasi' => $lokasi,
            'waktu_mulai' => $waktu_mulai,
            'waktu_selesai' => $waktu_selesai,
            'deskripsi' => $deskripsi,
            'status' => 'belum_mulai',
            'created_at' => date('Y-m-d H:i:s'),
            'is_from_admin' => true
        ];

        $successCount = 0;
        
        if ($assign_to === 'all') {
            foreach ($staffs as $staff) {
                $data = $baseKegiatanData;
                $data['user_id'] = $staff['id'];
                if (firebase_push('/activities', $data)) {
                    $successCount++;
                }
            }
        } else {
            $data = $baseKegiatanData;
            $data['user_id'] = $assign_to;
            if (firebase_push('/activities', $data)) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            $_SESSION['success_msg'] = "Tugas berhasil dibagikan ke $successCount staf.";
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error_msg'] = "Gagal menyimpan kegiatan ke Firebase.";
        }
    }
}

require_once '../components/header.php';
require_once '../components/sidebar.php';
?>

<div class="mb-8 animate-fade-in">
    <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Tugaskan Kegiatan Baru</h2>
    <p class="text-slate-500 mt-1">Buat kegiatan dan tugaskan kepada staf.</p>
</div>

<div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-3xl p-8 max-w-2xl border border-slate-100 animate-slide-up">
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3 animate-fade-in" role="alert">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="block sm:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['error_msg']); ?></span>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <form action="buat_kegiatan.php" method="POST" class="space-y-5">
        <div>
            <label class="block text-slate-700 text-sm font-bold mb-2">Tugaskan Ke <span class="text-rose-500">*</span></label>
            <select name="assign_to" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300" required>
                <option value="" disabled selected>-- Pilih Target Staf --</option>
                <option value="all" class="font-bold">✨ Semua Staf</option>
                <?php foreach ($staffs as $staff): ?>
                    <option value="<?php echo htmlspecialchars($staff['id']); ?>"><?php echo htmlspecialchars($staff['nama_lengkap']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-slate-700 text-sm font-bold mb-2">Nama Kegiatan <span class="text-rose-500">*</span></label>
            <input type="text" name="nama_kegiatan" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300" required placeholder="Contoh: Rapat Koordinasi Bulanan">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-slate-700 text-sm font-bold mb-2">Waktu Mulai <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="waktu_mulai" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300" required>
            </div>
            <div>
                <label class="block text-slate-700 text-sm font-bold mb-2">Waktu Selesai <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="waktu_selesai" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300" required>
            </div>
        </div>

        <div>
            <label class="block text-slate-700 text-sm font-bold mb-2">Lokasi (Opsional)</label>
            <input type="text" name="lokasi" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300" placeholder="Ruang Meeting A">
        </div>

        <div>
            <label class="block text-slate-700 text-sm font-bold mb-2">Deskripsi (Opsional)</label>
            <textarea name="deskripsi" rows="4" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 placeholder-slate-400" placeholder="Tulis rincian penugasan secara jelas..."></textarea>
        </div>

        <div class="flex items-center justify-end pt-4 space-x-4">
            <a href="dashboard.php" class="text-slate-500 hover:text-slate-700 font-bold px-4 py-2">Batal</a>
            <button type="submit" class="bg-gradient-to-r from-primary to-secondary hover:from-secondary hover:to-primary text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-500/30 transform transition-all duration-300 hover:-translate-y-1 active:scale-95">
                Kirim Tugas
            </button>
        </div>
    </form>
</div>

<?php require_once '../components/footer.php'; ?>