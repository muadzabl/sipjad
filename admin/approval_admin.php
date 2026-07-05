<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit;
}
require_once '../config/firebase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = $_POST['target_user_id'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($target_user_id && $action) {
        if ($action === 'approve') {
            $updateData = ['is_admin_approved' => true];
            $res = firebase_patch("/users/$target_user_id", $updateData);
            if ($res) {
                $_SESSION['success_msg'] = "Akun admin berhasil disetujui.";
            } else {
                $_SESSION['error_msg'] = "Gagal menyetujui akun admin.";
            }
        } elseif ($action === 'reject') {
            // Delete user
            $url = getFirebaseUrl("/users/$target_user_id");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $_SESSION['success_msg'] = "Akun admin berhasil ditolak dan dihapus.";
            } else {
                $_SESSION['error_msg'] = "Gagal menghapus akun admin.";
            }
        }
    }
    header("Location: approval_admin.php");
    exit;
}

require_once '../components/header.php';
require_once '../components/sidebar.php';

$users = firebase_get('/users');
$pendingAdmins = [];

if ($users) {
    foreach ($users as $key => $user) {
        if (isset($user['role']) && $user['role'] === 'admin' && empty($user['is_admin_approved'])) {
            $user['id'] = $key;
            $pendingAdmins[] = $user;
        }
    }
}
?>

<div class="mb-8 animate-fade-in">
    <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Persetujuan Akun Admin Baru</h2>
    <p class="text-slate-500 mt-1">Daftar pengguna yang mendaftar sebagai Admin dan membutuhkan persetujuan Anda.</p>
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

<div class="bg-white/80 backdrop-blur-xl shadow-xl shadow-slate-200/50 rounded-3xl overflow-hidden border border-slate-100 animate-slide-up">
    <?php if (empty($pendingAdmins)): ?>
        <div class="p-12 text-center text-slate-500 flex flex-col items-center">
            <svg class="w-16 h-16 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <p class="text-lg font-medium">Tidak ada admin baru yang menunggu persetujuan.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Daftar</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    <?php foreach ($pendingAdmins as $admin): ?>
                        <tr class="hover:bg-slate-50 transition-colors duration-200 group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800"><?php echo htmlspecialchars($admin['nama_lengkap'] ?? ''); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                <?php echo htmlspecialchars($admin['username'] ?? ''); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-sm">
                                <?php echo htmlspecialchars($admin['created_at'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                <form action="approval_admin.php" method="POST" class="inline-block">
                                    <input type="hidden" name="target_user_id" value="<?php echo htmlspecialchars($admin['id']); ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-600 font-semibold rounded-lg hover:bg-emerald-500 hover:text-white transition-all duration-300 transform active:scale-95 shadow-sm text-sm">
                                        Setujui
                                    </button>
                                </form>
                                <form action="approval_admin.php" method="POST" class="inline-block">
                                    <input type="hidden" name="target_user_id" value="<?php echo htmlspecialchars($admin['id']); ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" onclick="return confirm('Tolak dan hapus akun admin ini?');" class="inline-flex items-center px-4 py-2 bg-rose-50 text-rose-600 font-semibold rounded-lg hover:bg-rose-500 hover:text-white transition-all duration-300 transform active:scale-95 shadow-sm text-sm">
                                        Tolak
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../components/footer.php'; ?>
