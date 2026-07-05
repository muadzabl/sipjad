<?php
session_start();
require_once 'config/firebase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error_msg'] = 'Username dan password wajib diisi!';
        header("Location: index.php");
        exit;
    }

    // Ambil data users dari firebase
    $users = firebase_get('/users');
    
    $authenticated = false;
    $userData = null;
    $userId = null;

    if ($users) {
        foreach ($users as $key => $user) {
            if ($user['username'] === $username) {
                if (password_verify($password, $user['password'])) {
                    $authenticated = true;
                    $userData = $user;
                    $userId = $key;
                    break;
                }
            }
        }
    }

    if ($authenticated && $userData) {
        // Cek jika role admin dan belum diapprove
        if ($userData['role'] === 'admin') {
            if (!isset($userData['is_admin_approved']) || $userData['is_admin_approved'] == false) {
                $_SESSION['error_msg'] = 'Akun admin Anda masih menunggu persetujuan dari Super Admin.';
                header("Location: index.php");
                exit;
            }
        }

        // Set session
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $userData['username'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['nama_lengkap'] = $userData['nama_lengkap'];

        if ($userData['role'] === 'admin') {
            header("Location: /admin/dashboard.php");
        } else {
            header("Location: /staff/dashboard.php");
        }
        exit;
    } else {
        $_SESSION['error_msg'] = 'Username atau password salah!';
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
