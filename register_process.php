<?php
session_start();
require_once 'config/firebase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff';

    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($role)) {
        $_SESSION['error_msg'] = 'Semua field wajib diisi!';
        header("Location: register.php");
        exit;
    }

    // Validasi apakah username sudah ada
    $users = firebase_get('/users');
    if ($users) {
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $_SESSION['error_msg'] = 'Username sudah digunakan!';
                header("Location: register.php");
                exit;
            }
        }
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    $is_admin_approved = ($role === 'admin') ? false : true;

    $userData = [
        'nama_lengkap' => $nama_lengkap,
        'username' => $username,
        'password' => $hashed_password,
        'role' => $role,
        'is_admin_approved' => $is_admin_approved,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $result = firebase_push('/users', $userData);

    if ($result) {
        if ($role === 'admin') {
            $_SESSION['success_msg'] = 'Pendaftaran berhasil. Akun admin Anda sedang menunggu persetujuan.';
        } else {
            $_SESSION['success_msg'] = 'Pendaftaran berhasil. Silakan masuk.';
        }
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error_msg'] = 'Terjadi kesalahan saat mendaftar ke Firebase.';
        header("Location: register.php");
        exit;
    }
} else {
    header("Location: register.php");
    exit;
}
?>
