<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: /index.php");
    exit;
}
require_once '../config/firebase.php';

$activityId = $_GET['id'] ?? '';
if (empty($activityId)) {
    header("Location: dashboard.php");
    exit;
}

$activity = firebase_get("/activities/$activityId");
if (!$activity || $activity['user_id'] !== $_SESSION['user_id']) {
    header("Location: dashboard.php");
    exit;
}

// Cek apakah laporan boleh dibuat (berjalan, selesai, perlu_revisi)
if (!in_array($activity['status'], ['berjalan', 'selesai', 'perlu_revisi'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hasil_kegiatan = trim($_POST['hasil_kegiatan'] ?? '');
    $kendala = trim($_POST['kendala'] ?? '');
    $solusi = trim($_POST['solusi'] ?? '');
    $url_bukti = trim($_POST['url_bukti'] ?? '');

    if (empty($hasil_kegiatan)) {
        $_SESSION['error_msg'] = "Hasil Kegiatan wajib diisi!";
    } else {
        // Upload File Handling
        $url_bukti = $lap['url_bukti'] ?? ''; // default to existing
        if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileTmpPath = $_FILES['file_bukti']['tmp_name'];
            $fileName = $_FILES['file_bukti']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Validasi tipe file opsional (disini diizinkan semua atau gambar/pdf dsb)
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $destPath)) {
                $url_bukti = '/uploads/' . $newFileName;
            } else {
                $_SESSION['error_msg'] = "Gagal mengupload file ke server.";
            }
        } elseif (empty($url_bukti)) {
             $_SESSION['error_msg'] = "File bukti wajib diunggah untuk laporan baru!";
        }

        if (!isset($_SESSION['error_msg'])) {
            $laporanData = [
                'hasil_kegiatan' => $hasil_kegiatan,
                'kendala' => $kendala,
                'solusi' => $solusi,
                'url_bukti' => $url_bukti,
                'catatan_revisi' => '', // Reset catatan revisi saat disubmit ulang
                'submitted_at' => date('Y-m-d H:i:s')
            ];

        // Update laporan di node activity
        $updateData = [
            'laporan' => $laporanData,
            'status' => 'menunggu_persetujuan_laporan' // status baru
        ];

        $res = firebase_patch("/activities/$activityId", $updateData);
        if ($res) {
            $_SESSION['success_msg'] = "Laporan berhasil dikirim dan menunggu persetujuan admin.";
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error_msg'] = "Gagal menyimpan laporan ke Firebase.";
        }
    }
  }
}

require_once '../components/header.php';
require_once '../components/sidebar.php';

// Jika sudah ada laporan sebelumnya (revisi)
$lap = $activity['laporan'] ?? [];
?>

<div class="mb-8 animate-fade-in">
    <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight"><?php echo isset($activity['laporan']) ? 'Edit Laporan Kegiatan' : 'Buat Laporan Kegiatan'; ?></h2>
    <p class="text-slate-500 mt-1">Lengkapi data laporan kegiatan <span class="font-bold text-slate-700"><?php echo htmlspecialchars($activity['nama_kegiatan']); ?></span>.</p>
</div>

<?php if (isset($_SESSION['error_msg'])): ?>
    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl relative mb-6 flex items-center gap-3 animate-fade-in" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="block sm:inline text-sm font-medium"><?php echo htmlspecialchars($_SESSION['error_msg']); ?></span>
    </div>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-3xl p-8 max-w-3xl border border-slate-100 animate-slide-up">
    
    <?php if ($activity['status'] === 'perlu_revisi' && !empty($activity['laporan']['catatan_revisi'])): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 mb-6 rounded-r-xl">
            <h3 class="text-rose-800 font-bold flex items-center mb-1">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Catatan Revisi dari Admin:
            </h3>
            <p class="text-rose-700 text-sm pl-7"><?php echo htmlspecialchars($activity['laporan']['catatan_revisi']); ?></p>
        </div>
    <?php endif; ?>

    <form action="buat_laporan.php?id=<?php echo htmlspecialchars($activityId); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <div>
            <label class="block text-slate-700 text-sm font-bold mb-2">Hasil Kegiatan <span class="text-rose-500">*</span></label>
            <textarea name="hasil_kegiatan" rows="5" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 placeholder-slate-400" required placeholder="Jelaskan hasil dari kegiatan secara ringkas namun jelas..."><?php echo htmlspecialchars($activity['laporan']['hasil_kegiatan'] ?? ''); ?></textarea>
        </div>

        <div>
            <label class="block text-slate-700 text-sm font-bold mb-2">Kendala (Opsional)</label>
            <textarea name="kendala" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 placeholder-slate-400" placeholder="Apakah ada hambatan yang terjadi?"><?php echo htmlspecialchars($activity['laporan']['kendala'] ?? ''); ?></textarea>
        </div>

        <div>
            <label class="block text-slate-700 text-sm font-bold mb-2">Solusi (Opsional)</label>
            <textarea name="solusi" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 placeholder-slate-400" placeholder="Apa solusi dari hambatan di atas?"><?php echo htmlspecialchars($activity['laporan']['solusi'] ?? ''); ?></textarea>
        </div>

        <div>
            <label class="block text-slate-700 text-sm font-bold mb-2">Unggah File Bukti (Gambar / Dokumen)</label>
            
            <?php if (!empty($activity['laporan']['url_bukti'])): 
                $url = $activity['laporan']['url_bukti'];
                $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            ?>
                <div class="mb-4">
                    <p class="text-sm font-semibold text-slate-600 mb-2">File saat ini:</p>
                    <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                        <img src="<?php echo htmlspecialchars($url); ?>" alt="Bukti Laporan" class="w-full max-w-xs h-auto object-cover rounded-xl border border-slate-200 shadow-sm mb-2">
                    <?php else: ?>
                        <div class="flex items-center text-sm text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-200 w-fit">
                            <svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="text-primary hover:underline font-semibold truncate max-w-[250px]"><?php echo basename($url); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div id="preview-container" class="mb-4 hidden animate-fade-in">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-sm font-medium">File baru dipilih (belum tersimpan)</span>
                </div>
                <img id="image-preview" src="#" alt="Preview" class="hidden w-full max-w-xs h-auto object-cover rounded-xl border border-slate-200 shadow-sm mb-2">
                <div id="file-name-preview" class="text-sm text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 w-fit"></div>
            </div>

            <div class="flex items-center justify-center w-full">
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p class="mb-2 text-sm text-slate-500"><span class="font-bold">Klik untuk unggah</span> atau seret file ke sini</p>
                        <p class="text-xs text-slate-400">PNG, JPG, PDF (Maks. 5MB)</p>
                    </div>
                    <input type="file" id="file_bukti" name="file_bukti" class="hidden" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" />
                </label>
            </div>
            <p class="text-xs text-slate-500 mt-2">* Kosongkan jika tidak ingin mengubah file sebelumnya.</p>
        </div>

        <div class="flex items-center justify-end pt-4 space-x-4">
            <a href="dashboard.php" class="text-slate-500 hover:text-slate-700 font-bold px-4 py-2">Batal</a>
            <button type="submit" class="bg-gradient-to-r from-primary to-secondary hover:from-secondary hover:to-primary text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-500/30 transform transition-all duration-300 hover:-translate-y-1 active:scale-95">
                Kirim Laporan
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('file_bukti').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const fileNamePreview = document.getElementById('file-name-preview');

    if (file) {
        previewContainer.classList.remove('hidden');
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
            fileNamePreview.textContent = file.name;
        } else {
            imagePreview.classList.add('hidden');
            fileNamePreview.textContent = file.name + ' (Dokumen)';
        }
    } else {
        previewContainer.classList.add('hidden');
        imagePreview.classList.add('hidden');
    }
});
</script>

<?php require_once '../components/footer.php'; ?>
